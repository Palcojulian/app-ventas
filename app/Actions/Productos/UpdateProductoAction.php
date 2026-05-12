<?php

namespace App\Actions\Productos;

use App\Models\Producto;

class UpdateProductoAction
{
    public function handle(Producto $producto, array $data): Producto
    {
        $producto->update([
            'codigo' => $data['codigo'] ?? $producto->codigo,
            'nombre' => $data['nombre'] ?? $producto->nombre,
            'descripcion' => $data['descripcion'] ?? $producto->descripcion,
            'id_categoria' => $data['id_categoria'] ?? $producto->id_categoria,
            'precio_venta' => $data['precio_venta'] ?? $producto->precio_venta,
            'costo' => $data['costo'] ?? $producto->costo,
            'stock_actual' => $data['stock_actual'] ?? $producto->stock_actual,
            'stock_minimo' => $data['stock_minimo'] ?? $producto->stock_minimo,
            'unidad_medida' => $data['unidad_medida'] ?? $producto->unidad_medida,
            'estado' => $data['estado'] ?? $producto->estado,
        ]);

    


        return $producto->fresh();
    }
}
