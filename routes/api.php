<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Categorias\CategoriaController;
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
        
    });

    

});
