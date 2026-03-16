<?php

namespace App\Actions\Categorias;

use App\Models\Categoria;

class DeleteCategoriaAction
{
    public function handle(Categoria $categoria): void
    {
        $categoria->delete();
    }
}
