<?php

namespace App\Queries\Compras;

use App\Models\Compra;

class GetCompraById
{
    public function handle(int $id): Compra
    {
        return Compra::with(['proveedor', 'detalleCompras.producto'])->findOrFail($id);
    }
}
