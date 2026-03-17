<?php

namespace App\Actions\Proveedores;

use App\Models\Proveedor;

class DeleteProveedorAction
{
    public function handle(Proveedor $proveedor): void
    {
        $proveedor->delete();
    }
}
