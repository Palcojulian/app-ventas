<?php

namespace App\Actions\Compras;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Services\Inventario\MovimientoInventarioService;
use Illuminate\Support\Facades\DB;

class CreateCompraAction
{
    public function handle(array $data, int $idUsuario): Compra
    {
        $movimientoInventarioService = new MovimientoInventarioService;

        return DB::transaction(function () use ($data, $idUsuario, $movimientoInventarioService) {
            $total = 0;

            foreach ($data['detalles'] as $detalle) {
                $total += $detalle['cantidad'] * $detalle['costo_unitario'];
            }

            $compra = Compra::create([
                'id_proveedor' => $data['id_proveedor'],
                'total' => $total,
                'estado' => $data['estado'] ?? 'pendiente',
            ]);

            foreach ($data['detalles'] as $detalle) {
                DetalleCompra::create([
                    'id_compra' => $compra->id,
                    'id_producto' => $detalle['id_producto'],
                    'cantidad' => $detalle['cantidad'],
                    'costo_unitario' => $detalle['costo_unitario'],
                    'total' => $detalle['cantidad'] * $detalle['costo_unitario'],
                ]);

                if (($data['estado'] ?? 'pendiente') === 'completado') {
                    $producto = Producto::find($detalle['id_producto']);
                    $producto->increment('stock_actual', $detalle['cantidad']);

                    $movimientoInventarioService->registrarMovimiento(
                        $detalle['id_producto'],
                        'compra',
                        $detalle['cantidad'],
                        $idUsuario,
                        'Ingreso a inventario compra #'.$compra->id
                    );
                }
            }

            return $compra;
        });
    }
}
