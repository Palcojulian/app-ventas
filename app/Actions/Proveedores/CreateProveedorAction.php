<?php

namespace App\Actions\Proveedores;

use App\Models\Proveedor;

class CreateProveedorAction
{
    public function handle(array $data): Proveedor
    {
        return Proveedor::create([
            'nombre' => $data['nombre'],
            'contacto' => $data['contacto'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? null,
            'estado' => $data['estado'] ?? 'activo',
        ]);
    }
}
