<?php
// database/seeders/EstatusSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class EstatusSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        
        DB::table('estatus')->insert([
            [
                'descripcion' => 'Operativo',
                'color_badge' => 'success',
                'permite_prestamo' => true,
                'permite_solicitud' => true,
                'es_terminal' => false,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'descripcion' => 'En Reparación',
                'color_badge' => 'warning',
                'permite_prestamo' => false,
                'permite_solicitud' => false,
                'es_terminal' => false,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'descripcion' => 'No Operativo',
                'color_badge' => 'danger',
                'permite_prestamo' => false,
                'permite_solicitud' => false,
                'es_terminal' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'descripcion' => 'En Préstamo',
                'color_badge' => 'info',
                'permite_prestamo' => false,
                'permite_solicitud' => false,
                'es_terminal' => false,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'descripcion' => 'Dado de Baja',
                'color_badge' => 'secondary',
                'permite_prestamo' => false,
                'permite_solicitud' => false,
                'es_terminal' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }
}