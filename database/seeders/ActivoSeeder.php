<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivoSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tabla
        DB::table('activo')->truncate();

        DB::table('activo')->insert([
            [
                'serial' => 'LAP-DELL-001',
                'tipo_equipo' => 'principal',
                'marca_modelo' => 'Dell Latitude 3420',
                'id_estatus' => 1, // disponible
                'id_tipo_activo' => 1, // computadora
                'cantidad' => 5,
                'ubicacion' => 'Laboratorio A-101',
                'disponible_desde' => now(),
                'fecha_adquisicion' => '2024-01-15',
                'valor_compra' => 25000.00,
                'observaciones' => 'Equipos para laboratorio de programación',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'serial' => 'LAP-HP-002',
                'tipo_equipo' => 'principal',
                'marca_modelo' => 'HP ProBook 450',
                'id_estatus' => 1,
                'id_tipo_activo' => 1,
                'cantidad' => 3,
                'ubicacion' => 'Oficina de Profesores',
                'disponible_desde' => now(),
                'fecha_adquisicion' => '2024-02-10',
                'valor_compra' => 22000.00,
                'observaciones' => 'Equipos para docentes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'serial' => 'PROY-003',
                'tipo_equipo' => 'principal',
                'marca_modelo' => 'Epson EB-2155W',
                'id_estatus' => 1,
                'id_tipo_activo' => 2, // proyector
                'cantidad' => 2,
                'ubicacion' => 'Auditorio Central',
                'disponible_desde' => now(),
                'fecha_adquisicion' => '2024-01-20',
                'valor_compra' => 15000.00,
                'observaciones' => 'Proyectores para presentaciones',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'serial' => 'MON-004',
                'tipo_equipo' => 'principal',
                'marca_modelo' => 'Samsung 24" LED',
                'id_estatus' => 1,
                'id_tipo_activo' => 3, // monitor
                'cantidad' => 10,
                'ubicacion' => 'Laboratorio A-102',
                'disponible_desde' => now(),
                'fecha_adquisicion' => '2024-03-05',
                'valor_compra' => 8000.00,
                'observaciones' => 'Monitores para laboratorio',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'serial' => 'TAB-005',
                'tipo_equipo' => 'principal',
                'marca_modelo' => 'iPad Air 5ta Gen',
                'id_estatus' => 1,
                'id_tipo_activo' => 4, // tablet
                'cantidad' => 4,
                'ubicacion' => 'Biblioteca',
                'disponible_desde' => now(),
                'fecha_adquisicion' => '2024-04-10',
                'valor_compra' => 12000.00,
                'observaciones' => 'Tablets para préstamo en biblioteca',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
