<?php

namespace App\Services\Compras;

use App\Actions\Compras\CreateCompraAction;
use App\Actions\Compras\DeleteCompraAction;
use App\Actions\Compras\UpdateCompraAction;
use App\Queries\Compras\GetAllCompras;
use App\Queries\Compras\GetCompraById;
use Illuminate\Database\Eloquent\Collection;

class CompraService
{
    public function __construct(
        private GetAllCompras $getAllCompras,
        private GetCompraById $getCompraById,
        private CreateCompraAction $createCompraAction,
        private UpdateCompraAction $updateCompraAction,
        private DeleteCompraAction $deleteCompraAction,
    ) {}

    public function getAll(?string $estado = null): Collection
    {
        return $this->getAllCompras->handle($estado);
    }

    public function getById(int $id)
    {
        return $this->getCompraById->handle($id);
    }

    public function create(array $data, int $idUsuario)
    {
        return $this->createCompraAction->handle($data, $idUsuario);
    }

    public function update(int $id, array $data, int $idUsuario)
    {
        return $this->updateCompraAction->handle($id, $data, $idUsuario);
    }

    public function delete(int $id, int $idUsuario)
    {
        $this->deleteCompraAction->handle($id, $idUsuario);
    }
}
