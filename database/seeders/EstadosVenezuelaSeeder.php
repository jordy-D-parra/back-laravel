<?php
// database/seeders/EstadosVenezuelaSeeder.php

namespace Database\Seeders;

use App\Models\Estado;
use Illuminate\Database\Seeder;

class EstadosVenezuelaSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            'Amazonas', 'Anzoátegui', 'Apure', 'Aragua', 'Barinas',
            'Bolívar', 'Carabobo', 'Cojedes', 'Delta Amacuro',
            'Distrito Capital', 'Falcón', 'Guárico', 'La Guaira',
            'Lara', 'Mérida', 'Miranda', 'Monagas', 'Nueva Esparta',
            'Portuguesa', 'Sucre', 'Táchira', 'Trujillo',
            'Yaracuy', 'Zulia'
        ];

        foreach ($estados as $nombre) {
            Estado::updateOrCreate(
                ['nombre' => $nombre],
                ['activo' => true]
            );
        }

        $this->command->info('✅ ' . count($estados) . ' estados creados');
    }
}
