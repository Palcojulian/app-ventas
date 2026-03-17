<?php

namespace App\Queries\Ventas;

use App\Models\Venta;

class GetVentaById
{
    public function handle(int $id): Venta
    {
        return Venta::with(['cliente', 'usuario', 'detalleVentas.producto'])->findOrFail($id);
    }
}
