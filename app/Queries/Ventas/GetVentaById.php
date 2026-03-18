<?php

namespace App\Queries\Ventas;

use App\Models\DetalleVenta;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Collection;

class GetVentaById
{
    public function handle(int $id): Collection
    {   
        $items = DetalleVenta::query()->selectRaw("
            detalle_ventas.*,
            p.nombre as producto,
            c.nombre as categoria
        ")
        ->join("productos as p","p.id","=","detalle_ventas.id_producto")
        ->join("categorias as c","c.id","=","p.id_categoria")
        ->where("detalle_ventas.id_venta", "=", $id)->get();

        return $items;
    }
}
