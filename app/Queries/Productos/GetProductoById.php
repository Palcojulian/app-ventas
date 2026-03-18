<?php

namespace App\Queries\Productos;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;

class GetProductoById
{
    public function handle(int $id): Producto
    {   
        $producto = Producto::query()->selectRaw('
            productos.*, 
            c.nombre as categoria
        ')
        ->join('categorias as c', 'c.id', '=','productos.id_categoria',)
        ->where('productos.id','=', $id)->first();
        
        return $producto;
    }
}
