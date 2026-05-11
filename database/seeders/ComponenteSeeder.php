<?php
// database/seeders/ComponenteSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComponenteSeeder extends Seeder
{
    public function run()
    {
        $componentes = [
            ['nombre' => 'Batería Original', 'tipo' => 'Batería', 'serial' => 'BAT-HP-001', 'estado' => 'disponible', 'activo' => true, 'descripcion' => 'Batería original HP 3 celdas'],
            ['nombre' => 'Cargador 65W', 'tipo' => 'Cargador', 'serial' => 'CHG-DELL-001', 'estado' => 'disponible', 'activo' => true, 'descripcion' => 'Cargador Dell 65W USB-C'],
            ['nombre' => 'Cable HDMI 2m', 'tipo' => 'Cable', 'serial' => 'CBL-SAM-001', 'estado' => 'disponible', 'activo' => true, 'descripcion' => 'Cable HDMI 2 metros'],
            ['nombre' => 'Mouse Óptico', 'tipo' => 'Mouse', 'serial' => 'MOU-LEN-001', 'estado' => 'asignado', 'activo' => true, 'descripcion' => 'Mouse óptico Lenovo'],
        ];

        foreach ($componentes as $componente) {
            DB::table('componentes')->insert(array_merge($componente, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}
