# 02 · Estructura de carpetas

Este documento describe el árbol completo del proyecto y el propósito de cada carpeta/archivo clave.

## 🌳 Árbol global

```
app-ventas/
├── app/
│   ├── Actions/              # Casos de uso de escritura (Create/Update/Delete)
│   ├── Events/               # Eventos de dominio (categorías por ahora)
│   ├── Http/
│   │   ├── Controllers/      # Entrada HTTP, una subcarpeta por módulo
│   │   └── Requests/         # Form Requests, una subcarpeta por módulo
│   ├── Imports/              # Clases para Excel (maatwebsite/excel)
│   ├── Models/               # Modelos Eloquent
│   ├── Providers/            # Service Providers (sólo AppServiceProvider)
│   ├── Queries/              # Casos de uso de lectura (GetAllX / GetXById)
│   └── Services/             # Capa de orquestación por módulo
├── bootstrap/
│   ├── app.php               # Configuración de Laravel 12 (rutas, middleware, excepciones)
│   ├── providers.php         # Providers específicos de la app
│   └── cache/                # Caché de bootstrap (no tocar)
├── config/                   # Archivos de configuración estándar Laravel
├── database/
│   ├── migrations/           # Migraciones ordenadas por timestamp
│   ├── seeders/              # Datos iniciales (roles + usuario test)
│   └── factories/            # Sólo UserFactory actualmente
├── docker/                   # Script entrypoint.sh usado por Dockerfile
├── docker-compose.yml        # Levanta la app + MySQL 8
├── Dockerfile                # Imagen PHP 8.4-cli con extensiones necesarias
├── public/                   # Punto de entrada web (index.php)
├── resources/
│   ├── css/ app.css
│   ├── js/  app.js bootstrap.js
│   └── views/ exampleView.blade.php
├── routes/
│   ├── api.php               # Rutas REST (apiPrefix 'api')
│   ├── console.php           # Comandos programados (vacío)
│   └── web.php               # Ruta raíz informativa (JSON)
├── storage/                  # Logs, caché, sesiones, uploads
├── tests/
│   ├── Feature/              # Tests funcionales (HTTP)
│   ├── Unit/                 # Tests unitarios
│   ├── Pest.php              # Configuración global de Pest
│   └── TestCase.php          # Clase base
├── docs/                     # Esta documentación
├── AGENTS.md                 # Reglas de desarrollo (también usadas por Claude/OpenCode)
├── CLAUDE.md                 # Instrucciones adicionales para Claude
├── composer.json
├── package.json
├── phpunit.xml
└── vite.config.js
```

## 🧩 `app/` en detalle

### `app/Http/Controllers/`

Una **subcarpeta por módulo** con un único Controller que concentra los verbos REST.

| Subcarpeta | Controller | Prefijo ruta |
|------------|-----------|--------------|
| `Auth/` | `AuthController.php` | `api/auth` |
| `Categorias/` | `CategoriaController.php` | `api/v1/categorias` |
| `Compras/` | `CompraController.php` | `api/v1/compras` |
| `Productos/` | `ProductoController.php` (+ `importarProductos`) | `api/v1/productos` |
| `Proveedores/` | `ProveedorController.php` | `api/v1/proveedores` |
| `Ventas/` | `VentaController.php` | `api/v1/ventas` |
| `Inventario/` | *(vacía)* | Reservada para endpoints del módulo |

> **Convención:** añade una subcarpeta nueva aquí por cada módulo nuevo. Ver `docs/07-como-crear-un-nuevo-modulo.md`.

### `app/Http/Requests/`

Mismo patrón que Controllers: una subcarpeta por módulo con `Store…Request` y `Update…Request`. Excepciones:

- `app/Http/Requests/Productos/ImportExcelRequest.php` para `POST /productos/importar`.

### `app/Services/`

Un Service por módulo. Inyecta todas sus Queries y Actions vía constructor.

| Módulo | Service |
|--------|---------|
| Auth | `AuthService` |
| Categorias | `CategoriaService` |
| Compras | `CompraService` |
| Inventario | `MovimientoInventarioService` (no sigue el patrón CRUD estándar) |
| Productos | `ProductoService` (+ método `importar()`) |
| Proveedores | `ProveedorService` |
| Ventas | `VentaService` |

### `app/Queries/`

Sólo clases de **lectura**. Una carpeta por módulo excepto Inventario (vacía). En módulos CRUD hay exactamente:

- `GetAllX.php` con `handle(?string $estado = null)`.
- `GetXById.php` con `handle(int $id)`.

### `app/Actions/`

Sólo clases de **escritura**. Patrón idéntico para todos los módulos:

- `CreateXAction.php` con `handle(array $data[, extras]): Model`.
- `UpdateXAction.php` con `handle(Model $model, array $data[, extras]): Model`.
- `DeleteXAction.php` con `handle(Model $model): void`.

### `app/Events/`

Carpeta `Categorias/` con tres eventos base como referencia:

- `CategoriaCreadaEvent`
- `CategoriaActualizadaEvent`
- `CategoriaEliminadaEvent`

Aún no tienen listeners registrados. Ver `docs/08-como-anadir-eventos-y-listeners.md`.

### `app/Imports/`

- `ProductosImport.php`: clase de maatwebsite/excel que procesa hojas y crea productos. Usada por `ProductoService::importar()`.

### `app/Models/`

13 modelos: `Categoria`, `Cliente`, `Compra`, `DetalleCompra`, `DetalleVenta`, `LogSistema`, `MovimientoInventario`, `Producto`, `Proveedor`, `Role`, `User`, `UsuarioRol`, `Venta`.

> `UsuarioRol` no es un modelo Eloquent (representa la tabla pivote); los demás sí.

### `app/Providers/`

- `AppServiceProvider.php`: contenedor global sin bindings personalizados todavía.

## 📦 `database/`

### `database/migrations/`

Orden de ejecución (los timestamps determinan el orden):

1. `0001_01_01_000000_create_users_table.php` (Laravel default)
2. `0001_01_01_000001_create_cache_table.php`
3. `0001_01_01_000002_create_jobs_table.php`
4. `2024_01_01_000001_create_roles_table.php`
5. `2024_01_01_000002_create_usuario_rol_table.php`
6. `2024_01_01_000003_create_categorias_table.php`
7. `2024_01_01_000004_create_productos_table.php`
8. `2024_01_01_000005_create_clientes_table.php`
9. `2024_01_01_000006_create_proveedores_table.php`
10. `2024_01_01_000007_create_movimientos_inventario_table.php`
11. `2024_01_01_000008_create_ventas_table.php`
12. `2024_01_01_000009_create_detalle_ventas_table.php`
13. `2024_01_01_000010_create_compras_table.php`
14. `2024_01_01_000011_create_detalle_compras_table.php`
15. `2024_01_01_000012_create_logs_sistema_table.php`
16. `2026_03_16_205524_create_personal_access_tokens_table.php` (Sanctum)
17. `2026_03_17_184108_add_observacion_to_movimientos_inventario_table.php`
18. `2026_05_12_164251_add_prefijo_to_categorias_table.php`
19. `2026_05_12_191918_make_codigo_nullable_in_productos_table.php`

### `database/seeders/`

- `DatabaseSeeder.php` llama a `RoleSeeder` y crea el usuario `test@example.com`.
- `RoleSeeder.php` inserta los roles `admin`, `cliente`, `vendedor`.

### `database/factories/`

Sólo `UserFactory.php`. **Pendiente:** crear factories para los demás modelos (recomendado para tests).

## 🧪 `tests/`

| Carpeta | Contenido actual | Recomendación |
|---------|------------------|---------------|
| `tests/Unit/` | `ExampleTest.php` | Crear tests unitarios de Services/Actions. |
| `tests/Feature/` | `ExampleTest.php` | Crear tests de endpoints `api/v1/…`. |
| `tests/Pest.php` | Carga `pest-plugin-laravel`. | Añadir helpers reutilizables. |
| `tests/TestCase.php` | Base de Laravel. | Sin cambios. |

## ⚙️ `config/`

Archivos estándar de Laravel: `app.php`, `auth.php`, `cache.php`, `database.php`, `filesystems.php`, `logging.php`, `mail.php`, `queue.php`, `sanctum.php`, `services.php`, `session.php`. **No añadir nuevos aquí** sin aprobación; primero revisar si Laravel ya provee el hook necesario.

## 🪝 `bootstrap/`

- `app.php`: punto único de configuración (rutas, middleware, excepciones). En Laravel 12 reemplaza a `Kernel.php`.
- `providers.php`: registro de providers específicos (vacío salvo `App\Providers\AppServiceProvider`).

## 🐳 `docker/`

- `entrypoint.sh`: script de inicio (instalación automática de dependencias + arranque de PHP-FPM/CLI en `php artisan serve`).

## 🚏 `routes/`

| Archivo | Estado |
|---------|--------|
| `api.php` | Rutas REST versionadas con `auth:sanctum`. |
| `web.php` | Ruta raíz `GET /` con mensaje JSON. |
| `console.php` | Vacío (hooks de scheduler). |
