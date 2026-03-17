<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'admin', 'descripcion' => 'Administrador con acceso total al sistema'],
            ['nombre' => 'cliente', 'descripcion' => 'Cliente que puede realizar compras'],
            ['nombre' => 'vendedor', 'descripcion' => 'Vendedor que gestiona ventas y productos'],
        ];

        foreach ($roles as $rol) {
            Role::create($rol);
        }
    }
}
