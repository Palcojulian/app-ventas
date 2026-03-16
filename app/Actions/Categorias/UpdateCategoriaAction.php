<?php

namespace App\Actions\Categorias;

use App\Models\Categoria;

class UpdateCategoriaAction
{
    public function handle(Categoria $categoria, array $data): Categoria
    {
        $categoria->update([
            'nombre' => $data['nombre'] ?? $categoria->nombre,
            'descripcion' => $data['descripcion'] ?? $categoria->descripcion,
            'estado' => $data['estado'] ?? $categoria->estado,
        ]);

        return $categoria->fresh();
    }
}
