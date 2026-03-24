<?php

namespace App\Queries\Proveedores;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Collection;

class GetAllProveedores
{
    public function handle(?string $estado = null): Collection
    {
        $query = Proveedor::query();

        if ($estado !== null) {
            $query->where('estado', $estado);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
