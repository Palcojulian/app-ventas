# 07 · Cómo crear un nuevo módulo

Esta guía replica **exactamente** el patrón del proyecto. Sirve para cualquier módulo nuevo, por ejemplo `Clientes`, `Inventario` o `Reportes`. La numeración corresponde a los pasos en orden cronológico.

> 💡 Convenciones clave (recordatorio):
> - Subcarpeta por módulo dentro de `app/Http/Controllers/`, `app/Http/Requests/`, `app/Actions/`, `app/Queries/` y `app/Services/`.
> - Un único Service por módulo que recibe Queries + Actions en su constructor.
> - Validación con `Store…Request` / `Update…Request` y respuesta JSON 422 uniforme.
> - Eloquent con relaciones tipadas; `casts()` en lugar de `$casts`.

---

## 0. Plantilla de nombres

Sustituye `<Modulo>` por el singular en `PascalCase` (ej. `Cliente`) y `<modulo>` por la forma en snake_case en minúsculas para URLs (ej. `clientes`). Tabla: `clientes`.

Archivos a crear:

```
database/migrations/<timestamp>_create_clientes_table.php
app/Models/Cliente.php
database/factories/ClienteFactory.php
app/Http/Requests/Clientes/StoreClienteRequest.php
app/Http/Requests/Clientes/UpdateClienteRequest.php
app/Queries/Clientes/GetAllClientes.php
app/Queries/Clientes/GetClienteById.php
app/Actions/Clientes/CreateClienteAction.php
app/Actions/Clientes/UpdateClienteAction.php
app/Actions/Clientes/DeleteClienteAction.php
app/Services/Clientes/ClienteService.php
app/Http/Controllers/Clientes/ClienteController.php
tests/Feature/Clientes/ClienteApiTest.php  (con mkdir primero)
```

Registros en `routes/api.php` dentro del grupo `auth:sanctum` y `prefix('v1')`.

---

## 1. Modelo + migración + factory

```bash
php artisan make:model Cliente -mf
```

Esto crea:

- `app/Models/Cliente.php`
- `database/migrations/<timestamp>_create_clientes_table.php`
- `database/factories/ClienteFactory.php`

Edita la migración con los campos del recurso (ej. `nombre`, `documento`, `telefono`, `email`, `estado`) y luego:

```bash
php artisan migrate
```

### Ejemplo de `app/Models/Cliente.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'documento',
        'telefono',
        'email',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            // ejemplo: 'fecha_nacimiento' => 'date',
        ];
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_cliente');
    }
}
```

### Ejemplo de `database/factories/ClienteFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'nombre'    => fake()->name(),
            'documento' => fake()->unique()->numerify('##########'),
            'telefono'  => fake()->phoneNumber(),
            'email'     => fake()->unique()->safeEmail(),
            'estado'    => 'activo',
        ];
    }
}
```

---

## 2. Form Requests

```bash
php artisan make:request Clientes/StoreClienteRequest
php artisan make:request Clientes/UpdateClienteRequest
```

### `StoreClienteRequest.php`

```php
<?php

namespace App\Http\Requests\Clientes;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'    => 'required|string|max:255',
            'documento' => 'required|string|max:50|unique:clientes,documento',
            'telefono'  => 'nullable|string|max:50',
            'email'     => 'nullable|email|max:255|unique:clientes,email',
            'estado'    => 'nullable|string|in:activo,inactivo',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'    => 'El nombre del cliente es obligatorio.',
            'documento.required' => 'El documento es obligatorio.',
            'documento.unique'   => 'El documento ya está registrado.',
            'email.email'        => 'El email debe tener un formato válido.',
            'email.unique'       => 'El email ya está registrado.',
            'estado.in'          => 'El estado debe ser: activo o inactivo.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $mensajes = implode(' | ', $validator->errors()->all());
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Campos requeridos pendientes',
            'errores' => $mensajes,
        ], 422));
    }
}
```

`UpdateClienteRequest.php` replica el mismo esqueleto, cambiando `required` por `sometimes` y omitiendo `unique` (o usando `Rule::unique` con `ignore($this->id)`).

---

## 3. Queries

```bash
mkdir -p app/Queries/Clientes
```

### `GetAllClientes.php`

```php
<?php

namespace App\Queries\Clientes;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Collection;

class GetAllClientes
{
    public function handle(?string $estado = null): Collection
    {
        return Cliente::query()
            ->when($estado, fn ($q, $v) => $q->where('estado', '=', $v))
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
```

### `GetClienteById.php`

```php
<?php

namespace App\Queries\Clientes;

use App\Models\Cliente;

class GetClienteById
{
    public function handle(int $id): Cliente
    {
        return Cliente::query()->findOrFail($id);
    }
}
```

> 💡 Si necesitas joins enriquecidos, mira `app/Queries/Ventas/GetAllVentas.php` o `app/Queries/Productos/GetAllProductos.php` para ver el patrón con `selectRaw`/`join`.

---

## 4. Actions

```bash
mkdir -p app/Actions/Clientes
```

### `CreateClienteAction.php`

```php
<?php

namespace App\Actions\Clientes;

use App\Models\Cliente;

class CreateClienteAction
{
    public function handle(array $data): Cliente
    {
        $data['estado'] = $data['estado'] ?? 'activo';

        return Cliente::create($data)->refresh();
    }
}
```

### `UpdateClienteAction.php`

```php
<?php

namespace App\Actions\Clientes;

use App\Models\Cliente;

class UpdateClienteAction
{
    public function handle(Cliente $cliente, array $data): Cliente
    {
        $cliente->update([
            'nombre'    => $data['nombre']    ?? $cliente->nombre,
            'documento' => $data['documento'] ?? $cliente->documento,
            'telefono'  => $data['telefono']  ?? $cliente->telefono,
            'email'     => $data['email']     ?? $cliente->email,
            'estado'    => $data['estado']    ?? $cliente->estado,
        ]);

        return $cliente->fresh();
    }
}
```

### `DeleteClienteAction.php`

```php
<?php

namespace App\Actions\Clientes;

use App\Models\Cliente;

class DeleteClienteAction
{
    public function handle(Cliente $cliente): void
    {
        $cliente->delete();
    }
}
```

> 💡 Si la operación afecta a varias tablas, envuelve el cuerpo de `handle()` en `DB::transaction(function () use (...) { ... })`. Ejemplo: `app/Actions/Ventas/CreateVentaAction.php`.

---

## 5. Service

```bash
mkdir -p app/Services/Clientes
```

### `ClienteService.php`

```php
<?php

namespace App\Services\Clientes;

use App\Actions\Clientes\CreateClienteAction;
use App\Actions\Clientes\DeleteClienteAction;
use App\Actions\Clientes\UpdateClienteAction;
use App\Queries\Clientes\GetAllClientes;
use App\Queries\Clientes\GetClienteById;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Cliente;

class ClienteService
{
    public function __construct(
        private GetAllClientes $getAllClientes,
        private GetClienteById $getClienteById,
        private CreateClienteAction $createClienteAction,
        private UpdateClienteAction $updateClienteAction,
        private DeleteClienteAction $deleteClienteAction,
    ) {}

    public function getAll(?string $estado = null): Collection
    {
        return $this->getAllClientes->handle($estado);
    }

    public function getById(int $id): Cliente
    {
        return $this->getClienteById->handle($id);
    }

    public function create(array $data): Cliente
    {
        return $this->createClienteAction->handle($data);
    }

    public function update(int $id, array $data): Cliente
    {
        $cliente = $this->getClienteById->handle($id);

        return $this->updateClienteAction->handle($cliente, $data);
    }

    public function delete(int $id): void
    {
        $cliente = $this->getClienteById->handle($id);
        $this->deleteClienteAction->handle($cliente);
    }
}
```

---

## 6. Controller

```bash
mkdir -p app/Http/Controllers/Clientes
php artisan make:controller Clientes/ClienteController
```

### `ClienteController.php`

```php
<?php

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clientes\StoreClienteRequest;
use App\Http\Requests\Clientes\UpdateClienteRequest;
use App\Services\Clientes\ClienteService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ClienteController extends Controller
{
    public function __construct(
        private ClienteService $clienteService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $estado = $request->query('estado');
            $clientes = $this->clienteService->getAll($estado);

            return response()->json($clientes);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Error al obtener los clientes.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->clienteService->create($request->validated());
            return response()->json($cliente, 201);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Error al crear el cliente.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return response()->json($this->clienteService->getById($id));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error'   => 'Cliente no encontrado.',
                'message' => 'El cliente con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Error al obtener el cliente.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateClienteRequest $request, int $id): JsonResponse
    {
        try {
            return response()->json($this->clienteService->update($id, $request->validated()));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error'   => 'Cliente no encontrado.',
                'message' => 'El cliente con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Error al actualizar el cliente.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->clienteService->delete($id);
            return response()->json(['message' => 'Cliente eliminado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error'   => 'Cliente no encontrado.',
                'message' => 'El cliente con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Error al eliminar el cliente.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
```

> 📌 Si necesitas pasar el `id_usuario` autenticado al Service (estilo `Ventas`), inyecta `Auth::id()` como segundo argumento en `store`/`update`/`destroy`.

---

## 7. Registrar rutas

Edita `routes/api.php`, dentro del grupo `Route::middleware('auth:sanctum')` y `Route::prefix('v1')`:

```php
use App\Http\Controllers\Clientes\ClienteController;

Route::prefix('clientes')->group(function () {
    Route::get('/',          [ClienteController::class, 'index']);
    Route::post('/',         [ClienteController::class, 'store']);
    Route::get('/{id}',      [ClienteController::class, 'show']);
    Route::put('/{id}',      [ClienteController::class, 'update']);
    Route::delete('/{id}',   [ClienteController::class, 'destroy']);
});
```

Verifica con:

```bash
php artisan route:list --path=clientes
```

---

## 8. Tests Pest

```bash
php artisan make:test --pest Clientes/ClienteApiTest
```

Plantilla mínima (ver `docs/11-testing-con-pest.md` para una versión detallada):

```php
<?php

use App\Models\Cliente;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

it('lista clientes', function () {
    Cliente::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/clientes');

    $response->assertOk()
        ->assertJsonCount(3);
});

it('crea un cliente', function () {
    $data = [
        'nombre'    => 'Juan',
        'documento' => '1234567890',
        'telefono'  => '+57 300 123 4567',
        'email'     => 'juan@example.com',
        'estado'    => 'activo',
    ];

    $response = $this->postJson('/api/v1/clientes', $data);

    $response->assertCreated()
        ->assertJsonFragment(['nombre' => 'Juan']);

    expect(Cliente::where('documento', '1234567890')->exists())->toBeTrue();
});
```

---

## 9. Formateo final

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
```

---

## ✅ Checklist rápido

- [ ] Migración creada, atributos completos, ejecutada (`php artisan migrate`).
- [ ] Modelo con `$fillable`, relaciones tipadas y `casts()` (si aplica).
- [ ] Factory.
- [ ] `Store…Request` y `Update…Request` con mensajes en español y `failedValidation` uniforme.
- [ ] `Queries/<Modulo>/{GetAllX,GetXById}.php`.
- [ ] `Actions/<Modulo>/{CreateX,UpdateX,DeleteX}Action.php`.
- [ ] `Services/<Modulo>/<Modulo>Service.php`.
- [ ] `Controllers/<Modulo>/<Modulo>Controller.php` con `try/catch`.
- [ ] Ruta en `api.php` bajo `auth:sanctum` + `v1`.
- [ ] `php artisan make:test --pest …`.
- [ ] `vendor/bin/pint --dirty --format agent`.
- [ ] `php artisan test --compact` ✅.
