<?php

namespace App\Actions\Proveedores;

use App\Models\Proveedor;

class UpdateProveedorAction
{
    public function handle(Proveedor $proveedor, array $data): Proveedor
    {
        $proveedor->update([
            'nombre' => $data['nombre'] ?? $proveedor->nombre,
            'contacto' => $data['contacto'] ?? $proveedor->contacto,
            'telefono' => $data['telefono'] ?? $proveedor->telefono,
            'email' => $data['email'] ?? $proveedor->email,
            'estado' => $data['estado'] ?? $proveedor->estado,
        ]);

        return $proveedor->fresh();
    }
}
