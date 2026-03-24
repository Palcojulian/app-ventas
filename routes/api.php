<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Categorias\CategoriaController;
use App\Http\Controllers\Compras\CompraController;
use App\Http\Controllers\Productos\ProductoController;
use App\Http\Controllers\Proveedores\ProveedorController;
use App\Http\Controllers\Ventas\VentaController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::prefix('v1')->group(function () {
        Route::prefix('categorias')->group(function () {
            Route::get('/', [CategoriaController::class, 'index']);
            Route::post('/', [CategoriaController::class, 'store']);
            Route::get('/{id}', [CategoriaController::class, 'show']);
            Route::put('/{id}', [CategoriaController::class, 'update']);
            Route::delete('/{id}', [CategoriaController::class, 'destroy']);
        });

        Route::prefix('proveedores')->group(function () {
            Route::get('/', [ProveedorController::class, 'index']);
            Route::post('/', [ProveedorController::class, 'store']);
            Route::get('/{id}', [ProveedorController::class, 'show']);
            Route::put('/{id}', [ProveedorController::class, 'update']);
            Route::delete('/{id}', [ProveedorController::class, 'destroy']);
        });

        Route::prefix('productos')->group(function () {
            Route::get('/', [ProductoController::class, 'index']);
            Route::post('/', [ProductoController::class, 'store']);
            Route::get('/{id}', [ProductoController::class, 'show']);
            Route::put('/{id}', [ProductoController::class, 'update']);
            Route::delete('/{id}', [ProductoController::class, 'destroy']);
            Route::post('/importar', [ProductoController::class, 'importarProductos']);
        });

        Route::prefix('compras')->group(function () {
            Route::get('/', [CompraController::class, 'index']);
            Route::post('/', [CompraController::class, 'store']);
            Route::get('/{id}', [CompraController::class, 'show']);
            Route::put('/{id}', [CompraController::class, 'update']);
            Route::delete('/{id}', [CompraController::class, 'destroy']);
        });

        Route::prefix('ventas')->group(function () {
            Route::get('/', [VentaController::class, 'index']);
            Route::post('/', [VentaController::class, 'store']);
            Route::get('/{id}', [VentaController::class, 'show']);
            Route::put('/{id}', [VentaController::class, 'update']);
            Route::delete('/{id}', [VentaController::class, 'destroy']);
        });

    });

});
