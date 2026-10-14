# 09 · Cómo añadir Jobs y Queues

Los **Jobs** son unidades de trabajo diferibles (envío de correos, generación de PDFs, sincronizaciones con servicios externos, etc.). Laravel ya tiene la tabla `jobs` migrada y el archivo de configuración `config/queue.php` listo.

## 🧭 ¿Cuándo conviene un Job?

- Proceso que tarde más de unos cientos de milisegundos (enviar emails, llamar a APIs externas, generar reportes PDF).
- Tareas que quieras reintentar automáticamente (`tries`, `backoff`).
- Procesos programados en momentos específicos (combinar con `app/Console/Kernel.php` o `routes/console.php`).

## ⚙️ Configuración

`config/queue.php` está listo. En `.env` elige la conexión:

```dotenv
QUEUE_CONNECTION=database
# o
QUEUE_CONNECTION=redis
```

Si eliges `database`, la tabla `jobs` (creada por `0001_01_01_000002_create_jobs_table.php`) ya existe.

Para procesar los jobs:

```bash
php artisan queue:work
# En desarrollo con reinicio automático:
php artisan queue:listen
```

## ✍️ Crear un Job

```bash
php artisan make:job Ventas/GenerarFacturaPdfJob
```

### `app/Jobs/Ventas/GenerarFacturaPdfJob.php`

```php
<?php

namespace App\Jobs\Ventas;

use App\Models\Venta;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerarFacturaPdfJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 10; // segundos entre reintentos

    public function __construct(
        public readonly Venta $venta,
    ) {}

    public function handle(): void
    {
        // Aquí: generar PDF, guardar en storage, notificar al cliente...
        // Log temporal para verificar:
        \Log::info('Factura generada para venta #'.$this->venta->numero_factura);
    }

    public function failed(Throwable $e): void
    {
        // Se ejecuta tras el último intento. Útil para registrar en logs_sistema.
        \Log::error('Falló generación de factura: '.$e->getMessage());
    }
}
```

## 🚀 Despachar el Job

Desde la `Action` correspondiente (por ejemplo `CreateVentaAction` cuando la venta queda `completado`):

```php
use App\Jobs\Ventas\GenerarFacturaPdfJob;

return DB::transaction(function () use (...) {
    // ... lógica existente ...

    if (($data['estado'] ?? 'pendiente') === 'completado') {
        // Disparar el job en cola
        GenerarFacturaPdfJob::dispatch($venta);
    }

    return $venta;
});
```

También puedes:

- `->delay(now()->addMinutes(5))` para ejecución diferida.
- `->onQueue('nombre_cola')` para enrutar.
- `->onConnection('redis')` para usar otro driver.

## 🧪 Probar el Job

```php
<?php

use App\Jobs\Ventas\GenerarFacturaPdfJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;

it('encola el job de generación de factura', function () {
    Queue::fake();

    GenerarFacturaPdfJob::dispatch(\App\Models\Venta::factory()->create());

    Queue::assertPushed(GenerarFacturaPdfJob::class);
});
```

Para tests sincrónicos, usa `Bus::fake([GenerarFacturaPdfJob::class])` y `Bus::assertDispatched(GenerarFacturaPdfJob::class, fn ($job) => true)`.

## 🧾 Listeners en cola (relación con eventos)

`ShouldQueue` aplica también a los Listeners (ver `docs/08-como-anadir-eventos-y-listeners.md`). Implementación mínima:

```php
class NotificarStockBajo implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(VentaCreadaEvent $event): void
    {
        // ...
    }
}
```

## ⏰ Scheduler opcional

Si quieres ejecutar algo periódicamente, define el schedule en `routes/console.php` (que reemplaza al antiguo `App\Console\Kernel`):

```php
use Illuminate\Support\Facades\Schedule;

Schedule::job(new \App\Jobs\Inventario\RotacionDiariaJob)
    ->dailyAt('02:00')
    ->name('rotacion-diaria')
    ->withoutOverlapping();
```

Y en el cron del servidor añade una sola línea:

```cron
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

En `composer dev` la cola ya se levanta con `queue:listen`, por lo que los jobs corren sin más durante el desarrollo.

## ✅ Checklist

- [ ] Conexión de cola configurada en `.env` (`QUEUE_CONNECTION`).
- [ ] Job implementa `ShouldQueue` y define `tries`/`backoff`.
- [ ] `handle()` idempotente (re-ejecutable sin corromper datos).
- [ ] `failed()` para manejar el agotamiento de reintentos.
- [ ] Despachado con `Job::dispatch()` desde la `Action` o un Listener.
- [ ] `php artisan queue:work` corriendo en producción (supervisor o systemd).
- [ ] Tests con `Queue::fake()` o `Bus::fake()`.
