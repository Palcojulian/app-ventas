<?php

namespace App\Actions\Ventas;

use App\Models\Producto;
use App\Models\Venta;
use App\Services\Inventario\MovimientoInventarioService;

class DeleteVentaAction
{
    public function handle(int $id_venta, int $idUsuario): void
    {
        $movimientoInventarioService = new MovimientoInventarioService;
        $venta = Venta::findOrFail($id_venta);    

        if ($venta->estado === 'completado') {
            foreach ($venta->detalleVentas as $detalle) {
                $producto = Producto::find($detalle->id_producto);
                $producto->increment('stock_actual', $detalle->cantidad);

                $movimientoInventarioService->registrarMovimiento(
                    $detalle->id_producto,
                    'venta',
                    $detalle->cantidad,
                    $idUsuario,
                    'Venta #'.$venta->numero_factura.' - Eliminada'
                );
            }
        }

        $venta->delete();
    }
}
