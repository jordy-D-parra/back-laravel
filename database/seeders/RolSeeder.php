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
            ['nombre' => 'ingeniero', 'descripcion' => 'Ingeniero del departamento - Control total sobre inventario y equipos'],
            ['nombre' => 'secretaria', 'descripcion' => 'Secretaria del departamento - Solo lectura en inventario, puede gestionar trabajadores'],
            ['nombre' => 'tecnico', 'descripcion' => 'Técnico de soporte - Puede crear y editar activos, pero no eliminar'],
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(
                ['nombre' => $rol['nombre']],
                $rol
            );
        }
    }
}
