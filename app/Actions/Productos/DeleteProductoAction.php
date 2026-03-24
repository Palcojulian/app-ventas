<?php

namespace App\Actions\Productos;

use App\Models\Producto;

class DeleteProductoAction
{
    public function handle(Producto $producto): void
    {
        $producto->delete();
    }
}
