<?php

namespace App\Actions\Compras;

use App\Models\Compra;
use App\Models\Producto;
use App\Services\Inventario\MovimientoInventarioService;

class DeleteCompraAction
{
    public function handle(Compra $compra, int $idUsuario): void
    {
        $movimientoInventarioService = new MovimientoInventarioService;

        if ($compra->estado === 'completado') {
            foreach ($compra->detalleCompras as $detalle) {
                $producto = Producto::find($detalle->id_producto);
                $producto->decrement('stock_actual', $detalle->cantidad);

                $movimientoInventarioService->registrarMovimiento(
                    $detalle->id_producto,
                    'compra',
                    -$detalle->cantidad,
                    $idUsuario
                );
            }
        }

        $compra->delete();
    }
}
