<?php
// database/seeders/ParroquiasYaracuySeeder.php

namespace Database\Seeders;

use App\Models\Municipio;
use App\Models\Parroquia;
use Illuminate\Database\Seeder;

class ParroquiasYaracuySeeder extends Seeder
{
    public function run(): void
    {
        $municipios = Municipio::whereHas('estado', function($q) {
            $q->where('nombre', 'Yaracuy');
        })->get();

        $parroquias = [
            'San Felipe' => ['San Felipe', 'Albarico', 'Guama', 'Ricaurte', 'San Javier', 'Cocorote'],
            'Sucre' => ['Guama', 'Aroa', 'Sabana de Parra'],
            'Arístides Bastidas' => ['San Pablo', 'Boraure'],
            'Bolívar' => ['Aroa', 'Farriar'],
            'Bruzual' => ['Chivacoa', 'Yumare', 'Urachiche'],
            'Cocorote' => ['Cocorote'],
            'Independencia' => ['Independencia', 'El Guayabo', 'Salom'],
            'José Antonio Páez' => ['Sabana de Parra', 'San José de la Montaña'],
            'La Trinidad' => ['Boraure', 'El Guayabo'],
            'Manuel Monge' => ['Yumare', 'Santa Rita'],
            'Nirgua' => ['Nirgua', 'La Hacienda', 'La Represa', 'Salóm'],
            'Peña' => ['Yaritagua', 'San Andrés', 'Cabudare'],
            'San Javier' => ['San Javier'],
            'Urachiche' => ['Urachiche'],
            'Veroes' => ['Farriar', 'El Guayabo', 'Salóm'],
        ];

        $total = 0;
        foreach ($parroquias as $municipioNombre => $lista) {
            $municipio = $municipios->firstWhere('nombre', $municipioNombre);
            if (!$municipio) continue;

            foreach ($lista as $nombre) {
                Parroquia::updateOrCreate(
                    ['municipio_id' => $municipio->id, 'nombre' => $nombre],
                    ['activo' => true]
                );
                $total++;
            }
        }

        $this->command->info('✅ ' . $total . ' parroquias de Yaracuy creadas');
    }
}
