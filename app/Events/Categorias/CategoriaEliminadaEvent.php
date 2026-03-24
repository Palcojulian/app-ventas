<?php

namespace App\Events\Categorias;

use App\Models\Categoria;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoriaEliminadaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Categoria $categoria) {}
}
