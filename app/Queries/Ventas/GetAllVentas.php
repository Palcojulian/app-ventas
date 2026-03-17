<?php

namespace App\Queries\Ventas;

use App\Models\Venta;
use Illuminate\Database\Eloquent\Collection;

class GetAllVentas
{
    public function handle(?string $estado = null): Collection
    {
        $query = Venta::with(['cliente', 'usuario', 'detalleVentas.producto']);

        if ($estado !== null) {
            $query->where('estado', $estado);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
