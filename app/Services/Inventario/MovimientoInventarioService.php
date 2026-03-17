<?php

namespace App\Services\Inventario;

use App\Models\MovimientoInventario;
use App\Models\Producto;

class MovimientoInventarioService
{
    public function registrarMovimiento(
        int $idProducto,
        string $tipoMovimiento,
        int $cantidad,
        int $idUsuario
    ): MovimientoInventario {
        $producto = Producto::findOrFail($idProducto);
        $stockAnterior = $producto->stock_actual;

        if ($tipoMovimiento === 'venta' || $tipoMovimiento === 'ajuste_inventario' && $cantidad < 0) {
            $stockNuevo = $stockAnterior - abs($cantidad);
        } else {
            $stockNuevo = $stockAnterior + $cantidad;
        }

        return MovimientoInventario::create([
            'id_producto' => $idProducto,
            'tipo_movimiento' => $tipoMovimiento,
            'cantidad' => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $stockNuevo,
            'id_usuario' => $idUsuario,
        ]);
    }
}
