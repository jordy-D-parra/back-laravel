<?php

namespace Database\Seeders;

use App\Models\FichaSoporte;
use App\Models\FichaSoporteDetalle;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Usuario;
use App\Models\Estatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SoporteTecnicoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creando datos de demostración para Soporte Técnico...');

        // ============================================================
        // 1. OBTENER DATOS BASE
        // ============================================================
        $usuarioAdmin = Usuario::where('usuario', 'admin')->first()
            ?? Usuario::where('usuario', 'jordy')->first()
            ?? Usuario::first();

        // Buscar técnicos (usuarios con rol técnico)
        $tecnicos = Usuario::whereHas('rol', function($q) {
            $q->whereIn('nombre', ['admin', 'ingeniero', 'tecnico']);
        })->with('trabajador')->get();

        if ($tecnicos->isEmpty()) {
            $this->command->warn('⚠️ No se encontraron técnicos. Usando usuario admin como técnico.');
            $tecnicos = collect([$usuarioAdmin]);
        }

        // Obtener activos disponibles
        $activos = Activo::with(['modelo.marca', 'estatus'])->get();

        if ($activos->isEmpty()) {
            $this->command->error('❌ No hay activos en el sistema. Ejecuta primero InventarioDemoSeeder.');
            return;
        }

        // Obtener estatus "En reparación"
        $estatusReparacion = Estatus::where('descripcion', 'En reparación')->first();
        $estatusDisponible = Estatus::where('descripcion', 'Disponible')->first();

        $this->command->info('✅ Datos base encontrados:');
        $this->command->info("   - Usuario: {$usuarioAdmin->usuario}");
        $this->command->info("   - Técnicos: {$tecnicos->count()}");
        $this->command->info("   - Activos disponibles: {$activos->count()}");

        // ============================================================
        // 2. DATOS DE EJEMPLO
        // ============================================================
        $diagnosticos = [
            'Equipo presenta lentitud extrema al iniciar sistema operativo y al abrir aplicaciones.',
            'Pantalla presenta líneas horizontales y parpadeo constante. Se requiere revisión de hardware.',
            'No enciende. Se probó con otro cargador y tampoco responde. Posible falla en placa madre.',
            'Equipo no detecta el disco duro. Se escucha ruido metálico al encender.',
            'Sistema operativo corrupto. No inicia Windows, muestra pantalla azul.',
            'Equipo presenta sobrecalentamiento y se apaga automáticamente después de 10 minutos de uso.',
            'Teclado derramó líquido. Varias teclas no funcionan correctamente.',
            'Conector de carga dañado. No carga la batería.',
            'El equipo tiene virus que afectan el rendimiento y muestran anuncios constantes.',
            'No se conecta a la red WiFi. El adaptador de red no aparece en el sistema.',
        ];

        $trabajosRealizados = [
            'Se realizó limpieza profunda de hardware, cambio de pasta térmica y reinstalación de sistema operativo.',
            'Se reemplazó la pantalla por una nueva. El equipo funciona correctamente.',
            'Se reparó la placa madre reemplazando capacitores dañados. El equipo enciende correctamente.',
            'Se reemplazó el disco duro por un SSD. Se instaló sistema operativo y se restauraron los datos.',
            'Se reinstaló el sistema operativo desde cero. Se actualizaron todos los drivers.',
            'Se limpió el sistema de refrigeración y se reemplazó el ventilador de la CPU. Temperatura normalizada.',
            'Se reemplazó el teclado completo. Todas las teclas funcionan correctamente.',
            'Se reparó el conector de carga. La batería ahora carga correctamente.',
            'Se realizó escaneo completo con antivirus, se eliminaron todos los archivos maliciosos.',
            'Se reemplazó la tarjeta de red WiFi por una nueva. El equipo se conecta correctamente.',
        ];

        $nombresReportantes = [
            'María González',
            'Carlos Rodríguez',
            'Ana Martínez',
            'Luis Pérez',
            'Elena Sánchez',
            'José Fernández',
            'Laura Díaz',
            'Pedro Ramírez',
            'Carmen Torres',
            'Jorge Méndez',
        ];

        // ============================================================
        // 3. CREAR FICHAS DE SOPORTE
        // ============================================================

        $fichasCreadas = 0;
        $detallesCreados = 0;

        // Crear 15 fichas de soporte con diferentes estados
        for ($i = 0; $i < 15; $i++) {
            // Alternar entre en_proceso y finalizado
            $estado = $i < 8 ? 'en_proceso' : 'finalizado';

            // Seleccionar activo aleatorio
            $activo = $activos->random();
            $tecnico = $tecnicos->random();

            // Fechas
            $fechaIngreso = now()->subDays(rand(1, 30));
            $fechaSalida = $estado === 'finalizado'
                ? $fechaIngreso->copy()->addDays(rand(1, 10))
                : null;

            // Si el activo está en reparación, cambiar estado
            if ($estado === 'en_proceso' && $estatusReparacion) {
                $activo->update(['id_estatus' => $estatusReparacion->id]);
            } elseif ($estado === 'finalizado' && $estatusDisponible) {
                $activo->update(['id_estatus' => $estatusDisponible->id]);
            }

            // Nombre del técnico
            $tecnicoNombre = $tecnico->trabajador
                ? $tecnico->trabajador->nombre . ' ' . $tecnico->trabajador->apellido
                : $tecnico->usuario;

            // Diagnóstico y trabajo
            $diagnostico = $diagnosticos[array_rand($diagnosticos)];
            $trabajoRealizado = $estado === 'finalizado'
                ? $trabajosRealizados[array_rand($trabajosRealizados)]
                : null;

            // Crear ficha
            $ficha = FichaSoporte::create([
                'activo_id' => $activo->id,
                'tecnico_id' => $tecnico->id,
                'tecnico_nombre' => $tecnicoNombre,
                'usuario_reporta_id' => $usuarioAdmin->id,
                'usuario_reporta_nombre' => $nombresReportantes[array_rand($nombresReportantes)],
                'fecha_ingreso' => $fechaIngreso,
                'fecha_salida' => $fechaSalida,
                'diagnostico' => $diagnostico,
                'trabajo_realizado' => $trabajoRealizado,
                'observaciones' => 'Observaciones adicionales para la ficha #' . ($i + 1),
                'estado' => $estado,
            ]);

            $fichasCreadas++;

            // ============================================================
            // 4. CREAR DETALLES DE LA FICHA (Componentes)
            // ============================================================

            // Obtener componentes del activo
            $componentes = Componente::where('activo_id', $activo->id)->get();

            if ($componentes->isNotEmpty()) {
                // Seleccionar entre 1 y 3 componentes para registrar
                $componentesSeleccionados = $componentes->random(min(rand(1, 3), $componentes->count()));

                foreach ($componentesSeleccionados as $comp) {
                    $estadosSalida = ['funcionando', 'funcionando', 'funcionando', 'dañado', 'reemplazado'];
                    $estadoSalida = $estado === 'finalizado'
                        ? $estadosSalida[array_rand($estadosSalida)]
                        : null;

                    FichaSoporteDetalle::create([
                        'ficha_soporte_id' => $ficha->id,
                        'componente_id' => $comp->id,
                        'componente_nombre' => $comp->tipo . ' - ' . ($comp->marca ?? 'N/A'),
                        'estado_ingreso' => $comp->estado === 'instalado' ? 'funcionando' : 'dañado',
                        'estado_salida' => $estadoSalida,
                        'observaciones' => 'Componente revisado durante el mantenimiento',
                    ]);

                    $detallesCreados++;
                }
            } else {
                // Si no tiene componentes, crear detalles genéricos
                for ($j = 0; $j < rand(1, 2); $j++) {
                    $tipos = ['RAM', 'Disco Duro', 'Batería', 'Cargador', 'Pantalla'];
                    $estadosSalida = ['funcionando', 'dañado', 'reemplazado'];
                    $estadoSalida = $estado === 'finalizado'
                        ? $estadosSalida[array_rand($estadosSalida)]
                        : null;

                    FichaSoporteDetalle::create([
                        'ficha_soporte_id' => $ficha->id,
                        'componente_id' => null,
                        'componente_nombre' => $tipos[array_rand($tipos)] . ' - Genérico',
                        'estado_ingreso' => 'funcionando',
                        'estado_salida' => $estadoSalida,
                        'observaciones' => 'Componente genérico verificado',
                    ]);

                    $detallesCreados++;
                }
            }

            // Mostrar progreso cada 3 fichas
            if ($fichasCreadas % 3 === 0) {
                $this->command->info("   📝 Fichas creadas: {$fichasCreadas}");
            }
        }

        // ============================================================
        // 5. CREAR ALGUNAS FICHAS CON EQUIPOS EXTERNOS (sin activo_id)
        // ============================================================

        $equiposExternos = [
            [
                'serial' => 'EXT-001',
                'marca' => 'HP',
                'modelo' => 'EliteBook 840 G5',
                'diagnostico' => 'Equipo no enciende. Se sospecha falla en placa madre.',
                'trabajo' => 'Se diagnosticó falla en placa madre. No se pudo reparar. Se recomienda reemplazo.',
            ],
            [
                'serial' => 'EXT-002',
                'marca' => 'Dell',
                'modelo' => 'Latitude 5480',
                'diagnostico' => 'Pantalla presenta manchas y líneas verticales.',
                'trabajo' => 'Se reemplazó el panel de la pantalla. Queda funcionando correctamente.',
            ],
            [
                'serial' => 'EXT-003',
                'marca' => 'Lenovo',
                'modelo' => 'ThinkPad T480',
                'diagnostico' => 'Equipo con lentitud extrema y sobrecalentamiento.',
                'trabajo' => 'Se realizó limpieza profunda, cambio de pasta térmica y se instaló SSD.',
            ],
        ];

        foreach ($equiposExternos as $index => $equipo) {
            $tecnico = $tecnicos->random();
            $tecnicoNombre = $tecnico->trabajador
                ? $tecnico->trabajador->nombre . ' ' . $tecnico->trabajador->apellido
                : $tecnico->usuario;

            $fechaIngreso = now()->subDays(rand(1, 20));

            $ficha = FichaSoporte::create([
                'activo_id' => null, // Equipo externo
                'tecnico_id' => $tecnico->id,
                'tecnico_nombre' => $tecnicoNombre,
                'usuario_reporta_id' => $usuarioAdmin->id,
                'usuario_reporta_nombre' => $nombresReportantes[array_rand($nombresReportantes)],
                'fecha_ingreso' => $fechaIngreso,
                'fecha_salida' => $fechaIngreso->copy()->addDays(rand(2, 5)),
                'diagnostico' => $equipo['diagnostico'],
                'trabajo_realizado' => $equipo['trabajo'],
                'observaciones' => 'Equipo externo - ' . $equipo['marca'] . ' ' . $equipo['modelo'] . ' Serial: ' . $equipo['serial'],
                'estado' => 'finalizado',
            ]);

            $fichasCreadas++;

            // Detalles genéricos para equipo externo
            FichaSoporteDetalle::create([
                'ficha_soporte_id' => $ficha->id,
                'componente_id' => null,
                'componente_nombre' => 'Equipo completo - ' . $equipo['marca'] . ' ' . $equipo['modelo'],
                'estado_ingreso' => 'dañado',
                'estado_salida' => 'reparado',
                'observaciones' => 'Equipo externo reparado y entregado',
            ]);

            $detallesCreados++;
        }

        // ============================================================
        // 6. RESUMEN FINAL
        // ============================================================

        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('✅ SEEDER COMPLETADO EXITOSAMENTE');
        $this->command->info('========================================');

        $totales = [
            ['Fichas de soporte creadas', $fichasCreadas],
            ['Detalles de fichas creados', $detallesCreados],
            ['Técnicos disponibles', $tecnicos->count()],
            ['Activos disponibles', $activos->count()],
        ];

        $this->command->table(
            ['Concepto', 'Cantidad'],
            $totales
        );

        // Resumen por estado
        $enProceso = FichaSoporte::where('estado', 'en_proceso')->count();
        $finalizados = FichaSoporte::where('estado', 'finalizado')->count();

        $this->command->newLine();
        $this->command->info('📋 Resumen por estado:');
        $this->command->line("   • En proceso: {$enProceso}");
        $this->command->line("   • Finalizados: {$finalizados}");
        $this->command->line("   • Total: " . ($enProceso + $finalizados));
    }
}
