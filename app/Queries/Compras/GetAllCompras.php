<?php

namespace App\Queries\Compras;

use App\Models\Compra;
use Illuminate\Database\Eloquent\Collection;

class GetAllCompras
{
    public function handle(?string $estado = null): Collection
    {
        $query = Compra::from('compras as c')->selectRaw("
            c.*,
            p.nombre as proveedor,
            p.contacto,
            p.telefono, 
            p.email as correo
        ")
        ->join('proveedores as p', 'p.id', '=', 'c.id_proveedor')
        ->when(
            $estado, 
            fn ($query, $value) => 
            $query->where('c.estado','=', $value)
        )->orderBy('created_at', 'desc')->get();

        return $query;
    }
}
