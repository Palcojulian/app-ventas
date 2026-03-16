<?php

namespace App\Actions\Categorias;

use App\Models\Categoria;

class CreateCategoriaAction
{
    public function handle(array $data): Categoria
    {
        return Categoria::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'estado' => $data['estado'] ?? 'activo',
        ]);
    }
}
