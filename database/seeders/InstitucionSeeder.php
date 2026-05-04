<?php

namespace Database\Seeders;

use App\Models\Institucion;
use Illuminate\Database\Seeder;

class InstitucionSeeder extends Seeder
{
    public function run()
    {
        $instituciones = [
            [
                'nombre' => 'Facultad de Ingeniería',
                'representante' => 'Dr. Juan Carlos Pérez',
                'ubicacion' => 'Campus Principal, Bloque A, Piso 2',
                'informacion' => 'Facultad dedicada a la formación en ingenierías',
                'activo' => true
            ],
            [
                'nombre' => 'Escuela de Negocios',
                'representante' => 'Dra. María González',
                'ubicacion' => 'Campus Principal, Bloque B',
                'informacion' => 'Escuela de administración y negocios',
                'activo' => true
            ],
            [
                'nombre' => 'Departamento de Sistemas',
                'representante' => 'Ing. Carlos Rodríguez',
                'ubicacion' => 'Edificio de Tecnología, Piso 3',
                'informacion' => 'Departamento de tecnología y sistemas',
                'activo' => true
            ],
            [
                'nombre' => 'Facultad de Ciencias',
                'representante' => 'Dr. Ana Martínez',
                'ubicacion' => 'Campus Principal, Bloque C',
                'informacion' => 'Facultad de ciencias básicas',
                'activo' => true
            ],
            [
                'nombre' => 'Biblioteca Central',
                'representante' => 'Lic. Roberto Fernández',
                'ubicacion' => 'Edificio Biblioteca, Planta Baja',
                'informacion' => 'Biblioteca principal de la universidad',
                'activo' => true
            ]
        ];

        foreach ($instituciones as $institucion) {
            // Evita duplicados por nombre
            Institucion::firstOrCreate(
                ['nombre' => $institucion['nombre']],
                $institucion
            );
        }
    }
}
