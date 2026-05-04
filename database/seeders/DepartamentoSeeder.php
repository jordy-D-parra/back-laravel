<?php

namespace Database\Seeders;

use App\Models\Departamento;
use Illuminate\Database\Seeder;

class DepartamentoSeeder extends Seeder
{
    public function run()
    {
        $departamentos = [
            ['nombre' => 'Recursos Humanos', 'activo' => true],
            ['nombre' => 'Tecnología de la Información', 'activo' => true],
            ['nombre' => 'Administración', 'activo' => true],
            ['nombre' => 'Finanzas', 'activo' => true],
            ['nombre' => 'Logística', 'activo' => true],
            ['nombre' => 'Mantenimiento', 'activo' => true],
            ['nombre' => 'Investigación y Desarrollo', 'activo' => true],
            ['nombre' => 'Atención al Cliente', 'activo' => true],
            ['nombre' => 'Ventas', 'activo' => true],
            ['nombre' => 'Marketing', 'activo' => true],
        ];

        foreach ($departamentos as $departamento) {
            Departamento::firstOrCreate(
                ['nombre' => $departamento['nombre']],
                $departamento
            );
        }
    }
}
