<?php

namespace App\Services\Productos;

use App\Actions\Productos\CreateProductoAction;
use App\Actions\Productos\DeleteProductoAction;
use App\Actions\Productos\UpdateProductoAction;
use App\Queries\Productos\GetAllProductos;
use App\Queries\Productos\GetProductoById;
use Illuminate\Database\Eloquent\Collection;

class ProductoService
{
    public function __construct(
        private GetAllProductos $getAllProductos,
        private GetProductoById $getProductoById,
        private CreateProductoAction $createProductoAction,
        private UpdateProductoAction $updateProductoAction,
        private DeleteProductoAction $deleteProductoAction,
    ) {}

    public function getAll(?string $estado = null): Collection
    {
        return $this->getAllProductos->handle($estado);
    }

    public function getById(int $id)
    {
        return $this->getProductoById->handle($id);
    }

    public function create(array $data)
    {
        return $this->createProductoAction->handle($data);
    }

    public function update(int $id, array $data)
    {
        $producto = $this->getProductoById->handle($id);

        return $this->updateProductoAction->handle($producto, $data);
    }

    public function delete(int $id)
    {
        $producto = $this->getProductoById->handle($id);
        $this->deleteProductoAction->handle($producto);
    }
}
