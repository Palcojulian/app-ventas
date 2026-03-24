<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'API de App Ventas',
        'version' => '1.0',
    ]);
});
