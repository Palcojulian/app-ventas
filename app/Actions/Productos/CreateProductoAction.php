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

        return Producto::create([
            'codigo' => "{$prefijo}-" . str_pad($productos_creados, 4, '0', STR_PAD_LEFT),
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'id_categoria' => $data['id_categoria'],
            'precio_venta' => $data['precio_venta'],
            'costo' => $data['costo'] ?? 0,
            'stock_actual' => $data['stock_actual'] ?? 0,
            'stock_minimo' => $data['stock_minimo'] ?? 0,
            'unidad_medida' => $data['unidad_medida'] ?? null,
            'estado' => $data['estado'] ?? 'activo',
        ])->refresh();
    }
}
