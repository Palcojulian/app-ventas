<?php

namespace App\Services\Categorias;

use App\Actions\Categorias\CreateCategoriaAction;
use App\Actions\Categorias\DeleteCategoriaAction;
use App\Actions\Categorias\UpdateCategoriaAction;
use App\Events\Categorias\CategoriaActualizadaEvent;
use App\Events\Categorias\CategoriaCreadaEvent;
use App\Events\Categorias\CategoriaEliminadaEvent;
use App\Queries\Categorias\GetAllCategorias;
use App\Queries\Categorias\GetCategoriaById;
use Illuminate\Database\Eloquent\Collection;

class CategoriaService
{
    public function __construct(
        private GetAllCategorias $getAllCategorias,
        private GetCategoriaById $getCategoriaById,
        private CreateCategoriaAction $createCategoriaAction,
        private UpdateCategoriaAction $updateCategoriaAction,
        private DeleteCategoriaAction $deleteCategoriaAction,
    ) {}

    public function getAll(?string $estado = null): Collection
    {
        return $this->getAllCategorias->handle($estado);
    }

    public function getById(int $id)
    {
        return $this->getCategoriaById->handle($id);
    }

    public function create(array $data)
    {
        $categoria = $this->createCategoriaAction->handle($data);
        event(new CategoriaCreadaEvent($categoria));

        return $categoria;
    }

    public function update(int $id, array $data)
    {
        $categoria = $this->getCategoriaById->handle($id);
        $categoria = $this->updateCategoriaAction->handle($categoria, $data);
        event(new CategoriaActualizadaEvent($categoria));

        return $categoria;
    }

    public function delete(int $id)
    {
        $categoria = $this->getCategoriaById->handle($id);
        $this->deleteCategoriaAction->handle($categoria);
        event(new CategoriaEliminadaEvent($categoria));
    }
}
