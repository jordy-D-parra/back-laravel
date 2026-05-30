<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class prestamo extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NOTA: Los IDs referenciados deben existir en las tablas responsables y activos.
        // Si usas factories, puedes buscarlos dinámicamente.
        // Aquí se crea un conjunto fijo como ejemplo.

        DB::table('prestamos')->insert([
            [
                'responsable_id' => 1,
                'activo_id' => 1,
                'fecha_salida' => Carbon::now()->subDays(10)->toDateString(),
                'fecha_devolucion' => Carbon::now()->addDays(5)->toDateString(),
                'estado' => 'pendiente',
                'observaciones' => 'Préstamo inicial del proyector para capacitación.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'responsable_id' => 2,
                'activo_id' => 3,
                'fecha_salida' => Carbon::now()->subDays(6)->toDateString(),
                'fecha_devolucion' => Carbon::now()->subDays(1)->toDateString(),
                'estado' => 'vencido',
                'observaciones' => 'Retraso en la devolución debido a reparación.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'responsable_id' => 3,
                'activo_id' => 2,
                'fecha_salida' => Carbon::now()->subDays(15)->toDateString(),
                'fecha_devolucion' => Carbon::now()->subDays(2)->toDateString(),
                'estado' => 'devuelto',
                'observaciones' => 'Devuelto sin novedades.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'responsable_id' => 1,
                'activo_id' => 4,
                'fecha_salida' => Carbon::now()->subDays(1)->toDateString(),
                'fecha_devolucion' => Carbon::now()->addDays(3)->toDateString(),
                'estado' => 'entregado',
                'observaciones' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
