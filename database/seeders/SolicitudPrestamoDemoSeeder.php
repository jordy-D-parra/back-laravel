<?php

namespace Database\Seeders;

use App\Models\Activo;
use App\Models\Componente;
use App\Models\Departamento;
use App\Models\Institucion;
use App\Models\Prestamo;
use App\Models\PrestamoDetalle;
use App\Models\Responsable;
use App\Models\Solicitud;
use App\Models\DetalleSolicitud;
use App\Models\Usuario;
use App\Models\Estado;
use App\Models\Municipio;
use App\Models\Parroquia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Faker\Factory as Faker;

class SolicitudPrestamoDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creando solicitudes y préstamos de prueba...');
        $faker = Faker::create('es_ES');

        // ============================================================
        // 1. OBTENER DATOS BASE
        // ============================================================
        $usuario = Usuario::query()->where('usuario', 'jordy')->first()
            ?? Usuario::query()->first();

        $departamento = Departamento::query()->first();
        $institucion = Institucion::query()->first();
        $responsable = Responsable::query()->where('activo', true)->first();

        // Obtener ubicaciones de Yaracuy
        $estadoYaracuy = Estado::where('nombre', 'Yaracuy')->first();
        $municipios = Municipio::where('estado_id', $estadoYaracuy?->id)->get();
        $parroquias = Parroquia::whereIn('municipio_id', $municipios->pluck('id'))->get();

        if (!$usuario) {
            $this->command->error('❌ No se encontró ningún usuario.');
            return;
        }

        if (!$responsable) {
            $this->command->error('❌ No se encontró ningún responsable activo.');
            return;
        }

        $this->command->info('✅ Datos base encontrados:');
        $this->command->info("   - Usuario: {$usuario->usuario} (ID: {$usuario->id})");
        $this->command->info("   - Responsable: {$responsable->nombre} (ID: {$responsable->id})");

        // ============================================================
        // 2. BUSCAR ACTIVOS Y COMPONENTES DISPONIBLES
        // ============================================================
        $activosDisponibles = Activo::query()
            ->whereHas('estatus', function ($q) {
                $q->where('permite_prestamo', true);
            })
            ->limit(10)
            ->get();

        $componentesDisponibles = Componente::query()
            ->where('estado', 'en_bodega')
            ->limit(10)
            ->get();

        if ($activosDisponibles->isEmpty() && $componentesDisponibles->isEmpty()) {
            $this->command->warn('⚠️ No hay activos o componentes disponibles.');
            return;
        }

        $this->command->info('✅ Items disponibles encontrados:');
        $this->command->info("   - Activos disponibles: {$activosDisponibles->count()}");
        $this->command->info("   - Componentes disponibles: {$componentesDisponibles->count()}");

        // ============================================================
        // 3. CREAR 30+ SOLICITUDES PARA PROBAR PAGINACIÓN
        // ============================================================
        $this->command->info('📝 Creando múltiples solicitudes...');

        $estadosSolicitud = ['pendiente', 'aprobada', 'rechazada', 'cancelada'];
        $prioridades = ['baja', 'normal', 'alta', 'urgente'];
        $tiposSolicitante = ['interno', 'externo'];

        $solicitudesCreadas = [];
        $totalSolicitudes = 35; // 35 solicitudes para probar paginación (3 páginas de 10)

        // Lista de lugares de eventos en Yaracuy
        $lugaresEvento = [
            'Auditorio Principal de la Gobernación',
            'Salón de Conferencias del Hospital Central',
            'Laboratorio de Informática UNEY',
            'Sala de Reuniones de la Alcaldía',
            'Planta Baja de la Gobernación',
            'Edificio Administrativo de la UNEY',
            'Centro de Salud San Felipe',
            'Escuela Bolivariana Simón Bolívar - Salón Principal',
            'Comando de la Policía del Estado Yaracuy',
            'Casa de la Cultura de San Felipe',
            'Estadio de Béisbol de San Felipe',
            'Mercado Municipal de San Felipe',
            'Plaza Bolívar de San Felipe',
            'Terminal de Pasajeros de San Felipe',
            'Centro Comercial Plaza Mayor',
            'Hotel San Felipe',
            'Centro de Convenciones de Yaracuy',
            'Parque La Victoria',
            'Club de Leones de San Felipe',
            'Sede del Consejo Legislativo de Yaracuy'
        ];

        for ($i = 0; $i < $totalSolicitudes; $i++) {
            try {
                DB::beginTransaction();

                $tipoSolicitante = $tiposSolicitante[array_rand($tiposSolicitante)];
                $prioridad = $prioridades[array_rand($prioridades)];
                $estado = $estadosSolicitud[array_rand($estadosSolicitud)];

                // Seleccionar departamento o institución según tipo
                $departamentoId = null;
                $institucionId = null;

                if ($tipoSolicitante === 'interno') {
                    $deptos = Departamento::where('activo', true)->get();
                    if ($deptos->isNotEmpty()) {
                        $departamentoId = $deptos->random()->id;
                    }
                } else {
                    $insts = Institucion::where('activo', true)->get();
                    if ($insts->isNotEmpty()) {
                        $institucionId = $insts->random()->id;
                    }
                }

                // Seleccionar ubicación aleatoria
                $municipio = $municipios->random();
                $parroquia = Parroquia::where('municipio_id', $municipio->id)->first();
                $lugarEvento = $lugaresEvento[array_rand($lugaresEvento)];

                // Fechas
                $fechaSolicitud = $faker->dateTimeBetween('-60 days', 'now');
                $fechaRequerida = $faker->dateTimeBetween('+1 day', '+30 days');
                $fechaFinEstimada = $faker->dateTimeBetween(
                    $fechaRequerida->format('Y-m-d'),
                    (clone $fechaRequerida)->modify('+30 days')->format('Y-m-d')
                );

                // Justificaciones variadas
                $justificaciones = [
                    'Necesitamos equipos para el nuevo personal del departamento. Se requiere con urgencia para comenzar las labores.',
                    'Solicito equipos para el laboratorio de informática. Los equipos actuales están obsoletos.',
                    'Urge la reparación de equipos en el área administrativa para no detener la productividad.',
                    'Se requiere equipamiento para el evento de fin de año de la institución.',
                    'Los equipos actuales presentan fallas críticas. Se necesita reemplazo inmediato.',
                    'Solicitud de equipos para el taller de formación técnica que se realizará próximamente.',
                    'Necesitamos computadoras para el nuevo proyecto de digitalización de archivos.',
                    'Se requiere equipamiento para la sala de servidores. Los equipos actuales no soportan la demanda.',
                    'Solicito componentes para actualizar los equipos del departamento de sistemas.',
                    'Urge la compra/reparación de equipos para el área de atención al público.'
                ];

                $justificacion = $justificaciones[array_rand($justificaciones)];

                // Crear solicitud
                $solicitud = Solicitud::query()->create([
                    'usuario_id' => $usuario->id,
                    'tipo_solicitante' => $tipoSolicitante,
                    'institucion_id' => $institucionId,
                    'departamento_id' => $departamentoId,
                    'responsable_id' => $responsable->id,
                    'fecha_solicitud' => $fechaSolicitud,
                    'fecha_requerida' => $fechaRequerida,
                    'fecha_fin_estimada' => $fechaFinEstimada,
                    'justificacion' => $justificacion,
                    'prioridad' => $prioridad,
                    'estado_solicitud' => $estado,
                    'observaciones' => $faker->optional(0.6)->sentence(10),
                    'estado_id' => $estadoYaracuy?->id,
                    'municipio_id' => $municipio?->id,
                    'parroquia_id' => $parroquia?->id,
                    'lugar_evento' => $lugarEvento,
                ]);

                $solicitudesCreadas[] = $solicitud;
                $this->command->info("   ✅ Solicitud #{$solicitud->id} creada - Estado: {$estado} - Prioridad: {$prioridad}");

                // Agregar entre 1 y 3 detalles a la solicitud
                $numItems = rand(1, 3);
                for ($j = 0; $j < $numItems; $j++) {
                    $tipoItem = rand(0, 1) === 0 ? 'activo' : 'componente';
                    $activo = $tipoItem === 'activo' ? $activosDisponibles->random() : null;
                    $componente = $tipoItem === 'componente' ? $componentesDisponibles->random() : null;
                    $cantidad = rand(1, 5);

                    $descripcionPersonalizada = '';
                    if ($tipoItem === 'activo' && $activo) {
                        $descripcionPersonalizada = "Activo: {$activo->serial} - " . ($activo->modelo?->nombre ?? 'Sin modelo');
                    } elseif ($tipoItem === 'componente' && $componente) {
                        $descripcionPersonalizada = "Componente: {$componente->tipo} - {$componente->marca}";
                    } else {
                        $descripcionPersonalizada = "Item genérico: " . $faker->word() . " " . $faker->randomNumber(3);
                    }

                    DetalleSolicitud::query()->create([
                        'solicitud_id' => $solicitud->id,
                        'activo_id' => $activo?->id,
                        'componente_id' => $componente?->id,
                        'tipo_item' => $tipoItem,
                        'cantidad_solicitada' => $cantidad,
                        'descripcion_personalizada' => $descripcionPersonalizada,
                        'observaciones' => $faker->optional(0.3)->sentence(6),
                    ]);
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("   ❌ Error al crear solicitud: " . $e->getMessage());
                Log::error('Error en SolicitudPrestamoDemoSeeder: ' . $e->getMessage());
            }
        }

        $this->command->info('✅ ' . count($solicitudesCreadas) . ' solicitudes creadas para paginación.');

        // ============================================================
        // 4. CREAR PRÉSTAMOS (SOLO PARA ALGUNAS SOLICITUDES)
        // ============================================================
        $this->command->info('📦 Creando préstamos...');

        // Seleccionar solicitudes aprobadas para crear préstamos
        $solicitudesAprobadas = Solicitud::where('estado_solicitud', 'aprobada')
            ->limit(8)
            ->get();

        // Seleccionar solicitudes pendientes para crear préstamos
        $solicitudesPendientes = Solicitud::where('estado_solicitud', 'pendiente')
            ->limit(5)
            ->get();

        // Combinar ambas colecciones
        $solicitudesParaPrestamo = $solicitudesAprobadas->merge($solicitudesPendientes);

        foreach ($solicitudesParaPrestamo as $index => $solicitud) {
            // Alternar estados para variedad
            $estados = ['entregado', 'devuelto', 'aprobado', 'pendiente', 'extendido'];
            $estado = $estados[$index % count($estados)];

            // Si la solicitud está pendiente, solo crear préstamos pendientes o aprobados
            if ($solicitud->estado_solicitud === 'pendiente') {
                $estado = $index % 2 === 0 ? 'pendiente' : 'aprobado';
            }

            $this->crearPrestamo(
                $solicitud,
                $estado,
                $responsable,
                $usuario,
                $activosDisponibles->random() ?? null,
                $componentesDisponibles->random() ?? null
            );
        }

        // Crear algunos préstamos sin solicitud (directos)
        $this->command->info('📦 Creando préstamos directos (sin solicitud)...');

        for ($i = 0; $i < 8; $i++) {
            $estados = ['entregado', 'devuelto', 'aprobado', 'pendiente', 'extendido'];
            $estado = $estados[$i % count($estados)];

            $this->crearPrestamo(
                null,
                $estado,
                $responsable,
                $usuario,
                $activosDisponibles->random() ?? null,
                $componentesDisponibles->random() ?? null
            );
        }

        $this->command->info('✅ Solicitudes y préstamos de prueba creados correctamente.');

        // Mostrar resumen final
        $totalSolicitudesCreadas = Solicitud::count();
        $totalPrestamosCreados = Prestamo::count();
        $this->command->info('📊 Resumen final:');
        $this->command->info("   - Total solicitudes: {$totalSolicitudesCreadas}");
        $this->command->info("   - Total préstamos: {$totalPrestamosCreados}");
    }

    /**
     * Crear un préstamo con sus detalles.
     */
    private function crearPrestamo(
        ?Solicitud $solicitud,
        string $estado,
        Responsable $responsable,
        Usuario $usuario,
        ?Activo $activoDisponible,
        ?Componente $componenteDisponible
    ): void {
        try {
            DB::beginTransaction();

            // Determinar tipo de préstamo
            $tipoPrestamo = 'equipo';
            if ($activoDisponible && $componenteDisponible) {
                $tipoPrestamo = 'mixto';
            } elseif ($componenteDisponible && !$activoDisponible) {
                $tipoPrestamo = 'componente';
            }

            // Fechas
            $fechaPrestamo = now()->subDays(rand(1, 15));
            $fechaDevolucionEsperada = $fechaPrestamo->copy()->addDays(rand(3, 20));

            // Fecha real de devolución solo si está devuelto
            $fechaDevolucionReal = null;
            if ($estado === 'devuelto') {
                $fechaDevolucionReal = $fechaDevolucionEsperada->copy()->addDays(rand(0, 5));
            }

            // Crear préstamo
            $prestamo = Prestamo::query()->create([
                'codigo' => Prestamo::generarCodigo(),
                'tipo_prestamo' => $tipoPrestamo,
                'estado' => $estado,
                'departamento_id' => $solicitud?->departamento_id ?? Departamento::query()->first()?->id,
                'institucion_id' => $solicitud?->institucion_id ?? Institucion::query()->first()?->id,
                'responsable_receptor_id' => $responsable->id,
                'responsable_emisor_id' => $responsable->id,
                'usuario_registra_id' => $usuario->id,
                'fecha_prestamo' => $fechaPrestamo,
                'fecha_devolucion_esperada' => $fechaDevolucionEsperada,
                'fecha_devolucion_real' => $fechaDevolucionReal,
                'observaciones' => 'Préstamo generado por el seeder de pruebas.',
                'solicitud_id' => $solicitud?->id,
                'tiene_extension' => $estado === 'extendido',
                'total_extensiones' => $estado === 'extendido' ? rand(1, 2) : 0,
            ]);

            // Agregar detalles del préstamo - ACTIVO
            if ($activoDisponible) {
                PrestamoDetalle::query()->create([
                    'prestamo_id' => $prestamo->id,
                    'prestable_type' => Activo::class,
                    'prestable_id' => $activoDisponible->id,
                    'cantidad' => 1,
                    'estado_entrega' => in_array($estado, ['entregado', 'devuelto', 'aprobado', 'extendido'])
                        ? 'Entregado en buen estado'
                        : 'Pendiente de entrega',
                    'estado_devolucion' => $estado === 'devuelto'
                        ? 'Devuelto en buen estado'
                        : null,
                    'observaciones' => 'Detalle de prueba para activo',
                ]);

                // Actualizar estado del activo
                if (in_array($estado, ['entregado', 'aprobado', 'extendido']) && method_exists($activoDisponible, 'marcarComoPrestado')) {
                    $activoDisponible->marcarComoPrestado();
                } elseif ($estado === 'devuelto' && method_exists($activoDisponible, 'marcarComoDisponible')) {
                    $activoDisponible->marcarComoDisponible();
                }
            }

            // Agregar detalles del préstamo - COMPONENTE
            if ($componenteDisponible) {
                PrestamoDetalle::query()->create([
                    'prestamo_id' => $prestamo->id,
                    'prestable_type' => Componente::class,
                    'prestable_id' => $componenteDisponible->id,
                    'cantidad' => 1,
                    'estado_entrega' => in_array($estado, ['entregado', 'devuelto', 'aprobado', 'extendido'])
                        ? 'Entregado en buen estado'
                        : 'Pendiente de entrega',
                    'estado_devolucion' => $estado === 'devuelto'
                        ? 'Devuelto en buen estado'
                        : null,
                    'observaciones' => 'Detalle de prueba para componente',
                ]);

                // Actualizar estado del componente
                if (in_array($estado, ['entregado', 'aprobado', 'extendido'])) {
                    if (method_exists($componenteDisponible, 'marcarComoPrestado')) {
                        $componenteDisponible->marcarComoPrestado();
                    } else {
                        $componenteDisponible->update(['estado' => 'prestado']);
                    }
                } elseif ($estado === 'devuelto') {
                    if (method_exists($componenteDisponible, 'marcarComoDisponible')) {
                        $componenteDisponible->marcarComoDisponible();
                    } else {
                        $componenteDisponible->update(['estado' => 'en_bodega']);
                    }
                }
            }

            // Actualizar solicitud si existe
            if ($solicitud && $solicitud->estado_solicitud !== 'aprobada') {
                $nuevoEstado = match($estado) {
                    'devuelto', 'entregado' => 'entregada',
                    'aprobado' => 'aprobada',
                    'pendiente' => 'pendiente',
                    default => 'aprobada'
                };
                $solicitud->update(['estado_solicitud' => $nuevoEstado]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en crearPrestamo: ' . $e->getMessage());
        }
    }
}
