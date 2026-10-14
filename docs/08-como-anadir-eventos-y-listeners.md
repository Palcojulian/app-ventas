# 08 · Cómo añadir eventos y listeners

Los **eventos** son la pieza clave para desacoplar efectos secundarios (notificaciones, logs en `logs_sistema`, llamadas a servicios externos, etc.) de la lógica central de tu `Action`. La carpeta `app/Events/Categorias/` ya contiene tres eventos base que sirven como referencia.

## 🧭 ¿Cuándo tiene sentido crear un evento?

- Cuando la `Action` ya está cerca de la "regla de oro" de hacer **una sola cosa** pero necesitas **observar** algo (ej. notificar stock bajo, generar log, refrescar caché, emitir WebSocket).
- Cuando múltiples partes deben reaccionar a una misma acción.
- Cuando necesitas introducir una cola sin tocar la `Action` (un listener `ShouldQueue`).

## 🧱 Estructura recomendada

```
app/
├── Events/
│   └── Ventas/
│       ├── VentaCreadaEvent.php
│       ├── VentaActualizadaEvent.php
│       └── VentaEliminadaEvent.php
└── Listeners/
    └── Ventas/
        ├── RegistrarVentaEnLog.php
        └── NotificarStockBajo.php
```

El proyecto aún **no tiene** `app/Listeners/`; créala la primera vez que la necesites.

## ✍️ Crear un evento

```bash
php artisan make:event Ventas/VentaCreadaEvent
```

### `app/Events/Ventas/VentaCreadaEvent.php`

```php
<?php

namespace App\Events\Ventas;

use App\Models\Venta;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VentaCreadaEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Venta $venta,
        public readonly int $idUsuario,
    ) {}
}
```

> Los eventos suelen llevar solo DTOs (`readonly`). No es obligatorio extender ninguna clase; `Dispatchable` es cómodo para `event(new MiEvento(...))`.

## ✍️ Crear un listener

```bash
php artisan make:listener Ventas/RegistrarVentaEnLog
```

### `app/Listeners/Ventas/RegistrarVentaEnLog.php`

```php
<?php

namespace App\Listeners\Ventas;

use App\Events\Ventas\VentaCreadaEvent;
use App\Models\LogSistema;

class RegistrarVentaEnLog
{
    public function handle(VentaCreadaEvent $event): void
    {
        LogSistema::create([
            'id_usuario'  => $event->idUsuario,
            'accion'      => 'venta.creada',
            'descripcion' => 'Venta #'.$event->venta->numero_factura.' creada por $'.number_format($event->venta->total, 2),
        ]);
    }
}
```

## 🔌 Conectar evento ↔ listener (Event Discovery automático)

En Laravel 11/12 los listeners se autodescubren si:

- Implementan el método `handle(EventClass $event)`.
- No hay un método `subscribe()` ni configuración manual en provider.

Por defecto **basta con crear las clases**, sin tocar `AppServiceProvider`. Si prefieres hacerlo explícito:

```php
// app/Providers/AppServiceProvider.php
use App\Events\Ventas\VentaCreadaEvent;
use App\Listeners\Ventas\RegistrarVentaEnLog;
use Illuminate\Support\Facades\Event;

public function boot(): void
{
    Event::listen(VentaCreadaEvent::class, RegistrarVentaEnLog::class);
}
```

## 🚀 Disparar el evento desde una `Action`

Siguiendo el patrón, edita por ejemplo `CreateVentaAction.php` justo antes del `return $venta`:

```php
use App\Events\Ventas\VentaCreadaEvent;

return DB::transaction(function () use (...) {
    // ... existing code ...

    event(new VentaCreadaEvent($venta, $idUsuario));

    return $venta;
});
```

## 🚚 Convertir el listener en cola (sin tocar la `Action`)

Si el listener implica trabajo pesado (enviar mail, llamar a API externa), basta con implementar `ShouldQueue`:

```php
<?php

namespace App\Listeners\Ventas;

use App\Events\Ventas\VentaCreadaEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificarStockBajo implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(VentaCreadaEvent $event): void
    {
        // ... llamar API / enviar mail ...
    }
}
```

Y asegúrate de tener `QUEUE_CONNECTION=database` o `redis` en `.env` y ejecutar `php artisan queue:work`.

## 🧾 Ejemplo: registrar cada cambio de Categoría en `logs_sistema`

`app/Events/Categorias/` ya define `CategoriaCreadaEvent`, `CategoriaActualizadaEvent` y `CategoriaEliminadaEvent`. Para activarlos:

### Listener genérico

```php
<?php

namespace App\Listeners\Categorias;

use App\Models\LogSistema;

abstract class RegistrarCategoriaEnLog
{
    protected string $accion = '';

    protected function registrar(int $idUsuario, string $detalle): void
    {
        LogSistema::create([
            'id_usuario'  => $idUsuario,
            'accion'      => $this->accion,
            'descripcion' => $detalle,
        ]);
    }
}
```

```php
<?php

namespace App\Listeners\Categorias;

use App\Events\Categorias\CategoriaCreadaEvent;

class CategoriaCreadaEnLog extends RegistrarCategoriaEnLog
{
    protected string $accion = 'categoria.creada';

    public function handle(CategoriaCreadaEvent $event): void
    {
        $this->registrar(
            $event->idUsuario,
            "Categoría {$event->categoria->nombre} creada."
        );
    }
}
```

(Repite el patrón para `Actualizada` y `Eliminada`).

Y enlaza en `AppServiceProvider::boot()`:

```php
Event::listen(\App\Events\Categorias\CategoriaCreadaEvent::class,    \App\Listeners\Categorias\CategoriaCreadaEnLog::class);
Event::listen(\App\Events\Categorias\CategoriaActualizadaEvent::class,\App\Listeners\Categorias\CategoriaActualizadaEnLog::class);
Event::listen(\App\Events\Categorias\CategoriaEliminadaEvent::class, \App\Listeners\Categorias\CategoriaEliminadaEnLog::class);
```

## 🧪 Probar un listener con Pest

```php
<?php

use App\Events\Ventas\VentaCreadaEvent;
use App\Listeners\Ventas\RegistrarVentaEnLog;
use App\Models\Venta;

it('registra la venta en logs_sistema', function () {
    Event::fake();

    $venta = Venta::factory()->create();

    event(new VentaCreadaEvent($venta, $venta->id_usuario));

    Event::assertDispatched(VentaCreadaEvent::class);
});
```

Para listeners síncronos, ejecuta el handle y verifica el resultado en BD.

## 🧭 Resumen del flujo

```mermaid
flowchart LR
    A[Action] -->|event(new XEvent)| B[Laravel Event Bus]
    B --> C[Listener A: LogSistema]
    B --> D[Listener B: Notificar stock]
    C --> E[(DB)]
    D --> F[(Cola / Mail / API)]
```

## ✅ Checklist

- [ ] Evento en `app/Events/<Modulo>/` con propiedades `readonly`.
- [ ] Listener en `app/Listeners/<Modulo>/` con método `handle(EventClass $e)`.
- [ ] Disparado con `event(new XEvent(...))` desde la `Action` correspondiente.
- [ ] Si es trabajo pesado: implementar `ShouldQueue`.
- [ ] Si la auto-revelación no es suficiente, registrar en `AppServiceProvider`.
- [ ] Test que verifica que el evento se dispara (o que el listener produce el efecto esperado).
