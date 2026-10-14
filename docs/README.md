# Documentación de `app-ventas`

Bienvenido a la documentación del proyecto **app-ventas**. Este repositorio implementa una **API REST en Laravel 12** para la gestión integral de un sistema de ventas: productos, categorías, proveedores, clientes, compras, ventas e inventario.

## 📚 Índice de documentación

| # | Documento | Descripción |
|---|-----------|-------------|
| 01 | [Arquitectura](./01-arquitectura.md) | Capas del sistema, flujo de petición, stack y principios. |
| 02 | [Estructura de carpetas](./02-estructura-carpetas.md) | Árbol anotado de `app/`, `database/`, `routes/`, `config/`. |
| 03 | [Modelos y base de datos](./03-modelos-y-base-de-datos.md) | Diagrama ER y descripción de cada tabla. |
| 04 | [Autenticación y roles](./04-autenticacion-y-roles.md) | Sanctum, flujo `register` / `login` y tabla `usuario_rol`. |
| 05 | [Endpoints de la API](./05-endpoints-api.md) | Referencia REST completa (rutas, payloads, respuestas). |
| 06 | [Convenciones de código](./06-convenciones-de-codigo.md) | Reglas PHP, Laravel 12, Pint, PHPDoc y formato JSON. |
| 07 | [Cómo crear un nuevo módulo](./07-como-crear-un-nuevo-modulo.md) | Checklist paso a paso con fragmentos copy-paste. |
| 08 | [Cómo añadir eventos y listeners](./08-como-anadir-eventos-y-listeners.md) | Patrón `app/Events/` y casos de uso. |
| 09 | [Cómo añadir jobs y queues](./09-como-anadir-jobs-y-queues.md) | `ShouldQueue`, tabla `jobs` ya creada. |
| 10 | [Cómo añadir policies y gates](./10-como-anadir-policies-y-gates.md) | Autorización por recurso o rol. |
| 11 | [Testing con Pest](./11-testing-con-pest.md) | Convenciones y plantilla de test. |
| 12 | [Despliegue y Docker](./12-despliegue-y-docker.md) | `Dockerfile`, `docker-compose.yml` y scripts `composer`. |

## 🧰 Stack del proyecto

| Componente | Versión |
|------------|---------|
| PHP | 8.4 |
| Laravel | 12 |
| Sanctum | 4 |
| Pest | 4 |
| PHPUnit | 12 |
| maatwebsite/excel | 3 (importación de productos) |
| MySQL | 8 (vía Docker) |
| Vite / Tailwind 4 | disponibles (no usados en la API) |

## 🧭 Cómo está organizada esta documentación

- Los archivos `01-*` a `05-*` describen **cómo es el proyecto hoy**.
- Los archivos `06-*` a `12-*` describen **cómo extenderlo** manteniendo el mismo estilo.

Si vas a añadir un nuevo módulo, empieza por **`06-convenciones-de-codigo.md`** y luego sigue **`07-como-crear-un-nuevo-modulo.md`**.

## ⚡ Comandos clave

```bash
# Instalar dependencias
composer install
npm install

# Entorno de desarrollo (servidor + cola + pail + vite)
composer dev

# Migrar y sembrar
php artisan migrate --seed

# Levantar con Docker
docker compose up -d

# Formatear PHP modificado
vendor/bin/pint --dirty --format agent

# Ejecutar tests
composer test
# o bien
php artisan test --compact

# Listar rutas
php artisan route:list
```

## 📏 Reglas generales extraídas de `AGENTS.md`

1. Llaves obligatorias en estructuras de control, incluso para una sola línea.
2. Constructor con property promotion de PHP 8 (`public function __construct(public Foo $foo) {}`).
3. Tipos de retorno explícitos en todos los métodos.
4. Claves de enums en `TitleCase` (`FavoritePerson`, `Monthly`).
5. Preferir `PHPDoc` sobre comentarios inline.
6. Usar Eloquent con relaciones tipadas; evitar `DB::` salvo en transacciones.
7. Eager loading (`->with()`) para evitar N+1.
8. Usar siempre rutas con nombre y la función `route()`.
9. Usar `config('x.y')` en lugar de `env()` fuera de archivos de configuración.
10. En Laravel 12, los middleware se registran en `bootstrap/app.php` (no en `app/Http/Kernel.php`).
11. Los modelos definen los casts en el método `casts()`, no en la propiedad `$casts`.
12. Tras modificar PHP, ejecutar `vendor/bin/pint --dirty --format agent`.
13. No crear scripts de verificación puntuales cuando existan tests que los cubran.

## 🔗 Recursos externos

- [Documentación oficial de Laravel 12](https://laravel.com/docs/12.x)
- [Laravel Sanctum](https://laravel.com/docs/12.x/sanctum)
- [Pest 4](https://pestphp.com/docs)
- [maatwebsite/excel 3](https://docs.laravel-excel.com/3.1)
