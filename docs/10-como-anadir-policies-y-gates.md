# 10 · Cómo añadir Policies y Gates

Las **Policies** definen quién puede hacer qué sobre un modelo concreto (`view`, `create`, `update`, `delete`). Combinadas con **Gates** permiten centralizar reglas de autorización por rol o por propietario.

Actualmente el proyecto **no aplica** políticas en controllers (no existe `app/Policies/`). Esta guía describe cómo añadirlas siguiendo Laravel 12.

## 🎯 Diferencia entre Gates y Policies

| Mecanismo | Caso de uso |
|-----------|-------------|
| `Gate::define(...)` | Reglas globales (por rol de usuario, capacidades booleanas). |
| `Policy` (clase) | Reglas por instancia de modelo (un usuario puede editar **su** venta pero no la de otros). |

Se pueden combinar: una Policy puede usar gates internamente.

## ✍️ Crear una Policy

```bash
php artisan make:policy VentaPolicy --model=Venta
```

Esto crea `app/Policies/VentaPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venta;

class VentaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles->pluck('nombre')->contains('vendedor')
            || $user->roles->pluck('nombre')->contains('admin');
    }

    public function view(User $user, Venta $venta): bool
    {
        return $this->viewAny($user) || $user->id === $venta->id_usuario;
    }

    public function create(User $user): bool
    {
        return $user->roles->pluck('nombre')->contains('vendedor')
            || $user->roles->pluck('nombre')->contains('admin');
    }

    public function update(User $user, Venta $venta): bool
    {
        return $this->create($user) || $user->id === $venta->id_usuario;
    }

    public function delete(User $user, Venta $venta): bool
    {
        return $user->roles->pluck('nombre')->contains('admin');
    }
}
```

## 🔌 Registrar la Policy

En Laravel 12 ya no existe `AuthServiceProvider`. Registra el mapeo **explícitamente** dentro de `app/Providers/AppServiceProvider.php`:

```php
use App\Models\Venta;
use App\Policies\VentaPolicy;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(Venta::class, VentaPolicy::class);
}
```

> 💡 Laravel también descubre Policies automáticamente si la clase sigue la convención `App\Policies\<Model>Policy` y existe el modelo. El registro explícito es más claro para nuevos colaboradores.

## 🚦 Definir Gates adicionales

```php
public function boot(): void
{
    Gate::policy(Venta::class, VentaPolicy::class);

    Gate::define('administrar-sistema', function (User $user): bool {
        return $user->roles->pluck('nombre')->contains('admin');
    });
}
```

## 🛡️ Uso en Controllers

### Opción 1: helper `$this->authorize()`

Requiere que el controller extienda la clase base `App\Http\Controllers\Controller` (que ya se encarga del trait `AuthorizesRequests`):

```php
public function update(UpdateVentaRequest $request, int $id): JsonResponse
{
    $venta = $this->ventaService->getById($id); // Lanza 404 si no existe

    $this->authorize('update', $venta); // Lanza 403 si no pasa

    // ... lógica de actualización ...
}
```

`authorize()` lanza `AuthorizationException`, que por defecto produce 403. Para devolver tu shape JSON, añade un render en `bootstrap/app.php`:

```php
use Illuminate\Auth\Access\AuthorizationException;

$exceptions->render(function (AuthorizationException $e, $request) {
    if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json([
            'success' => false,
            'message' => 'No tiene permisos para realizar esta acción.',
            'error'   => 'Forbidden',
        ], 403);
    }
});
```

### Opción 2: helper `Gate::allows()` con respuesta manual

```php
use Illuminate\Support\Facades\Gate;

public function destroy(int $id): JsonResponse
{
    $venta = $this->ventaService->getById($id);

    if (! Gate::allows('delete', $venta)) {
        return response()->json([
            'success' => false,
            'message' => 'No tiene permisos para eliminar esta venta.',
        ], 403);
    }

    $this->ventaService->delete($id, Auth::id());

    return response()->json(['message' => 'Venta eliminada correctamente']);
}
```

## 🧭 Policies en módulos CRUD sin propiedad exclusiva

Para módulos como `Categorías` o `Proveedores` (donde no hay "dueño"), basta una Policy simple basada en rol:

```php
class CategoriaPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->roles->pluck('nombre')->contains('admin')) {
            return true; // acceso total
        }
        return null; // continúa con las reglas siguientes
    }

    public function viewAny(User $user): bool  { return true; }
    public function view(User $user): bool     { return true; }
    public function create(User $user): bool   { return $user->roles->pluck('nombre')->contains('vendedor'); }
    public function update(User $user): bool   { return $user->roles->pluck('nombre')->contains('vendedor'); }
    public function delete(User $user): bool   { return false; } // solo admin (lo corta `before`)
}
```

El hook `before()` corta la verificación cuando retorna `true`; retorna `null` para continuar.

## 🧪 Tests de autorización

```php
use App\Models\User;
use App\Models\Venta;
use Laravel\Sanctum\Sanctum;

it('impide actualizar la venta a un usuario sin rol', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $venta = Venta::factory()->create();

    $response = $this->putJson("/api/v1/ventas/{$venta->id}", [
        'estado' => 'cancelado',
    ]);

    $response->assertForbidden();
});
```

## ✅ Checklist

- [ ] `App\Policies\<Modelo>Policy.php` creada con métodos `viewAny/view/create/update/delete`.
- [ ] Registrada con `Gate::policy(Modelo::class, Policy::class)` en `AppServiceProvider::boot()`.
- [ ] Controller usa `$this->authorize(...)` o `Gate::allows(...)`.
- [ ] Render personalizado de `AuthorizationException` en `bootstrap/app.php` (shape JSON).
- [ ] Tests negativos verifican `403 Forbidden` cuando aplica.
