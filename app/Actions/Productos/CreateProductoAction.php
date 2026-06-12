<?php

namespace App\Actions\Productos;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Support\Facades\Log;

class CreateProductoAction
{
    public function handle(array $data): Producto
    {
        $productos_creados = count(Producto::where("id_categoria", "=", $data['id_categoria'])->get()) + 1;
        $prefijo = Categoria::find($data['id_categoria'])->prefijo;
        $data['codigo'] = "{$prefijo}-" . str_pad($productos_creados, 4, '0', STR_PAD_LEFT);
        $data['estado'] =  $data['estado'] ?? 'activo';
        return Producto::create($data)->refresh();
    }
}
