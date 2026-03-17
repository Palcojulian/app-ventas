<?php

namespace App\Queries\Compras;

use App\Models\Compra;
use App\Models\DetalleCompra;
use Illuminate\Database\Eloquent\Collection;
class GetCompraById
{
    public function handle(int $id): Collection
    {
        $itemsCompra = DetalleCompra::from("detalle_compras as dc")->selectRaw("
            dc.*,
            p.nombre as producto,
            p.codigo as codigo_producto,
            c.nombre as categoria
        ")
        ->join("productos as p", "p.id", "=", "dc.id_producto")
        ->join("categorias as c", "c.id", "=", "p.id_categoria")
        ->where("dc.id_compra", "=", $id)->orderBy("created_at","desc")->get();
        
        return $itemsCompra;
    }
}
