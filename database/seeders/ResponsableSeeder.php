<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResponsableSeeder extends Seeder
{
    public function run()
    {
        DB::table('responsable')->insert([
            [
                'nombre' => 'Juan Pérez',
                'departamento' => 'Sistemas',
                'tipo' => 'interno',
                'documento' => 'V-12345678',
                'telefono' => '0412-1234567',
                'email' => 'juan.perez@empresa.com',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'María González',
                'departamento' => 'Recursos Humanos',
                'tipo' => 'interno',
                'documento' => 'V-87654321',
                'telefono' => '0416-7654321',
                'email' => 'maria.gonzalez@empresa.com',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Pedro Sánchez',
                'departamento' => 'Mantenimiento',
                'tipo' => 'externo',
                'institucion_id' => 1, // Ajusta según tu BD
                'documento' => 'J-12345678',
                'telefono' => '0424-9876543',
                'email' => 'pedro@servicios.com',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
