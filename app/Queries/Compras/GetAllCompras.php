<?php

namespace App\Queries\Compras;

use App\Models\Compra;
use Illuminate\Database\Eloquent\Collection;

class GetAllCompras
{
    public function handle(?string $estado = null): Collection
    {
        $query = Compra::with(['proveedor', 'detalleCompras.producto']);

        if ($estado !== null) {
            $query->where('estado', $estado);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
