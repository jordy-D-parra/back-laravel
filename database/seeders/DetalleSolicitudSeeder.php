<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetalleSolicitudSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('detalle_solicitud')->truncate();

        DB::table('detalle_solicitud')->insert([
            // Detalles para Solicitud #1
            [
                'id_solicitud' => 1,
                'id_activo' => 1, // Dell Latitude
                'periferico_id' => null,
                'tipo_item' => 'activo',
                'cantidad_solicitada' => 2,
                'observaciones' => 'Para laboratorio de programación',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_solicitud' => 1,
                'id_activo' => null,
                'periferico_id' => 1, // Mouse Logitech
                'tipo_item' => 'periferico',
                'cantidad_solicitada' => 3,
                'observaciones' => 'Mouse para las laptops',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Detalles para Solicitud #2
            [
                'id_solicitud' => 2,
                'id_activo' => 3, // Proyector Epson
                'periferico_id' => null,
                'tipo_item' => 'activo',
                'cantidad_solicitada' => 1,
                'observaciones' => 'Para presentación con clientes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_solicitud' => 2,
                'id_activo' => null,
                'periferico_id' => 4, // Webcam Logitech
                'tipo_item' => 'periferico',
                'cantidad_solicitada' => 1,
                'observaciones' => 'Para videoconferencia',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Detalles para Solicitud #3
            [
                'id_solicitud' => 3,
                'id_activo' => 2, // HP ProBook
                'periferico_id' => null,
                'tipo_item' => 'activo',
                'cantidad_solicitada' => 5,
                'observaciones' => 'Para curso de programación',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_solicitud' => 3,
                'id_activo' => null,
                'periferico_id' => 2, // Teclado HP
                'tipo_item' => 'periferico',
                'cantidad_solicitada' => 5,
                'observaciones' => 'Teclados para las computadoras',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Detalles para Solicitud #4
            [
                'id_solicitud' => 4,
                'id_activo' => 4, // Monitor Samsung
                'periferico_id' => null,
                'tipo_item' => 'activo',
                'cantidad_solicitada' => 1,
                'observaciones' => 'Para oficina administrativa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
