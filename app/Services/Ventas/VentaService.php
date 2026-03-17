<?php

namespace App\Services\Ventas;

use App\Actions\Ventas\CreateVentaAction;
use App\Actions\Ventas\DeleteVentaAction;
use App\Actions\Ventas\UpdateVentaAction;
use App\Queries\Ventas\GetAllVentas;
use App\Queries\Ventas\GetVentaById;
use Illuminate\Database\Eloquent\Collection;

class VentaService
{
    public function __construct(
        private GetAllVentas $getAllVentas,
        private GetVentaById $getVentaById,
        private CreateVentaAction $createVentaAction,
        private UpdateVentaAction $updateVentaAction,
        private DeleteVentaAction $deleteVentaAction,
    ) {}

    public function getAll(?string $estado = null): Collection
    {
        return $this->getAllVentas->handle($estado);
    }

    public function getById(int $id)
    {
        return $this->getVentaById->handle($id);
    }

    public function create(array $data, int $idUsuario)
    {
        return $this->createVentaAction->handle($data, $idUsuario);
    }

    public function update(int $id, array $data, int $idUsuario)
    {
        $venta = $this->getVentaById->handle($id);

        return $this->updateVentaAction->handle($venta, $data, $idUsuario);
    }

    public function delete(int $id, int $idUsuario)
    {
        $venta = $this->getVentaById->handle($id);
        $this->deleteVentaAction->handle($venta, $idUsuario);
    }
}
