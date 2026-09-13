<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoActivoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipo_activo')->truncate();

        DB::table('tipo_activo')->insert([
            [
                'nombre' => 'computadora',
                'categoria' => 'equipo_computo',
                'requiere_serial' => true,
                'requiere_cantidad' => false,
                'requiere_mantenimiento' => true,
                'vida_util_meses' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'proyector',
                'categoria' => 'equipo_audiovisual',
                'requiere_serial' => true,
                'requiere_cantidad' => false,
                'requiere_mantenimiento' => true,
                'vida_util_meses' => 48,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'monitor',
                'categoria' => 'periferico',
                'requiere_serial' => true,
                'requiere_cantidad' => false,
                'requiere_mantenimiento' => false,
                'vida_util_meses' => 36,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'tablet',
                'categoria' => 'dispositivo_movil',
                'requiere_serial' => true,
                'requiere_cantidad' => false,
                'requiere_mantenimiento' => true,
                'vida_util_meses' => 36,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'impresora',
                'categoria' => 'equipo_oficina',
                'requiere_serial' => true,
                'requiere_cantidad' => false,
                'requiere_mantenimiento' => true,
                'vida_util_meses' => 48,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'telefono',
                'categoria' => 'comunicacion',
                'requiere_serial' => true,
                'requiere_cantidad' => false,
                'requiere_mantenimiento' => false,
                'vida_util_meses' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
