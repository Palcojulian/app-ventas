<?php

namespace App\Queries\Ventas;

use App\Models\Venta;
use Illuminate\Database\Eloquent\Collection;

class GetAllVentas
{
    public function handle(?string $estado = null): Collection
    {   

        $data = Venta::query()->selectRaw("
            ventas.*,
            uc.name as cliente,
            ua.name as usuario_sistema
        ")
        ->join("users as uc", "uc.id","=","ventas.id_cliente")
        ->join("users as ua", "ua.id", "=", "ventas.id_usuario")
        ->when(
            $estado, 
            fn($query, $value) => $query->where("ventas.estado", "=", $value)
        )->get();
    
        return $data;
    }
}
