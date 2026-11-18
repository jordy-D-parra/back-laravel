<?php

namespace Database\Seeders;

use App\Models\Estatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstatusSeeder extends Seeder
{
    /**
     * Estatus que, al asignarse a un activo, disparan la creación
     * de una ficha de soporte técnica desde el módulo de Inventario.
     *
     * Flujo del negocio:
     *   - Averiado       → se dañó, DEBE abrir ficha de soporte   ✅ DISPARA
     *   - En reparación  → ya hay ficha abierta, NO reabrir       ❌ NO dispara
     *   - Disponible     → reparado, no aplica                    ❌ NO dispara
     *   - Desechado      → terminal, no aplica                    ❌ NO dispara
     *
     * ⚠️ Debe coincidir con el array de Activo::requiereFichaSoporte()
     *
     * @var array<string>
     */
    public const ESTATUS_QUE_REQUIEREN_FICHA = [
        'Averiado',
    ];

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
                'descripcion' => 'Averiado',
                'color_badge' => 'danger',
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
                'color_badge' => 'dark',
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
            ['Descripción', 'Color Badge', 'Préstamo', 'Solicitud', 'Terminal', '¿Dispara ficha?'],
            collect($estatus)->map(function ($e) {
                $disparaFicha = in_array(
                    $e['descripcion'],
                    self::ESTATUS_QUE_REQUIEREN_FICHA,
                    true
                );

                return [
                    $e['descripcion'],
                    $e['color_badge'],
                    $e['permite_prestamo'] ? '✅' : '❌',
                    $e['permite_solicitud'] ? '✅' : '❌',
                    $e['es_terminal'] ? '✅' : '❌',
                    $disparaFicha ? '🛠️ SÍ' : '—',
                ];
            })->toArray()
        );

        $this->command->info(
            '🛠️  Estatus que disparan ficha de soporte: ' .
            implode(', ', self::ESTATUS_QUE_REQUIEREN_FICHA)
        );
    }
}