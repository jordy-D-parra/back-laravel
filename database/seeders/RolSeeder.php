<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'admin', 'descripcion' => 'Administrador del sistema con acceso total'],
            ['nombre' => 'ingeniero', 'descripcion' => 'Ingeniero del departamento'],
            ['nombre' => 'secretaria', 'descripcion' => 'Secretaria del departamento, experta en papeleo'],
            ['nombre' => 'tecnico', 'descripcion' => 'Técnico de soporte y reparación'],
        ];

        foreach ($roles as $rol) {
            Rol::create($rol);
        }
    }
}
