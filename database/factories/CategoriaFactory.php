<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition(): array
    {
        $prefijo = strtoupper(Str::random(3));

        return [
            'nombre' => ucfirst(fake()->unique()->word()),
            'descripcion' => fake()->sentence(),
            'estado' => 'activo',
            'prefijo' => $prefijo,
        ];
    }
}
