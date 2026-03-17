<?php

namespace App\Actions\Ventas;

use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\Inventario\MovimientoInventarioService;
use Illuminate\Support\Facades\DB;

class CreateVentaAction
{
    public function handle(array $data, int $idUsuario): Venta
    {
        $movimientoInventarioService = new MovimientoInventarioService;

        return DB::transaction(function () use ($data, $idUsuario, $movimientoInventarioService) {
            $subtotal = 0;
            $impuestos = $data['impuestos'] ?? 0;
            $descuento = $data['descuento'] ?? 0;

            foreach ($data['detalles'] as $detalle) {
                $subtotal += $detalle['cantidad'] * $detalle['precio_unitario'];
            }

            $total = $subtotal + $impuestos - $descuento;

            $numeroFactura = $this->generarNumeroFactura();

            $venta = Venta::create([
                'numero_factura' => $numeroFactura,
                'id_cliente' => $data['id_cliente'],
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'descuento' => $descuento,
                'total' => $total,
                'metodo_pago' => $data['metodo_pago'] ?? null,
                'estado' => $data['estado'] ?? 'pendiente',
                'id_usuario' => $idUsuario,
            ]);

            foreach ($data['detalles'] as $detalle) {
                DetalleVenta::create([
                    'id_venta' => $venta->id,
                    'id_producto' => $detalle['id_producto'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'total' => $detalle['cantidad'] * $detalle['precio_unitario'],
                ]);

                if (($data['estado'] ?? 'pendiente') === 'completado') {
                    $producto = Producto::find($detalle['id_producto']);
                    $producto->decrement('stock_actual', $detalle['cantidad']);

                    $movimientoInventarioService->registrarMovimiento(
                        $detalle['id_producto'],
                        'venta',
                        $detalle['cantidad'],
                        $idUsuario,
                        'Salida inventario por venta #'.$venta->numero_factura
                    );
                }
            }

            return $venta;
        });
    }

    private function generarNumeroFactura(): string
    {
        $ultimaVenta = Venta::orderBy('id', 'desc')->first();
        $numero = $ultimaVenta ? $ultimaVenta->id + 1 : 1;

        return 'FACT-'.str_pad((string) $numero, 8, '0', STR_PAD_LEFT);
    }
}
