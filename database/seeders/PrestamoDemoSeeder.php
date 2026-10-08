<?php

// database/seeders/PrestamoDemoSeeder.php

namespace Database\Seeders;

use App\Models\Prestamo;
use App\Models\PrestamoDetalle;
use App\Models\PrestamoExtension;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Departamento;
use App\Models\Institucion;
use App\Models\Responsable;
use App\Models\Solicitud;
use App\Models\Usuario;
use App\Models\Estatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PrestamoDemoSeeder extends Seeder
{
    /**
     * ✅ Contador en memoria para los códigos de préstamo.
     * Evita consultar la BD en cada iteración (problema de aislamiento
     * de transacciones → códigos duplicados).
     */
    private int $contadorCodigo = 0;

    public function run(): void
    {
        $this->command->info('🚀 Creando préstamos de demostración...');

        // ============================================================
        // 1. VALIDAR DATOS BASE
        // ============================================================
        $usuario = Usuario::where('usuario', 'jordy')->first()
            ?? Usuario::where('usuario', 'melo')->first()
            ?? Usuario::first();

        if (!$usuario) {
            $this->command->error('❌ No hay usuarios. Ejecuta primero UsuarioAdminSeeder.');
            return;
        }

        $departamento = Departamento::where('activo', true)->first();
        $institucion  = Institucion::where('activo', true)->first();
        $responsable  = Responsable::where('activo', true)->first();

        if (!$departamento && !$institucion) {
            $this->command->error('❌ No hay departamentos ni instituciones. Ejecuta primero EntidadesSeeder.');
            return;
        }

        if (!$responsable) {
            $this->command->error('❌ No hay responsables activos. Ejecuta primero EntidadesSeeder.');
            return;
        }

        // Estatus para los activos
        $estatusDisponible = Estatus::where('descripcion', 'Disponible')->first();
        $estatusPrestado   = Estatus::where('descripcion', 'Prestado')->first();

        if (!$estatusDisponible || !$estatusPrestado) {
            $this->command->error('❌ Faltan estatus "Disponible" o "Prestado". Ejecuta EstatusSeeder.');
            return;
        }

        // Activos y componentes disponibles
        $activosDisponibles = Activo::whereHas('estatus', function ($q) {
                $q->where('permite_prestamo', true);
            })
            ->whereNull('reservado_en_prestamo_id')
            ->limit(30)
            ->get();

        $componentesDisponibles = Componente::where('estado', 'en_bodega')
            ->whereNull('reservado_en_prestamo_id')
            ->limit(30)
            ->get();

        if ($activosDisponibles->isEmpty() && $componentesDisponibles->isEmpty()) {
            $this->command->error('❌ No hay activos ni componentes disponibles. Ejecuta InventarioDemoSeeder.');
            return;
        }

        // Solicitudes aprobadas (sin préstamo aún)
        $solicitudesDisponibles = Solicitud::whereIn('estado_solicitud', ['aprobada', 'pendiente'])
            ->whereDoesntHave('prestamos')
            ->limit(15)
            ->get();

        $this->command->info('✅ Datos base encontrados:');
        $this->command->info("   - Usuario: {$usuario->usuario}");
        $this->command->info("   - Activos disponibles: {$activosDisponibles->count()}");
        $this->command->info("   - Componentes disponibles: {$componentesDisponibles->count()}");
        $this->command->info("   - Solicitudes disponibles: {$solicitudesDisponibles->count()}");

        // ============================================================
        // 2. LIMPIAR PRÉSTAMOS ANTERIORES
        // ✅ Usamos forceDelete() para evitar que SoftDeletes interfiera
        //    con la restricción UNIQUE sobre `codigo`.
        // ============================================================
        $this->command->info('🧹 Limpiando préstamos previos...');

        PrestamoExtension::query()->delete();
        PrestamoDetalle::query()->delete();

        // Forzar borrado físico (evita conflictos con UNIQUE sobre codigo)
        Prestamo::query()->forceDelete();

        // Liberar reservas
        Activo::whereNotNull('reservado_en_prestamo_id')->update(['reservado_en_prestamo_id' => null]);
        Componente::whereNotNull('reservado_en_prestamo_id')->update(['reservado_en_prestamo_id' => null]);

        // ✅ Resetear el contador tras la limpieza
        $this->contadorCodigo = 0;

        $this->command->info('✅ Limpieza completada.');

        // ============================================================
        // 3. CREAR 25 PRÉSTAMOS CON DIFERENTES ESTADOS
        // ============================================================
        $estados = [
            'pendiente' => 5,
            'aprobado'  => 4,
            'entregado' => 6,
            'extendido' => 3,
            'devuelto'  => 5,
            'cancelado' => 1,
            'rechazado' => 1,
        ];

        $totalCreados = 0;

        foreach ($estados as $estado => $cantidad) {
            for ($i = 0; $i < $cantidad; $i++) {
                try {
                    $prestamo = $this->crearPrestamo(
                        $estado,
                        $usuario,
                        $departamento,
                        $institucion,
                        $responsable,
                        $activosDisponibles,
                        $componentesDisponibles,
                        $solicitudesDisponibles,
                        $estatusDisponible,
                        $estatusPrestado
                    );

                    if ($prestamo) {
                        $totalCreados++;
                        $this->command->info("   ✅ Préstamo #{$prestamo->codigo} [{$estado}]");
                    }
                } catch (\Throwable $e) {
                    $this->command->error("   ❌ Error al crear préstamo [{$estado}]: " . $e->getMessage());
                    Log::error('Error en PrestamoDemoSeeder: ' . $e->getMessage(), [
                        'estado' => $estado,
                        'trace'  => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        // ============================================================
        // 4. RESUMEN
        // ============================================================
        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('✅ SEEDER DE PRÉSTAMOS COMPLETADO');
        $this->command->info('========================================');
        $this->command->info("   • Préstamos creados: {$totalCreados}");
        $this->command->info("   • Total en sistema: " . Prestamo::count());
        $this->command->newLine();

        $this->command->table(
            ['Estado', 'Cantidad'],
            Prestamo::selectRaw('estado, count(*) as total')
                ->groupBy('estado')
                ->orderBy('estado')
                ->get()
                ->map(fn($p) => [ucfirst($p->estado), $p->total])
                ->toArray()
        );
    }

    // ============================================================
    // CREAR PRÉSTAMO
    // ============================================================
    private function crearPrestamo(
        string $estado,
        Usuario $usuario,
        ?Departamento $departamento,
        ?Institucion $institucion,
        Responsable $responsable,
        $activosDisponibles,
        $componentesDisponibles,
        $solicitudesDisponibles,
        $estatusDisponible,
        $estatusPrestado
    ): ?Prestamo {
        DB::beginTransaction();

        try {
            // ---- Fechas según el estado ----
            $fechaPrestamo = Carbon::now()->subDays(rand(1, 30));

            switch ($estado) {
                case 'pendiente':
                case 'aprobado':
                case 'cancelado':
                case 'rechazado':
                    $fechaDevolucionEsperada = $fechaPrestamo->copy()->addDays(rand(7, 20));
                    $fechaDevolucionReal = null;
                    break;
                case 'entregado':
                case 'extendido':
                    $fechaDevolucionEsperada = Carbon::now()->addDays(rand(3, 15));
                    $fechaDevolucionReal = null;
                    break;
                case 'devuelto':
                    $fechaDevolucionEsperada = $fechaPrestamo->copy()->addDays(rand(5, 15));
                    $fechaDevolucionReal = $fechaDevolucionEsperada->copy()->addDays(rand(0, 3));
                    break;
                default:
                    $fechaDevolucionEsperada = $fechaPrestamo->copy()->addDays(7);
                    $fechaDevolucionReal = null;
            }

            // ---- Tipo de préstamo ----
            $tipoPrestamo = ['equipo', 'componente', 'mixto'][array_rand(['equipo', 'componente', 'mixto'])];

            // ---- Asignar solicitud (solo para algunos) ----
            $solicitudId = null;
            if ($solicitudesDisponibles->isNotEmpty() && rand(0, 1)) {
                $solicitud = $solicitudesDisponibles->random();
                $solicitudId = $solicitud->id;
            }

            // ---- ✅ GENERAR CÓDIGO ÚNICO USANDO CONTADOR EN MEMORIA ----
            // (Evita el problema de aislamiento de transacciones)
            $codigoUnico = $this->generarCodigoPrestamoSeguro();

            // ---- Crear el préstamo ----
            $prestamo = Prestamo::create([
                'codigo' => $codigoUnico,
                'tipo_prestamo' => $tipoPrestamo,
                'estado' => $estado,
                'departamento_id' => $departamento?->id,
                'institucion_id' => $institucion?->id,
                'responsable_receptor_id' => $responsable->id,
                'responsable_emisor_id' => $responsable->id,
                'usuario_registra_id' => $usuario->id,
                'fecha_prestamo' => $fechaPrestamo,
                'fecha_devolucion_esperada' => $fechaDevolucionEsperada,
                'fecha_devolucion_real' => $fechaDevolucionReal,
                'observaciones' => 'Préstamo generado por el seeder de demostración.',
                'condiciones' => 'Devolver en las mismas condiciones en que fue entregado.',
                'solicitud_id' => $solicitudId,
                'tiene_extension' => $estado === 'extendido',
                'total_extensiones' => $estado === 'extendido' ? rand(1, 2) : 0,
            ]);

            // ---- Agregar detalles según el tipo ----
            $detallesCreados = 0;

            if (in_array($tipoPrestamo, ['equipo', 'mixto']) && $activosDisponibles->isNotEmpty()) {
                $activo = $activosDisponibles->random();
                $this->crearDetallePrestamo($prestamo, $activo, 'activo', $estado, $estatusDisponible, $estatusPrestado);
                $detallesCreados++;
            }

            if (in_array($tipoPrestamo, ['componente', 'mixto']) && $componentesDisponibles->isNotEmpty()) {
                $cantidadComponentes = rand(1, 2);
                $usados = [];

                for ($j = 0; $j < $cantidadComponentes; $j++) {
                    $componente = $componentesDisponibles->random();
                    if (in_array($componente->id, $usados)) continue;
                    $usados[] = $componente->id;

                    $this->crearDetallePrestamo($prestamo, $componente, 'componente', $estado, $estatusDisponible, $estatusPrestado);
                    $detallesCreados++;
                }
            }

            if ($detallesCreados === 0) {
                DB::rollBack();
                return null;
            }

            // ---- Crear extensión si aplica ----
            if ($estado === 'extendido') {
                PrestamoExtension::create([
                    'prestamo_id' => $prestamo->id,
                    'aprobado_por' => $usuario->id,
                    'tipo' => 'completa',
                    'fecha_anterior' => $fechaPrestamo->copy()->addDays(7),
                    'fecha_nueva' => $fechaDevolucionEsperada,
                    'motivo' => 'Extensión de plazo por necesidad del departamento.',
                ]);
            }

            DB::commit();
            return $prestamo;

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * ✅ Genera un código único usando un contador en memoria.
     *
     * NO consulta la BD en cada iteración. Esto evita el problema de
     * aislamiento de transacciones donde una transacción abierta no ve
     * los INSERT de otra transacción paralela (o anterior no confirmada).
     *
     * Solo la primera vez consulta la BD para saber por dónde empezar,
     * pero después solo incrementa en memoria.
     */
    private function generarCodigoPrestamoSeguro(): string
    {
        $anio = date('Y');

        // Solo la primera vez consultamos la BD para saber el último número
        if ($this->contadorCodigo === 0) {
            $ultimo = Prestamo::whereYear('created_at', $anio)
                ->orderBy('id', 'desc')
                ->first();

            if ($ultimo && preg_match('/PRES-\d{4}-(\d+)/', $ultimo->codigo, $matches)) {
                $this->contadorCodigo = intval($matches[1]);
            }
        }

        // Incrementar en memoria (sin tocar la BD)
        $this->contadorCodigo++;

        return 'PRES-' . $anio . '-' . str_pad($this->contadorCodigo, 4, '0', STR_PAD_LEFT);
    }

    // ============================================================
    // CREAR DETALLE Y ACTUALIZAR ESTADO DEL ITEM
    // ============================================================
    private function crearDetallePrestamo(
        Prestamo $prestamo,
        $prestable,
        string $tipo,
        string $estadoPrestamo,
        $estatusDisponible,
        $estatusPrestado
    ): void {
        // Determinar estados de entrega y devolución según el estado del préstamo
        $estadoEntrega = 'Pendiente de entrega';
        $estadoDevolucion = null;

        switch ($estadoPrestamo) {
            case 'entregado':
            case 'extendido':
                $estadoEntrega = 'Entregado en buen estado';
                break;
            case 'devuelto':
                $estadoEntrega = 'Entregado en buen estado';
                $estadoDevolucion = 'Devuelto en buen estado';
                break;
            case 'cancelado':
            case 'rechazado':
                $estadoEntrega = 'No entregado';
                break;
            case 'aprobado':
                $estadoEntrega = 'Aprobado - pendiente de entrega';
                break;
            case 'pendiente':
                $estadoEntrega = 'Reservado';
                break;
        }

        // Crear el detalle
        PrestamoDetalle::create([
            'prestamo_id' => $prestamo->id,
            'prestable_type' => $tipo === 'activo' ? Activo::class : Componente::class,
            'prestable_id' => $prestable->id,
            'cantidad' => 1,
            'estado_entrega' => $estadoEntrega,
            'estado_devolucion' => $estadoDevolucion,
            'observaciones' => 'Item de demostración',
        ]);

        // Actualizar estado del item según el estado del préstamo
        if ($prestable instanceof Activo) {
            switch ($estadoPrestamo) {
                case 'pendiente':
                    // Reservar
                    $prestable->update(['reservado_en_prestamo_id' => $prestamo->id]);
                    break;

                case 'aprobado':
                case 'entregado':
                case 'extendido':
                    // Marcar como prestado
                    $prestable->update([
                        'id_estatus' => $estatusPrestado->id,
                        'reservado_en_prestamo_id' => null,
                    ]);
                    break;

                case 'devuelto':
                case 'cancelado':
                case 'rechazado':
                    // Marcar como disponible
                    $prestable->update([
                        'id_estatus' => $estatusDisponible->id,
                        'reservado_en_prestamo_id' => null,
                    ]);
                    break;
            }
        } elseif ($prestable instanceof Componente) {
            switch ($estadoPrestamo) {
                case 'pendiente':
                    $prestable->update(['reservado_en_prestamo_id' => $prestamo->id]);
                    break;

                case 'aprobado':
                case 'entregado':
                case 'extendido':
                    $prestable->update([
                        'estado' => 'prestado',
                        'reservado_en_prestamo_id' => null,
                    ]);
                    break;

                case 'devuelto':
                case 'cancelado':
                case 'rechazado':
                    $prestable->update([
                        'estado' => 'en_bodega',
                        'reservado_en_prestamo_id' => null,
                    ]);
                    break;
            }
        }
    }
}