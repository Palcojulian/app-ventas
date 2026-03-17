<?php

namespace App\Actions\Ventas;

use App\Models\Producto;
use App\Models\Venta;
use App\Services\Inventario\MovimientoInventarioService;

class UpdateVentaAction
{
    public function handle(Venta $venta, array $data, int $idUsuario): Venta
    {
        $movimientoInventarioService = new MovimientoInventarioService;
        $estadoAnterior = $venta->estado;
        $nuevoEstado = $data['estado'] ?? $venta->estado;

        $venta->update([
            'estado' => $nuevoEstado,
            'metodo_pago' => $data['metodo_pago'] ?? $venta->metodo_pago,
        ]);

        if ($estadoAnterior !== 'completado' && $nuevoEstado === 'completado') {
            foreach ($venta->detalleVentas as $detalle) {
                $producto = Producto::find($detalle->id_producto);
                $producto->decrement('stock_actual', $detalle->cantidad);

                $movimientoInventarioService->registrarMovimiento(
                    $detalle->id_producto,
                    'venta',
                    -$detalle->cantidad,
                    $idUsuario,
                    'Venta #'.$venta->numero_factura.' - Completada'
                );
            }
        }

        if ($estadoAnterior === 'completado' && $nuevoEstado !== 'completado') {
            foreach ($venta->detalleVentas as $detalle) {
                $producto = Producto::find($detalle->id_producto);
                $producto->increment('stock_actual', $detalle->cantidad);

                $movimientoInventarioService->registrarMovimiento(
                    $detalle->id_producto,
                    'venta',
                    $detalle->cantidad,
                    $idUsuario,
                    'Venta #'.$venta->numero_factura.' - '.ucfirst($nuevoEstado)
                );
            }
        }

        return $venta->fresh();
    }
}
