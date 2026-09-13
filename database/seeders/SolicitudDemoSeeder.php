<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Solicitud;
use App\Models\DetalleSolicitud;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class SolicitudDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creando datos de demostración para solicitudes...');

        $usuario = Usuario::where('usuario', 'jordy')->first();
        if (!$usuario) {
            $this->command->error('No se encontró usuario admin. Ejecuta primero UsuarioAdminSeeder');
            return;
        }

        $activo = Activo::first();
        $componente = Componente::first();

        $solicitudes = [
            [
                'justificacion' => 'Necesito equipos para el proyecto de renovación tecnológica del departamento. Se requiere con urgencia para cumplir con los plazos establecidos.',
                'prioridad' => 'alta',
                'fecha_requerida' => now()->addDays(5),
                'fecha_fin_estimada' => now()->addDays(30),
                'tipo_solicitante' => 'interno',
                'estado_solicitud' => 'pendiente'
            ],
            [
                'justificacion' => 'Solicito computadoras para el laboratorio de informática. Los equipos actuales están obsoletos y necesitamos renovarlos para el nuevo año escolar.',
                'prioridad' => 'normal',
                'fecha_requerida' => now()->addDays(10),
                'fecha_fin_estimada' => now()->addDays(45),
                'tipo_solicitante' => 'externo',
                'estado_solicitud' => 'aprobada'
            ],
            [
                'justificacion' => 'Urge la reparación de equipos en el área administrativa. Los equipos presentan fallas críticas que afectan la productividad diaria.',
                'prioridad' => 'urgente',
                'fecha_requerida' => now()->addDays(2),
                'fecha_fin_estimada' => now()->addDays(15),
                'tipo_solicitante' => 'interno',
                'estado_solicitud' => 'rechazada'
            ],
            [
                'justificacion' => 'Se requiere equipamiento para el nuevo personal contratado. Incluye computadoras, monitores y periféricos básicos.',
                'prioridad' => 'baja',
                'fecha_requerida' => now()->addDays(20),
                'fecha_fin_estimada' => now()->addDays(60),
                'tipo_solicitante' => 'externo',
                'estado_solicitud' => 'pendiente'
            ],
        ];

        foreach ($solicitudes as $data) {
            $solicitud = Solicitud::create([
                'usuario_id' => $usuario->id,
                'tipo_solicitante' => $data['tipo_solicitante'],
                'fecha_solicitud' => now(),
                'fecha_requerida' => $data['fecha_requerida'],
                'fecha_fin_estimada' => $data['fecha_fin_estimada'],
                'justificacion' => $data['justificacion'],
                'prioridad' => $data['prioridad'],
                'estado_solicitud' => $data['estado_solicitud'],
                'observaciones' => 'Solicitud generada automáticamente por el seeder',
            ]);

            // Agregar detalles
            if ($activo) {
                DetalleSolicitud::create([
                    'solicitud_id' => $solicitud->id,
                    'activo_id' => $activo->id,
                    'tipo_item' => 'activo',
                    'cantidad_solicitada' => rand(1, 3),
                ]);
            }

            if ($componente) {
                DetalleSolicitud::create([
                    'solicitud_id' => $solicitud->id,
                    'componente_id' => $componente->id,
                    'tipo_item' => 'componente',
                    'cantidad_solicitada' => rand(2, 5),
                ]);
            }
        }

        $this->command->info('✅ ' . count($solicitudes) . ' solicitudes creadas');
    }
}
