<?php

namespace App\Queries\Categorias;

use App\Models\Categoria;

class GetCategoriaById
{
    public function handle(int $id): Categoria
    {
        return Categoria::findOrFail($id);
    }
}
