<?php

namespace App\Services\Proveedores;

use App\Actions\Proveedores\CreateProveedorAction;
use App\Actions\Proveedores\DeleteProveedorAction;
use App\Actions\Proveedores\UpdateProveedorAction;
use App\Queries\Proveedores\GetAllProveedores;
use App\Queries\Proveedores\GetProveedorById;
use Illuminate\Database\Eloquent\Collection;

class ProveedorService
{
    public function __construct(
        private GetAllProveedores $getAllProveedores,
        private GetProveedorById $getProveedorById,
        private CreateProveedorAction $createProveedorAction,
        private UpdateProveedorAction $updateProveedorAction,
        private DeleteProveedorAction $deleteProveedorAction,
    ) {}

    public function getAll(?string $estado = null): Collection
    {
        return $this->getAllProveedores->handle($estado);
    }

    public function getById(int $id)
    {
        return $this->getProveedorById->handle($id);
    }

    public function create(array $data)
    {
        return $this->createProveedorAction->handle($data);
    }

    public function update(int $id, array $data)
    {
        $proveedor = $this->getProveedorById->handle($id);

        return $this->updateProveedorAction->handle($proveedor, $data);
    }

    public function delete(int $id)
    {
        $proveedor = $this->getProveedorById->handle($id);
        $this->deleteProveedorAction->handle($proveedor);
    }
}
