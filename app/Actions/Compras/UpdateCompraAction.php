<?php

namespace App\Actions\Compras;

use App\Models\Compra;
use App\Models\Producto;
use App\Services\Inventario\MovimientoInventarioService;
use Illuminate\Support\Facades\DB;

class UpdateCompraAction
{
    public function handle(int $id_compra, array $data, int $idUsuario): Compra
    {
        return DB::transaction(function () use ($id_compra, $data, $idUsuario) {
            $compra = Compra::findOrFail($id_compra);
            $movimientoInventarioService = new MovimientoInventarioService;
            $estadoAnterior = $compra->estado;
            $nuevoEstado = $data['estado'] ?? $compra->estado;

            $compra->update([
                'estado' => $nuevoEstado,
            ]);

            if ($estadoAnterior !== 'completado' && $nuevoEstado === 'completado') {
                foreach ($compra->detalleCompras as $detalle) {
                    $producto = Producto::find($detalle->id_producto);
                    $producto->increment('stock_actual', $detalle->cantidad);

                    $movimientoInventarioService->registrarMovimiento(
                        $detalle->id_producto,
                        'compra',
                        $detalle->cantidad,
                        $idUsuario,
                        'Compra #'.$compra->id.' - Completada'
                    );
                }
            }

            if ($estadoAnterior === 'completado' && $nuevoEstado !== 'completado') {
                foreach ($compra->detalleCompras as $detalle) {
                    $producto = Producto::find($detalle->id_producto);
                    $producto->decrement('stock_actual', $detalle->cantidad);

                    $movimientoInventarioService->registrarMovimiento(
                        $detalle->id_producto,
                        'compra',
                        -$detalle->cantidad,
                        $idUsuario,
                        'Compra #'.$compra->id.' - '.ucfirst($nuevoEstado)
                    );
                }
            }

            return $compra->fresh();
        });
    }
}
