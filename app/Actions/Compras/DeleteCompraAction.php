<?php

namespace App\Actions\Compras;

use App\Models\Compra;
use App\Models\Producto;
use App\Services\Inventario\MovimientoInventarioService;
use Illuminate\Support\Facades\DB;

class DeleteCompraAction
{
    public function handle(int $id_compra, int $idUsuario): void
    {
        DB::transaction(function () use ($id_compra, $idUsuario) {
            $compra = Compra::findOrFail($id_compra);
            $movimientoInventarioService = new MovimientoInventarioService;

            if ($compra->estado === 'completado') {
                foreach ($compra->detalleCompras as $detalle) {
                    $producto = Producto::find($detalle->id_producto);
                    $producto->decrement('stock_actual', $detalle->cantidad);

                    $movimientoInventarioService->registrarMovimiento(
                        $detalle->id_producto,
                        'compra',
                        -$detalle->cantidad,
                        $idUsuario,
                        'Compra #'.$compra->id.' - Eliminada'
                    );
                }
            }

            $compra->delete();
        });
    }
}
