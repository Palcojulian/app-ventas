<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Actions\Productos\CreateProductoAction;


class ProductosImport  implements ToModel, WithHeadingRow
{
    private CreateProductoAction $createProductoAction;

    public function __construct() {
        $this->createProductoAction = new CreateProductoAction();
    }

    public function model(array $row)
    {
        $this->createProductoAction->handle($row);
    }
}