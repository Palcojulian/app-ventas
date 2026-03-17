<?php

namespace App\Queries\Productos;

use App\Models\Producto;

class GetProductoById
{
    public function handle(int $id): Producto
    {
        return Producto::with('categoria')->findOrFail($id);
    }
}
