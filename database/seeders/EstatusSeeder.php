<?php

namespace Database\Seeders;

use App\Models\Estatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estatus = [
            [
                'descripcion' => 'Disponible',
                'color_badge' => 'success',
                'permite_prestamo' => true,
                'permite_solicitud' => true,
                'es_terminal' => false,
            ],
            [
                'descripcion' => 'Prestado',
                'color_badge' => 'warning',
                'permite_prestamo' => false,
                'permite_solicitud' => false,
                'es_terminal' => false,
            ],
            [
                'descripcion' => 'En reparación',
                'color_badge' => 'info',
                'permite_prestamo' => false,
                'permite_solicitud' => true,
                'es_terminal' => false,
            ],
            [
                'descripcion' => 'Desechado',
                'color_badge' => 'danger',
                'permite_prestamo' => false,
                'permite_solicitud' => false,
                'es_terminal' => true,
            ],
            [
                'descripcion' => 'En bodega',
                'color_badge' => 'secondary',
                'permite_prestamo' => true,
                'permite_solicitud' => true,
                'es_terminal' => false,
            ],
            [
                'descripcion' => 'Reservado',
                'color_badge' => 'primary',
                'permite_prestamo' => false,
                'permite_solicitud' => true,
                'es_terminal' => false,
            ],
        ];

        foreach ($estatus as $estado) {
            Estatus::updateOrCreate(
                ['descripcion' => $estado['descripcion']],
                $estado
            );
        }

        $this->command->info('✅ Estatus creados correctamente: ' . count($estatus) . ' registros');
        $this->command->table(
            ['Descripción', 'Color Badge', 'Préstamo', 'Solicitud', 'Terminal'],
            collect($estatus)->map(function ($e) {
                return [
                    $e['descripcion'],
                    $e['color_badge'],
                    $e['permite_prestamo'] ? '✅' : '❌',
                    $e['permite_solicitud'] ? '✅' : '❌',
                    $e['es_terminal'] ? '✅' : '❌',
                ];
            })->toArray()
        );
    }
}
