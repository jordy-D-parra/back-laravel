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
use Illuminate\Support\Facades\Log;

class SoporteTecnicoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creando datos de demostración para Soporte Técnico...');

        // ============================================================
        // 1. VALIDAR DATOS BASE
        // ============================================================
        $usuarioAdmin = Usuario::where('usuario', 'jordy')->first()
            ?? Usuario::where('usuario', 'admin')->first()
            ?? Usuario::where('usuario', 'melo')->first()
            ?? Usuario::first();

        if (!$usuarioAdmin) {
            $this->command->error('❌ No se encontró ningún usuario.');
            $this->command->error('   Ejecuta primero: php artisan db:seed --class=UsuarioAdminSeeder');
            return;
        }

        // Validar estatus
        $estatusReparacion = Estatus::where('descripcion', 'En reparación')->first();
        $estatusDisponible = Estatus::where('descripcion', 'Disponible')->first();

        if (!$estatusReparacion || !$estatusDisponible) {
            $this->command->error('❌ No se encontraron los estatus "En reparación" o "Disponible".');
            $this->command->error('   Ejecuta primero: php artisan db:seed --class=EstatusSeeder');
            return;
        }

        // Buscar técnicos
        $tecnicos = Usuario::whereHas('rol', function ($q) {
            $q->whereIn('nombre', ['admin', 'ingeniero', 'tecnico']);
        })->with('trabajador')->get();

        if ($tecnicos->isEmpty()) {
            $this->command->warn('⚠️ No se encontraron técnicos. Usando usuario admin como técnico.');
            $tecnicos = collect([$usuarioAdmin]);
        }

        // Obtener activos
        $activos = Activo::with(['modelo.marca', 'estatus'])->get();

        if ($activos->isEmpty()) {
            $this->command->error('❌ No hay activos en el sistema.');
            $this->command->error('   Ejecuta primero: php artisan db:seed --class=InventarioDemoSeeder');
            return;
        }

        $this->command->info('✅ Datos base encontrados:');
        $this->command->info("   - Usuario admin: {$usuarioAdmin->usuario} (ID: {$usuarioAdmin->id})");
        $this->command->info("   - Técnicos disponibles: {$tecnicos->count()}");
        $this->command->info("   - Activos disponibles: {$activos->count()}");
        $this->command->info("   - Estatus reparación ID: {$estatusReparacion->id}");

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
        // 3. LIMPIAR DATOS PREVIOS DE FICHAS (borrado ordenado)
        // ============================================================
        // ✅ FIX: En PostgreSQL, un usuario normal NO puede usar
        //    SET session_replication_role. Se borra respetando el orden
        //    de las foreign keys: primero los hijos, luego los padres.
        FichaSoporteDetalle::query()->delete();
        FichaSoporte::query()->delete();

        // Reiniciar secuencias (por si acaso se quiere IDs limpios)
        try {
            DB::statement('ALTER SEQUENCE fichas_soporte_detalle_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE fichas_soporte_id_seq RESTART WITH 1');
        } catch (\Throwable $e) {
            // Si las secuencias no existen con ese nombre exacto, ignorar
            Log::warning('No se pudieron reiniciar las secuencias: ' . $e->getMessage());
        }

        $this->command->info('🧹 Datos previos de fichas limpiados.');

        // ============================================================
        // 4. CREAR FICHAS DE SOPORTE CON ACTIVOS EXISTENTES
        // ============================================================
        $this->command->info('📝 Creando fichas de soporte con activos del inventario...');

        $fichasCreadas = 0;
        $detallesCreados = 0;

        // Tomar los primeros 12 activos para asignarles fichas
        $activosParaFichas = $activos->take(12);

        foreach ($activosParaFichas as $index => $activo) {
            try {
                // Alternar entre en_proceso y finalizado
                $estado = $index < 7 ? 'en_proceso' : 'finalizado';

                $tecnico = $tecnicos->random();
                $tecnicoNombre = $tecnico->trabajador
                    ? $tecnico->trabajador->nombre . ' ' . $tecnico->trabajador->apellido
                    : $tecnico->usuario;

                // Fechas
                $fechaIngreso = now()->subDays(rand(1, 30));
                $fechaSalida = $estado === 'finalizado'
                    ? $fechaIngreso->copy()->addDays(rand(1, 10))
                    : null;

                // Fecha requerida de entrega (algunas fichas con fecha, otras sin)
                $fechaRequerida = rand(0, 1)
                    ? $fechaIngreso->copy()->addDays(rand(5, 15))
                    : null;

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
                    'fecha_requerida_entrega' => $fechaRequerida,
                    'fecha_salida' => $fechaSalida,
                    'diagnostico' => $diagnostico,
                    'trabajo_realizado' => $trabajoRealizado,
                    'observaciones' => 'Observaciones adicionales para la ficha #' . ($fichasCreadas + 1),
                    'estado' => $estado,
                    'origen' => 'manual',
                ]);

                $fichasCreadas++;

                // Cambiar estado del activo
                if ($estado === 'en_proceso') {
                    $activo->update(['id_estatus' => $estatusReparacion->id]);
                } else {
                    $activo->update(['id_estatus' => $estatusDisponible->id]);
                }

                // ============================================================
                // 5. CREAR DETALLES DE LA FICHA (Componentes)
                // ============================================================
                $componentes = Componente::where('activo_id', $activo->id)->get();

                if ($componentes->isNotEmpty()) {
                    // Seleccionar entre 1 y 3 componentes
                    $cantidadSeleccionar = min(rand(1, 3), $componentes->count());
                    $componentesSeleccionados = $componentes->random($cantidadSeleccionar);

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
                    // Si no hay componentes, crear detalles genéricos
                    $cantidadGenericos = rand(1, 2);
                    for ($j = 0; $j < $cantidadGenericos; $j++) {
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

                // Mostrar progreso
                if ($fichasCreadas % 3 === 0) {
                    $this->command->info("   📝 Fichas creadas: {$fichasCreadas}");
                }

            } catch (\Throwable $e) {
                $this->command->error("   ❌ Error al crear ficha para activo #{$activo->id}: " . $e->getMessage());
                Log::error('Error en SoporteTecnicoSeeder: ' . $e->getMessage(), [
                    'activo_id' => $activo->id,
                    'trace' => $e->getTraceAsString(),
                ]);
                continue;
            }
        }

        // ============================================================
        // 6. CREAR FICHAS ADICIONALES CON ESTADOS VARIADOS
        // ============================================================
        $this->command->info('📝 Creando fichas adicionales con estados variados...');

        // Tomar otros activos para más variedad
        $activosExtra = $activos->skip(12)->take(8);

        foreach ($activosExtra as $index => $activo) {
            try {
                $tecnico = $tecnicos->random();
                $tecnicoNombre = $tecnico->trabajador
                    ? $tecnico->trabajador->nombre . ' ' . $tecnico->trabajador->apellido
                    : $tecnico->usuario;

                $fechaIngreso = now()->subDays(rand(30, 90));
                $fechaSalida = $fechaIngreso->copy()->addDays(rand(2, 15));

                $ficha = FichaSoporte::create([
                    'activo_id' => $activo->id,
                    'tecnico_id' => $tecnico->id,
                    'tecnico_nombre' => $tecnicoNombre,
                    'usuario_reporta_id' => $usuarioAdmin->id,
                    'usuario_reporta_nombre' => $nombresReportantes[array_rand($nombresReportantes)],
                    'fecha_ingreso' => $fechaIngreso,
                    'fecha_requerida_entrega' => $fechaIngreso->copy()->addDays(rand(5, 20)),
                    'fecha_salida' => $fechaSalida,
                    'diagnostico' => $diagnosticos[array_rand($diagnosticos)],
                    'trabajo_realizado' => $trabajosRealizados[array_rand($trabajosRealizados)],
                    'observaciones' => 'Ficha histórica finalizada',
                    'estado' => 'finalizado',
                    'origen' => 'manual',
                ]);

                $fichasCreadas++;

                // Asegurar que el activo esté disponible
                $activo->update(['id_estatus' => $estatusDisponible->id]);

                // Detalles genéricos
                for ($j = 0; $j < rand(1, 2); $j++) {
                    $tipos = ['RAM', 'Disco Duro', 'Batería', 'Cargador'];
                    FichaSoporteDetalle::create([
                        'ficha_soporte_id' => $ficha->id,
                        'componente_id' => null,
                        'componente_nombre' => $tipos[array_rand($tipos)] . ' - Genérico',
                        'estado_ingreso' => 'funcionando',
                        'estado_salida' => 'funcionando',
                        'observaciones' => 'Componente verificado',
                    ]);
                    $detallesCreados++;
                }

            } catch (\Throwable $e) {
                $this->command->error("   ❌ Error en ficha extra #{$index}: " . $e->getMessage());
                continue;
            }
        }

        // ============================================================
        // 7. RESUMEN FINAL
        // ============================================================
        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('✅ SEEDER COMPLETADO EXITOSAMENTE');
        $this->command->info('========================================');

        $totales = [
            ['Fichas de soporte creadas', $fichasCreadas],
            ['Detalles de fichas creados', $detallesCreados],
            ['Técnicos utilizados', $tecnicos->count()],
            ['Activos utilizados', min(20, $activos->count())],
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