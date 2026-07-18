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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolicitudPrestamoDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creando solicitudes y préstamos de prueba...');

        // ============================================================
        // 1. OBTENER DATOS BASE
        // ============================================================
        $usuario = Usuario::query()->where('usuario', 'jordy')->first()
            ?? Usuario::query()->first();

        $departamento = Departamento::query()->first();
        $institucion = Institucion::query()->first();
        $responsable = Responsable::query()->where('activo', true)->first();

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
        $activoDisponible = Activo::query()
            ->whereHas('estatus', function ($q) {
                $q->where('permite_prestamo', true);
            })
            ->first();

        $componenteDisponible = Componente::query()
            ->where('estado', 'en_bodega')
            ->first();

        if (!$activoDisponible && !$componenteDisponible) {
            $this->command->warn('⚠️ No hay activos o componentes disponibles.');
            return;
        }

        $this->command->info('✅ Items disponibles encontrados:');
        if ($activoDisponible) {
            $this->command->info("   - Activo: {$activoDisponible->serial} (ID: {$activoDisponible->id})");
        }
        if ($componenteDisponible) {
            $this->command->info("   - Componente: {$componenteDisponible->tipo} (ID: {$componenteDisponible->id})");
        }

        // ============================================================
        // 3. CREAR SOLICITUDES (SIN COLUMNA CODIGO)
        // ============================================================
        $plantillas = [
            [
                'justificacion' => 'Solicitud de prueba para equipo de oficina en el área administrativa. Se requiere computadora con componentes básicos para el personal nuevo.',
                'prioridad' => 'alta',
                'estado_solicitud' => 'aprobada',
                'tipo_solicitante' => 'interno',
                'departamento_id' => $departamento?->id,
                'institucion_id' => null,
                'responsable_id' => $responsable->id,
            ],
            [
                'justificacion' => 'Solicitud de prueba para laboratorio de informática. Se requieren componentes complementarios para actualizar los equipos del taller de reparación.',
                'prioridad' => 'normal',
                'estado_solicitud' => 'aprobada',
                'tipo_solicitante' => 'externo',
                'departamento_id' => null,
                'institucion_id' => $institucion?->id,
                'responsable_id' => $responsable->id,
            ],
            [
                'justificacion' => 'Solicitud de prueba pendiente para validar el flujo de aprobación. Este caso permite probar el proceso completo desde la solicitud hasta la creación del préstamo.',
                'prioridad' => 'baja',
                'estado_solicitud' => 'pendiente',
                'tipo_solicitante' => 'interno',
                'departamento_id' => $departamento?->id,
                'institucion_id' => null,
                'responsable_id' => $responsable->id,
            ],
        ];

        $solicitudesCreadas = [];

        foreach ($plantillas as $index => $plantilla) {
            try {
                DB::beginTransaction();

                // Crear solicitud (SIN columna codigo)
                $solicitud = Solicitud::query()->create([
                    'usuario_id' => $usuario->id,
                    'tipo_solicitante' => $plantilla['tipo_solicitante'],
                    'institucion_id' => $plantilla['institucion_id'],
                    'departamento_id' => $plantilla['departamento_id'],
                    'responsable_id' => $plantilla['responsable_id'],
                    'fecha_solicitud' => now()->subDays($index + 1),
                    'fecha_requerida' => now()->addDays($index + 3),
                    'fecha_fin_estimada' => now()->addDays($index + 10),
                    'justificacion' => $plantilla['justificacion'],
                    'prioridad' => $plantilla['prioridad'],
                    'estado_solicitud' => $plantilla['estado_solicitud'],
                    'observaciones' => 'Generada automáticamente para pruebas de préstamos.',
                    // 'codigo' => 'SOL-' . date('Y') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT), // <-- ELIMINADO
                ]);

                $solicitudesCreadas[] = $solicitud;
                $this->command->info("✅ Solicitud #{$solicitud->id} creada - Estado: {$solicitud->estado_solicitud}");

                // Agregar detalles de la solicitud
                if ($activoDisponible) {
                    DetalleSolicitud::query()->create([
                        'solicitud_id' => $solicitud->id,
                        'activo_id' => $activoDisponible->id,
                        'tipo_item' => 'activo',
                        'cantidad_solicitada' => 1,
                        'descripcion_personalizada' => "Activo: {$activoDisponible->serial}",
                        'observaciones' => 'Item de prueba para solicitud',
                    ]);
                    $this->command->info("   - Item agregado: Activo {$activoDisponible->serial}");
                }

                if ($componenteDisponible) {
                    DetalleSolicitud::query()->create([
                        'solicitud_id' => $solicitud->id,
                        'componente_id' => $componenteDisponible->id,
                        'tipo_item' => 'componente',
                        'cantidad_solicitada' => 1,
                        'descripcion_personalizada' => "Componente: {$componenteDisponible->tipo}",
                        'observaciones' => 'Componente de prueba para solicitud',
                    ]);
                    $this->command->info("   - Item agregado: Componente {$componenteDisponible->tipo}");
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("❌ Error al crear solicitud: " . $e->getMessage());
                Log::error('Error en SolicitudPrestamoDemoSeeder: ' . $e->getMessage());
            }
        }

        // ============================================================
        // 4. CREAR PRÉSTAMOS
        // ============================================================
        $this->command->info('📦 Creando préstamos...');

        // Préstamo 1: Desde la primera solicitud (aprobada) - ESTADO DEVUELTO
        if (isset($solicitudesCreadas[0])) {
            $this->crearPrestamo(
                $solicitudesCreadas[0],
                'devuelto',
                $responsable,
                $usuario,
                $activoDisponible,
                $componenteDisponible
            );
        }

        // Préstamo 2: Desde la segunda solicitud (aprobada) - ESTADO APROBADO
        if (isset($solicitudesCreadas[1])) {
            $this->crearPrestamo(
                $solicitudesCreadas[1],
                'aprobado',
                $responsable,
                $usuario,
                $activoDisponible,
                $componenteDisponible
            );
        }

        // Préstamo 3: Sin solicitud (directo) - ESTADO DEVUELTO
        $this->crearPrestamo(
            null,
            'devuelto',
            $responsable,
            $usuario,
            $activoDisponible,
            $componenteDisponible
        );

        // Préstamo 4: Sin solicitud (directo) - ESTADO PENDIENTE
        $this->crearPrestamo(
            null,
            'pendiente',
            $responsable,
            $usuario,
            $activoDisponible,
            $componenteDisponible
        );

        // Préstamo 5: Sin solicitud (directo) - ESTADO ENTREGADO
        $this->crearPrestamo(
            null,
            'entregado',
            $responsable,
            $usuario,
            $activoDisponible,
            $componenteDisponible
        );

        $this->command->info('✅ Solicitudes y préstamos de prueba creados correctamente.');
    }

    /**
     * Crear un préstamo con sus detalles.
     *
     * CORREGIDO: No usa métodos inexistentes en Componente
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
            $fechaPrestamo = now()->subDays(rand(1, 5));
            $fechaDevolucionEsperada = $fechaPrestamo->copy()->addDays(rand(3, 10));

            // Fecha real de devolución solo si está devuelto
            $fechaDevolucionReal = null;
            if ($estado === 'devuelto' || $estado === 'cancelado') {
                $fechaDevolucionReal = $fechaDevolucionEsperada->copy()->addDays(rand(0, 2));
            }

            // Crear préstamo
            $prestamo = Prestamo::query()->create([
                'codigo' => Prestamo::generarCodigo(),
                'tipo_prestamo' => $tipoPrestamo,
                'estado' => $estado,
                'departamento_id' => $solicitud?->departamento_id,
                'institucion_id' => $solicitud?->institucion_id,
                'responsable_receptor_id' => $responsable->id,
                'responsable_emisor_id' => $responsable->id,
                'usuario_registra_id' => $usuario->id,
                'fecha_prestamo' => $fechaPrestamo,
                'fecha_devolucion_esperada' => $fechaDevolucionEsperada,
                'fecha_devolucion_real' => $fechaDevolucionReal,
                'observaciones' => 'Préstamo generado por el seeder de pruebas.',
                'solicitud_id' => $solicitud?->id,
                'tiene_extension' => false,
                'total_extensiones' => 0,
            ]);

            $this->command->info("   ✅ Préstamo {$prestamo->codigo} creado - Estado: {$estado}");

            // Agregar detalles del préstamo - ACTIVO
            if ($activoDisponible) {
                $detalle = PrestamoDetalle::query()->create([
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

                $this->command->info("      - Item: Activo {$activoDisponible->serial}");

                // Actualizar estado del activo (SOLO SI EXISTE EL MÉTODO)
                if (in_array($estado, ['entregado', 'aprobado', 'extendido']) && method_exists($activoDisponible, 'marcarComoPrestado')) {
                    $activoDisponible->marcarComoPrestado();
                } elseif ($estado === 'devuelto' && method_exists($activoDisponible, 'marcarComoDisponible')) {
                    $activoDisponible->marcarComoDisponible();
                }
            }

            // Agregar detalles del préstamo - COMPONENTE
            if ($componenteDisponible) {
                $detalle = PrestamoDetalle::query()->create([
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

                $this->command->info("      - Item: Componente {$componenteDisponible->tipo}");

                // ACTUALIZAR ESTADO DEL COMPONENTE - CORREGIDO
                // Usamos actualización directa en lugar de métodos inexistentes
                if (in_array($estado, ['entregado', 'aprobado', 'extendido'])) {
                    // Si el componente tiene método marcarComoPrestado, usarlo
                    if (method_exists($componenteDisponible, 'marcarComoPrestado')) {
                        $componenteDisponible->marcarComoPrestado();
                    } else {
                        // Si no, actualizar directamente
                        $componenteDisponible->update(['estado' => 'prestado']);
                    }
                } elseif ($estado === 'devuelto') {
                    // Si el componente tiene método marcarComoDisponible, usarlo
                    if (method_exists($componenteDisponible, 'marcarComoDisponible')) {
                        $componenteDisponible->marcarComoDisponible();
                    } else {
                        // Si no, actualizar directamente
                        $componenteDisponible->update(['estado' => 'en_bodega']);
                    }
                }
            }

            // Actualizar solicitud si existe
            if ($solicitud) {
                $nuevoEstado = match($estado) {
                    'devuelto', 'entregado' => 'entregada',
                    'aprobado' => 'aprobada',
                    'pendiente' => 'pendiente',
                    default => 'aprobada'
                };
                $solicitud->update(['estado_solicitud' => $nuevoEstado]);
                $this->command->info("      - Solicitud #{$solicitud->id} actualizada a: {$nuevoEstado}");
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("   ❌ Error al crear préstamo: " . $e->getMessage());
            Log::error('Error en crearPrestamo: ' . $e->getMessage());
        }
    }
}
