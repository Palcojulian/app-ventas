# 11 · Testing con Pest

Este proyecto usa **Pest 4** sobre **PHPUnit 12**. Los archivos generados por defecto (`tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`) están pensados solo como smoke tests; aquí verás cómo escribir tests útiles siguiendo el patrón del repositorio.

## 🚀 Comandos

```bash
composer test                       # equivalente a php artisan test
php artisan test --compact          # salida compacta
php artisan test --compact --filter=CategoriaTest
php artisan make:test --pest Productos/ProductoApiTest
php artisan make:test --pest --unit Services/ProductoTest
```

## 📂 Estructura

```
tests/
├── Feature/                # Tests que ejercitan HTTP (endpoints)
│   ├── Productos/
│   └── Ventas/
├── Unit/                   # Tests aislados (clases sueltas)
│   └── Services/
├── Pest.php                # Configuración global (uses, helpers)
└── TestCase.php            # Clase base Laravel
```

Crea subcarpetas por módulo para mantener el orden.

## ⚙️ `tests/Pest.php` — configuración global

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->in('Feature');
uses(TestCase::class)->in('Feature');
```

## 🧪 Plantilla: test de endpoint (`Feature`)

```php
<?php

use App\Models\Producto;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

it('lista productos autenticados', function () {
    Producto::factory()->count(3)->create();

    $this->getJson('/api/v1/productos')
        ->assertOk()
        ->assertJsonCount(3);
});

it('rechaza la creación sin campos obligatorios', function () {
    $this->postJson('/api/v1/productos', [])
        ->assertStatus(422)
        ->assertJsonStructure(['success', 'message', 'errores']);
});

it('crea un producto válido', function () {
    $categoria = \App\Models\Categoria::factory()->create();

    $data = [
        'nombre'       => 'Gaseosa 500ml',
        'id_categoria' => $categoria->id,
        'precio_venta' => 3500,
        'costo'        => 2000,
        'stock_actual' => 100,
        'stock_minimo' => 10,
        'unidad_medida'=> 'unidad',
        'estado'       => 'activo',
    ];

    $this->postJson('/api/v1/productos', $data)
        ->assertCreated()
        ->assertJsonFragment(['nombre' => 'Gaseosa 500ml']);

    expect(Producto::where('nombre', 'Gaseosa 500ml')->exists())->toBeTrue();
});

it('devuelve 404 al buscar un producto inexistente', function () {
    $this->getJson('/api/v1/productos/9999')
        ->assertNotFound()
        ->assertJsonFragment(['error' => 'Producto no encontrado.']);
});
```

## 🧪 Plantilla: test unitario (`Unit`)

```php
<?php

use App\Actions\Productos\CreateProductoAction;

it('autogenera el código cuando no se proporciona', function () {
    $categoria = \App\Models\Categoria::factory()->create(['prefijo' => 'BEB']);

    $producto = (new CreateProductoAction)->handle([
        'nombre'       => 'Limonada',
        'id_categoria' => $categoria->id,
        'precio_venta' => 2000,
        'costo'        => 1000,
    ]);

    expect($producto->codigo)->toMatch('/^BEB-\d{4}$/');
});
```

## 🧪 Test de transacciones e inventario

```php
<?php

use App\Actions\Ventas\CreateVentaAction;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;

it('descuenta stock y registra movimiento al completar la venta', function () {
    $vendedor = User::factory()->create();
    $producto = Producto::factory()->create(['stock_actual' => 50]);
    $cliente  = User::factory()->create();

    $venta = (new CreateVentaAction)->handle([
        'id_cliente' => $cliente->id,
        'estado'     => 'completado',
        'detalles'   => [
            ['id_producto' => $producto->id, 'cantidad' => 5, 'precio_unitario' => 1000],
        ],
    ], $vendedor->id);

    expect($venta->fresh()->total)->toBe(5000.0);
    expect($producto->fresh()->stock_actual)->toBe(45);
    expect(\App\Models\MovimientoInventario::where('id_producto', $producto->id)->exists())->toBeTrue();
});
```

## 🧪 Tests de validación de campos específicos

```php
it('rechaza precio_venta negativo', function () {
    $categoria = \App\Models\Categoria::factory()->create();

    $this->postJson('/api/v1/productos', [
        'nombre'       => 'X',
        'id_categoria' => $categoria->id,
        'precio_venta' => -1,
    ])->assertStatus(422)
      ->assertJsonPath('errores', fn ($s) => str_contains($s, 'precio de venta no puede'));
});
```

## 🧪 Tests con excepciones simuladas

```php
use App\Services\Productos\ProductoService;

it('captura error inesperado y devuelve 500', function () {
    $service = $this->mock(ProductoService::class);
    $service->shouldReceive('getAll')->andThrow(new \RuntimeException('boom'));

    $this->getJson('/api/v1/productos')
        ->assertStatus(500)
        ->assertJsonFragment(['error' => 'Error al obtener los productos.']);
});
```

## 🛠️ Helpers y convenciones

- Usa `Sanctum::actingAs($user)` para autenticar en tests HTTP.
- Para crear muchos registros usa factories con estados personalizados (`factory()->count(50)`).
- Para sobreescribir valores usa `factory()->create(['campo' => 'x'])`.
- Para bases de datos, usa el trait `RefreshDatabase` configurado en `Pest.php`.
- Cuando verifiques JSON, prefiere `assertJsonFragment`, `assertJsonPath`, `assertJsonStructure` sobre inspecciones manuales.

## ✅ Checklist de un test "completo"

- [ ] Smoke 200 / 201 en el happy path.
- [ ] Validación 422 con cuerpo correcto.
- [ ] 404 cuando el recurso no existe.
- [ ] 401 si el endpoint requiere `auth:sanctum` y no se autentica.
- [ ] (Opcional) políticas de autorización 403.
- [ ] (Opcional) transacciones (inventario, logs).
