<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerifericoSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tabla primero
        DB::table('periferico')->truncate();

        DB::table('periferico')->insert([
            [
                'nombre' => 'Mouse Logitech M185',
                'tipo' => 'mouse',
                'marca' => 'Logitech',
                'modelo' => 'M185',
                'serial' => 'MOUSE-001',
                'cantidad_total' => 10,
                'cantidad_disponible' => 8,
                'ubicacion' => 'Laboratorio A-101',
                'observaciones' => 'Mouse inalámbrico',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Teclado HP USB',
                'tipo' => 'teclado',
                'marca' => 'HP',
                'modelo' => 'KB-216',
                'serial' => 'TECL-002',
                'cantidad_total' => 15,
                'cantidad_disponible' => 12,
                'ubicacion' => 'Laboratorio A-101',
                'observaciones' => 'Teclado con cable USB',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Audífonos Sony WH-1000XM4',
                'tipo' => 'audifonos',
                'marca' => 'Sony',
                'modelo' => 'WH-1000XM4',
                'serial' => 'AUD-003',
                'cantidad_total' => 5,
                'cantidad_disponible' => 3,
                'ubicacion' => 'Laboratorio de Idiomas',
                'observaciones' => 'Audífonos con cancelación de ruido',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Webcam Logitech C920',
                'tipo' => 'webcam',
                'marca' => 'Logitech',
                'modelo' => 'C920',
                'serial' => 'WEBCAM-004',
                'cantidad_total' => 8,
                'cantidad_disponible' => 6,
                'ubicacion' => 'Salón de videoconferencias',
                'observaciones' => 'Cámara HD 1080p',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'USB Kingston 64GB',
                'tipo' => 'usb',
                'marca' => 'Kingston',
                'modelo' => 'DataTraveler',
                'serial' => 'USB-005',
                'cantidad_total' => 20,
                'cantidad_disponible' => 18,
                'ubicacion' => 'Almacén',
                'observaciones' => 'Memoria USB 3.0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Adaptador HDMI VGA',
                'tipo' => 'adaptador',
                'marca' => 'Generic',
                'modelo' => 'HDMI-VGA',
                'serial' => 'ADAP-006',
                'cantidad_total' => 12,
                'cantidad_disponible' => 10,
                'ubicacion' => 'Auditorio',
                'observaciones' => 'Convertidor HDMI a VGA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
