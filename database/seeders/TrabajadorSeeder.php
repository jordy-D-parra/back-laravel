<?php

namespace Database\Seeders;

use App\Models\Trabajador;
use Illuminate\Database\Seeder;

class TrabajadorSeeder extends Seeder
{
    public function run(): void
    {
        Trabajador::create([
            'cedula' => 'V-12345678',
            'nombre' => 'Administrador',
            'apellido' => 'Sistema',
            'departamento' => 'Informática',
            'cargo' => 'Jefe de Departamento',
            'especialidad' => 'Gestión de sistemas y redes',
            'telefono' => '0412-1234567',
        ]);

        // Puedes agregar más trabajadores de ejemplo aquí
    }
}
