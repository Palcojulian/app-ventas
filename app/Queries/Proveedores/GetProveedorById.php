<?php

namespace App\Queries\Proveedores;

use App\Models\Proveedor;

class GetProveedorById
{
    public function handle(int $id): Proveedor
    {
        return Proveedor::findOrFail($id);
    }
}
