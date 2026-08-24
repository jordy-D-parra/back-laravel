<?php
// database/seeders/MunicipiosYaracuySeeder.php

namespace Database\Seeders;

use App\Models\Estado;
use App\Models\Municipio;
use Illuminate\Database\Seeder;

class MunicipiosYaracuySeeder extends Seeder
{
    public function run(): void
    {
        $estado = Estado::where('nombre', 'Yaracuy')->first();

        if (!$estado) {
            $this->command->error('Estado Yaracuy no encontrado');
            return;
        }

        $municipios = [
            'San Felipe', 'Sucre', 'Arístides Bastidas', 'Bolívar',
            'Bruzual', 'Cocorote', 'Independencia', 'José Antonio Páez',
            'La Trinidad', 'Manuel Monge', 'Nirgua', 'Peña',
            'San Javier', 'Urachiche', 'Veroes'
        ];

        foreach ($municipios as $nombre) {
            Municipio::updateOrCreate(
                ['estado_id' => $estado->id, 'nombre' => $nombre],
                ['activo' => true]
            );
        }

        $this->command->info('✅ ' . count($municipios) . ' municipios de Yaracuy creados');
    }
}

