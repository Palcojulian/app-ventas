<?php

namespace App\Actions\Productos;

use App\Models\Producto;

class CreateProductoAction
{
    public function handle(array $data): Producto
    {
        return Producto::create([
            'codigo' => $data['codigo'],
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'id_categoria' => $data['id_categoria'],
            'precio_venta' => $data['precio_venta'],
            'costo' => $data['costo'] ?? 0,
            'stock_actual' => $data['stock_actual'] ?? 0,
            'stock_minimo' => $data['stock_minimo'] ?? 0,
            'unidad_medida' => $data['unidad_medida'] ?? null,
            'estado' => $data['estado'] ?? 'activo',
        ]);
    }
}
