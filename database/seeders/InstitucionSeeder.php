<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstitucionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('institucion')->insert([
            [
                'nombre' => 'Facultad de Ingeniería',
                'informacion' => 'Facultad dedicada a la formación en ingenierías',
                'representante' => 'Dr. Juan Carlos Pérez',
                'ubicacion' => 'Campus Principal, Bloque A, Piso 2',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Escuela de Negocios',
                'informacion' => 'Escuela de administración y negocios',
                'representante' => 'Dra. María González',
                'ubicacion' => 'Campus Principal, Bloque B',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Departamento de Sistemas',
                'informacion' => 'Departamento de tecnología y sistemas',
                'representante' => 'Ing. Carlos Rodríguez',
                'ubicacion' => 'Edificio de Tecnología, Piso 3',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Facultad de Ciencias',
                'informacion' => 'Facultad de ciencias básicas',
                'representante' => 'Dr. Ana Martínez',
                'ubicacion' => 'Campus Principal, Bloque C',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Biblioteca Central',
                'informacion' => 'Biblioteca principal de la universidad',
                'representante' => 'Lic. Roberto Fernández',
                'ubicacion' => 'Edificio Biblioteca, Planta Baja',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
