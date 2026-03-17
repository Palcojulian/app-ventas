<?php

namespace App\Queries\Productos;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;

class GetAllProductos
{
    public function handle(?string $estado = null): Collection
    {
        $query = Producto::from('productos as p')->selectRaw("
            p.*,
            c.nombre as categoria
        ")->join("categorias as c","c.id","=","p.id_categoria")
        ->when(
            $estado, 
            fn($query, $value) => 
            $query->where('p.estado', "=", $value))
        ->orderBy('created_at', 'desc')->get();
        
        return $query;
    }
}
