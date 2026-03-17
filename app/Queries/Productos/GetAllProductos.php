<?php

namespace App\Queries\Productos;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;

class GetAllProductos
{
    public function handle(?string $estado = null): Collection
    {
        $query = Producto::with('categoria');

        if ($estado !== null) {
            $query->where('estado', $estado);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
