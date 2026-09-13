<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SolicitudSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('solicitud')->truncate();

        DB::table('solicitud')->insert([
            [
                'id_solicitante' => 1, // Asegúrate que existe un usuario con ID 1
                'tipo_solicitante' => 'interno',
                'institucion_id' => 1,
                'oficio_adjunto' => null,
                'fecha_solicitud' => now()->subDays(5),
                'fecha_requerida' => now()->addDays(2),
                'fecha_fin_estimada' => now()->addDays(9),
                'justificacion' => 'Proyecto de investigación - Se necesitan equipos para el laboratorio de computación',
                'prioridad' => 'alta',
                'estado_solicitud' => 'pendiente',
                'observaciones' => 'Preferiblemente equipos con Windows 11',
                'aprobado_por' => null,
                'fecha_aprobacion' => null,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'id_solicitante' => 1,
                'tipo_solicitante' => 'externo',
                'institucion_id' => 2,
                'oficio_adjunto' => null,
                'fecha_solicitud' => now()->subDays(3),
                'fecha_requerida' => now()->addDays(1),
                'fecha_fin_estimada' => now()->addDays(4),
                'justificacion' => 'Presentación importante con clientes - Requerimos proyector y laptop',
                'prioridad' => 'urgente',
                'estado_solicitud' => 'aprobada',
                'observaciones' => 'El proyector debe tener entrada HDMI',
                'aprobado_por' => 1,
                'fecha_aprobacion' => now()->subDays(2),
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id_solicitante' => 1,
                'tipo_solicitante' => 'interno',
                'institucion_id' => 3,
                'oficio_adjunto' => null,
                'fecha_solicitud' => now()->subDays(10),
                'fecha_requerida' => now()->subDays(5),
                'fecha_fin_estimada' => now()->addDays(2),
                'justificacion' => 'Curso de programación - 20 estudiantes necesitan computadoras',
                'prioridad' => 'normal',
                'estado_solicitud' => 'rechazada',
                'observaciones' => 'Software necesario: Visual Studio Code, XAMPP',
                'aprobado_por' => 1,
                'fecha_aprobacion' => now()->subDays(8),
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(8),
            ],
            [
                'id_solicitante' => 1,
                'tipo_solicitante' => 'interno',
                'institucion_id' => 1,
                'oficio_adjunto' => null,
                'fecha_solicitud' => now()->subDays(7),
                'fecha_requerida' => now()->addDays(3),
                'fecha_fin_estimada' => now()->addDays(10),
                'justificacion' => 'Actualización de equipos en oficina administrativa',
                'prioridad' => 'baja',
                'estado_solicitud' => 'pendiente',
                'observaciones' => null,
                'aprobado_por' => null,
                'fecha_aprobacion' => null,
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],
        ]);
    }
}
