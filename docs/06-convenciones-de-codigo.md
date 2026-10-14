# 06 · Convenciones de código

Estas reglas se aplican a **todo el código PHP** que se añada al proyecto. Reflejan tanto las normas de Laravel 12 como el estilo ya asentado en el repositorio (`AGENTS.md`, `pint.json`, código existente).

## 🔤 PHP base

- **Versión:** 8.4 (`composer.json` declara `"php": "^8.2"` pero el Dockerfile corre 8.4).
- **Llaves obligatorias**, incluso para una sola línea:

  ```php
  if ($ok) {
      return true;
  }
  ```

- **Constructor con property promotion** de PHP 8. No se permite `__construct()` vacío salvo privado:

  ```php
  public function __construct(
      private ProductoService $productoService,
  ) {}
  ```

- **Tipos de retorno explícitos** en todos los métodos y funciones. Si usas `Throwable` captúralo de manera intencionada, no de forma genérica, salvo en la capa de controller (uniformidad de respuesta).
- **Enums:** claves en `TitleCase` (`FavoritePerson`, `Monthly`).
- **PHPDoc > comentarios inline.** Usa bloques PHPDoc para describir firmas con arrays o tipos complejos; evita `// esto…`.
- **`use` ordenados alfabéticamente** dentro de cada bloque. Mantén el grupo PHP estándar separado de `use Illuminate\…` y de los namespaces `App\…`.

## 🏛️ Laravel 12 — modelo y proveedores

- **No existe `app/Http/Kernel.php`**. Registra middleware en `bootstrap/app.php` con `Application::configure()->withMiddleware(...)`.
- **No existe `app/Console/Kernel.php`**. Configurar comandos en `bootstrap/app.php` o `routes/console.php`. Los comandos bajo `app/Console/Commands/` se auto-registran.
- **Providers** específicos de la app en `bootstrap/providers.php`.
- **Models:** declarar los casts en el método `casts()`, no en `$casts`:

  ```php
  protected function casts(): array
  {
      return [
          'password' => 'hashed',
          'email_verified_at' => 'datetime',
      ];
  }
  ```

- **Migraciones:** al modificar una columna incluye **todos** los atributos previos; si no, se pierden.

## 🧱 Arquitectura por capas

| Capa | Carpeta | Reglas |
|------|---------|--------|
| Controller | `app/Http/Controllers/<Modulo>/` | Inyectar Service por constructor, retorno `: JsonResponse`, `try/catch (Throwable $e)`. |
| FormRequest | `app/Http/Requests/<Modulo>/` | `Store…Request` / `Update…Request`. `authorize()` retorna `true` salvo excepciones. Mensajes en español. |
| Service | `app/Services/<Modulo>/` | Una sola clase por módulo. Métodos públicos delgados. |
| Query | `app/Queries/<Modulo>/` | Nombre `GetAllX` / `GetXById`. Único método `handle()`. |
| Action | `app/Actions/<Modulo>/` | Nombre `CreateXAction` / `UpdateXAction` / `DeleteXAction`. Único método `handle()`. |
| Model | `app/Models/` | `$fillable` obligatorio, relaciones tipadas, `casts()` para casts. |
| Event | `app/Events/<Modulo>/` | Sufijo `Event`, una clase por caso (Creada/Actualizada/Eliminada por defecto). |

## 📐 Validación (Form Requests)

- Mensajes **siempre en español**, sin mezclar idiomas.
- Sobrescribe `failedValidation()` para devolver `JSON 422` con shape estable:

  ```php
  protected function failedValidation(Validator $validator): void
  {
      $mensajes = implode(' | ', $validator->errors()->all());
      throw new HttpResponseException(response()->json([
          'success' => false,
          'message' => 'Campos requeridos pendientes',
          'errores' => $mensajes,
      ], 422));
  }
  ```

- Para arrays valida con la notación `detalles.*.id_producto`.

## 🛰️ Controllers — patrón uniforme

```php
public function show(int $id): JsonResponse
{
    try {
        $item = $this->service->getById($id);
        return response()->json($item);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'error'   => 'Recurso no encontrado.',
            'message' => 'El recurso con el ID especificado no existe.',
        ], 404);
    } catch (Throwable $e) {
        return response()->json([
            'error'   => 'Error al obtener el recurso.',
            'message' => $e->getMessage(),
        ], 500);
    }
}
```

## 🗄️ Persistencia

- **Eloquent primero.** Evita `DB::` salvo en transacciones multi-tabla.
- **Eager loading** para evitar N+1 (ej. `$venta->load(['cliente','usuario','detalleVentas.producto'])`).
- **Transacciones** explícitas en Actions con efectos colaterales:

  ```php
  return DB::transaction(function () use ($data, $idUsuario) {
      // ... varios inserts/updates ...
  });
  ```

- **No usar `env()`** fuera de archivos en `config/`. Para runtime, usa `config('app.timezone')` o similar.
- **Rutas con nombre** y `route('ventas.show', $id)`.
- **Sin raw SQL** sin una justificación clara; usar `selectRaw(...)` con expresiones explícitas (como en `GetAllVentas`, `GetAllProductos`) es aceptable y ya está normalizado en el proyecto.

## 📦 Respuestas JSON

| Caso | Shape |
|------|-------|
| OK (200) | Recurso o lista. |
| Creado (201) | Recurso. |
| Validación (422) | `{ success:false, message:"Campos requeridos pendientes", errores:"<msg1> | <msg2>" }`. |
| No encontrado (404) | `{ error:"… no encontrado.", message:"El … con el ID especificado no existe." }`. |
| No autenticado (401) | `{ success:false, message:"No autenticado…", error:"Unauthorized" }`. |
| Error inesperado (500) | `{ error:"Error al …", message:"<excepción>" }`. |

## 🧪 Formateo y estilo (Pint)

- Tras cualquier modificación de PHP, ejecutar:

  ```bash
  vendor/bin/pint --dirty --format agent
  ```

- **Nunca** ejecutes `vendor/bin/pint --test --format agent` para "verificar"; ejecuta `pint` directamente para que corrija.

## 🚫 Cosas a evitar

- Crear scripts sueltos (`tinker --execute` o archivos `probar_*.php`) cuando el flujo ya esté cubierto por un test.
- Inventar una nueva estructura de carpetas sin aprobación.
- Cambiar dependencias de `composer.json` sin aprobación.
- Crear nuevos archivos de documentación fuera de `docs/` salvo que se pida explícitamente.

## ✅ Checklist pre-commit (resumido)

1. Código tipado y con llaves.
2. Mensajes de validación en español.
3. Si añadiste archivos PHP: `vendor/bin/pint --dirty --format agent`.
4. Si añadiste lógica testeable: test Pest correspondiente (ver `docs/11-testing-con-pest.md`).
5. Si añadiste una ruta: confirmar que está dentro del grupo `auth:sanctum` y bajo `v1` salvo que sea pública.
6. Mensajes en español para errores de usuario. Mensajes en inglés sólo para excepciones internas (no se exponen al cliente final si están en 500 con `$e->getMessage()` en producción; en desarrollo es legítimo verlos).
