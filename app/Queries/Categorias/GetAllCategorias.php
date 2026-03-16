<?php

namespace App\Queries\Categorias;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Collection;

class GetAllCategorias
{
    public function handle(?string $estado = null): Collection
    {
        $query = Categoria::query();

        if ($estado !== null) {
            $query->where('estado', $estado);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
