<?php

namespace App\Imports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductosImport  implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        Log::info($row['columna_1']);
        Log::info($row['codigo_tienda']);
        Log::info($row['codigo_barras_o_qr']);    
    }
}