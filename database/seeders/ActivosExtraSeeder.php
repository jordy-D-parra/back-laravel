<?php

// database/seeders/ActivosExtraSeeder.php

namespace Database\Seeders;

use App\Models\Activo;
use App\Models\Componente;
use App\Models\Estatus;
use App\Models\Institucion;
use App\Models\Responsable;
use App\Models\Departamento;
use App\Models\Modelo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivosExtraSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creando activos extra con componentes...');

        // ============================================================
        // 1. VALIDAR DATOS BASE
        // ============================================================
        $estatusDisponible = Estatus::where('descripcion', 'Disponible')->first();
        $estatusPrestado   = Estatus::where('descripcion', 'Prestado')->first();
        $estatusReparacion = Estatus::where('descripcion', 'En reparación')->first();
        $estatusBodega     = Estatus::where('descripcion', 'En bodega')->first()
            ?? $estatusDisponible;

        if (!$estatusDisponible) {
            $this->command->error('❌ No existe el estatus "Disponible". Ejecuta EstatusSeeder primero.');
            return;
        }

        // Estatus disponibles para rotar
        $estatusPool = array_filter([
            $estatusDisponible,
            $estatusPrestado,
            $estatusReparacion,
            $estatusBodega,
        ]);

        $instituciones = Institucion::where('activo', true)->get();

        if ($instituciones->isEmpty()) {
            $this->command->error('❌ No hay instituciones activas. Ejecuta EntidadesSeeder primero.');
            return;
        }

        $modelos = Modelo::with(['marca', 'categoria'])
            ->where('activo', true)
            ->get();

        if ($modelos->isEmpty()) {
            $this->command->error('❌ No hay modelos activos. Ejecuta EquiposDemoSeeder primero.');
            return;
        }

        $this->command->info("✅ Datos base encontrados:");
        $this->command->info("   - Instituciones: {$instituciones->count()}");
        $this->command->info("   - Modelos: {$modelos->count()}");
        $this->command->info("   - Estatus disponibles: " . count($estatusPool));

        // ============================================================
        // 2. CONFIGURACIÓN
        // ============================================================
        $cantidadActivos = 50; // Cuántos activos crear

        $prefijosSerial = [
            'DEL' => ['Latitude', 'OptiPlex', 'XPS', 'Inspiron', 'Vostro'],
            'HP'  => ['EliteBook', 'ProBook', 'Pavilion', 'EliteDesk', 'ProDesk'],
            'LEN' => ['ThinkPad', 'ThinkCentre', 'IdeaPad', 'Legion'],
            'SAM' => ['Galaxy', 'Odyssey', 'T55'],
            'EPS' => ['EcoTank', 'WorkForce'],
            'CAN' => ['PIXMA'],
            'BRO' => ['DCP'],
            'APP' => ['MacBook', 'iPad', 'iMac'],
            'ACE' => ['Aspire', 'Nitro'],
            'ASU' => ['VivoBook', 'ZenBook'],
        ];

        $ubicaciones = [
            'Oficina 101', 'Oficina 102', 'Oficina 201', 'Oficina 202',
            'Oficina 301', 'Oficina 302', 'Sala de Servidores',
            'Laboratorio A', 'Laboratorio B', 'Laboratorio C',
            'Bodega Central', 'Recepción', 'Sala de Reuniones',
            'Depósito Piso 1', 'Depósito Piso 2', 'Archivo',
        ];

        $tiposComponentes = [
            'RAM'         => ['Kingston', 'Crucial', 'Corsair', 'ADATA'],
            'Disco SSD'   => ['Kingston', 'Samsung', 'Crucial', 'WD'],
            'Disco Duro'  => ['Seagate', 'WD', 'Toshiba'],
            'Batería'     => ['Dell', 'HP', 'Lenovo', 'Genérica'],
            'Cargador'    => ['Dell', 'HP', 'Lenovo', 'Genérico'],
            'Mouse'       => ['Logitech', 'Microsoft', 'Genérico'],
            'Teclado'     => ['Logitech', 'Microsoft', 'Genérico'],
            'Monitor'     => ['Dell', 'HP', 'Samsung', 'LG'],
            'Webcam'      => ['Logitech', 'Microsoft'],
            'Adaptador'   => ['Genérico', 'UGREEN'],
        ];

        $creados = 0;
        $errores = 0;
        $componentesCreados = 0;

        // ============================================================
        // 3. CREAR ACTIVOS
        // ============================================================
        $this->command->info("📦 Creando {$cantidadActivos} activos...");

        for ($i = 0; $i < $cantidadActivos; $i++) {
            try {
                DB::beginTransaction();

                // ---- Elegir institución y su responsable ----
                $institucion = $instituciones->random();

                // Elegir departamento opcional (30% de los casos)
                $departamento = null;
                if (rand(0, 100) < 30) {
                    $departamento = Departamento::where('institucion_id', $institucion->id)
                        ->where('activo', true)
                        ->inRandomOrder()
                        ->first();
                }

                // Buscar responsable (del depto o de la institución)
                $responsable = null;
                if ($departamento) {
                    $responsable = Responsable::where('departamento_id', $departamento->id)
                        ->where('activo', true)
                        ->first();
                }
                if (!$responsable) {
                    $responsable = Responsable::where('institucion_id', $institucion->id)
                        ->where('activo', true)
                        ->first();
                }
                if (!$responsable) {
                    // Fallback: cualquier responsable activo
                    $responsable = Responsable::where('activo', true)->first();
                }
                if (!$responsable) {
                    throw new \Exception("No hay responsable para institución {$institucion->nombre}");
                }

                // ---- Elegir modelo ----
                $modelo = $modelos->random();
                $marcaNombre = $modelo->marca?->nombre ?? 'GEN';
                $prefijo = strtoupper(substr($marcaNombre, 0, 3));

                // Si el prefijo no está en el pool, usar uno genérico
                if (!isset($prefijosSerial[$prefijo])) {
                    $prefijo = 'GEN';
                }

                // ---- Generar serial único ----
                $serial = $this->generarSerialUnico($prefijo);

                // ---- Elegir estatus (mayoría disponible) ----
                $estatus = $this->elegirEstatus($estatusPool, $estatusDisponible);

                // ---- Crear activo ----
                $activo = Activo::create([
                    'serial'          => $serial,
                    'modelo_id'       => $modelo->id,
                    'id_estatus'      => $estatus->id,
                    'institucion_id'  => $institucion->id,
                    'departamento_id' => $departamento?->id,
                    'responsable_id'  => $responsable->id,
                    'ubicacion'       => $ubicaciones[array_rand($ubicaciones)],
                    'fecha_adquisicion'   => now()->subDays(rand(30, 1500)),
                    'fecha_fin_garantia'  => now()->addDays(rand(-200, 900)),
                    'vida_util_anos'      => rand(3, 6),
                    'observaciones'       => 'Activo generado por ActivosExtraSeeder',
                ]);

                $creados++;

                // ---- Crear entre 1 y 4 componentes ----
                $cantidadComp = rand(1, 4);
                $tiposElegidos = array_rand($tiposComponentes, min($cantidadComp, count($tiposComponentes)));

                if (!is_array($tiposElegidos)) {
                    $tiposElegidos = [$tiposElegidos];
                }

                foreach ($tiposElegidos as $tipo) {
                    $marcasDelTipo = $tiposComponentes[$tipo];
                    $marcaComp = $marcasDelTipo[array_rand($marcasDelTipo)];

                    Componente::create([
                        'tipo'           => $tipo,
                        'marca'          => $marcaComp,
                        'modelo'         => 'Genérico',
                        'serial'         => $this->generarSerialComponente($tipo),
                        'capacidad'      => $this->getCapacidadAleatoria($tipo),
                        'estado'         => 'instalado',
                        'activo_id'      => $activo->id,
                        'institucion_id' => $institucion->id,
                        'departamento_id'=> $departamento?->id,
                        'responsable_id' => $responsable->id,
                        'ubicacion'      => $activo->ubicacion,
                        'fecha_instalacion' => now(),
                    ]);

                    $componentesCreados++;
                }

                DB::commit();

                if (($i + 1) % 10 === 0) {
                    $this->command->info("   → {$creados} activos creados...");
                }

            } catch (\Throwable $e) {
                DB::rollBack();
                $errores++;
                Log::error('Error al crear activo extra: ' . $e->getMessage(), [
                    'iteracion' => $i,
                ]);
            }
        }

        // ============================================================
        // 4. CREAR ALGUNOS COMPONENTES SUELTOS (en bodega, sin activo)
        // ============================================================
        $componentesSueltos = 25;
        $this->command->info("📦 Creando {$componentesSueltos} componentes sueltos (en bodega)...");

        $institucionDefault = $instituciones->first();
        $responsableDefault = Responsable::where('institucion_id', $institucionDefault->id)
            ->where('activo', true)
            ->first()
            ?? Responsable::where('activo', true)->first();

        for ($j = 0; $j < $componentesSueltos; $j++) {
            try {
                $tiposKeys = array_keys($tiposComponentes);
                $tipo = $tiposKeys[array_rand($tiposKeys)];
                $marcasDelTipo = $tiposComponentes[$tipo];
                $marcaComp = $marcasDelTipo[array_rand($marcasDelTipo)];

                Componente::create([
                    'tipo'           => $tipo,
                    'marca'          => $marcaComp,
                    'modelo'         => 'Genérico',
                    'serial'         => $this->generarSerialComponente($tipo),
                    'capacidad'      => $this->getCapacidadAleatoria($tipo),
                    'estado'         => 'en_bodega',
                    'activo_id'      => null,
                    'institucion_id' => $institucionDefault->id,
                    'responsable_id' => $responsableDefault->id,
                    'ubicacion'      => 'Bodega Central',
                ]);

                $componentesCreados++;
            } catch (\Throwable $e) {
                Log::error('Error al crear componente suelto: ' . $e->getMessage());
            }
        }

        // ============================================================
        // 5. RESUMEN
        // ============================================================
        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('✅ SEEDER COMPLETADO');
        $this->command->info('========================================');
        $this->command->table(
            ['Concepto', 'Cantidad'],
            [
                ['Activos creados', $creados],
                ['Componentes creados', $componentesCreados],
                ['Errores', $errores],
                ['Total activos en BD', Activo::count()],
                ['Total componentes en BD', Componente::count()],
            ]
        );
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Genera un serial único para activos.
     */
    private function generarSerialUnico(string $prefijo): string
    {
        $intentos = 0;
        do {
            $serial = strtoupper($prefijo) . '-' . rand(1000, 9999) . '-' . chr(65 + rand(0, 25));
            $intentos++;
        } while (Activo::where('serial', $serial)->exists() && $intentos < 50);

        return $serial;
    }

    /**
     * Genera un serial único para componentes.
     */
    private function generarSerialComponente(string $tipo): string
    {
        $prefijo = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $tipo), 0, 3));
        $intentos = 0;
        do {
            $serial = $prefijo . '-' . rand(10000, 99999);
            $intentos++;
        } while (Componente::where('serial', $serial)->exists() && $intentos < 50);

        return $serial;
    }

    /**
     * Devuelve una capacidad aleatoria según el tipo de componente.
     */
    private function getCapacidadAleatoria(string $tipo): ?string
    {
        return match ($tipo) {
            'RAM'        => ['4GB', '8GB', '16GB', '32GB'][array_rand(['4GB', '8GB', '16GB', '32GB'])],
            'Disco SSD'  => ['240GB', '480GB', '512GB', '1TB'][array_rand(['240GB', '480GB', '512GB', '1TB'])],
            'Disco Duro' => ['500GB', '1TB', '2TB'][array_rand(['500GB', '1TB', '2TB'])],
            'Batería'    => ['41Wh', '45Wh', '52Wh'][array_rand(['41Wh', '45Wh', '52Wh'])],
            'Cargador'   => ['45W', '65W', '90W', '120W'][array_rand(['45W', '65W', '90W', '120W'])],
            'Monitor'    => ['21.5"', '24"', '27"', '32"'][array_rand(['21.5"', '24"', '27"', '32"'])],
            'Webcam'     => ['720p', '1080p'][array_rand(['720p', '1080p'])],
            default      => null,
        };
    }

    /**
     * Elige un estatus al azar, dando más probabilidad a "Disponible".
     */
    private function elegirEstatus($estatusPool, $estatusDisponible)
    {
        $r = rand(1, 100);

        if ($r <= 60) {
            return $estatusDisponible; // 60% disponible
        }

        // Elegir cualquier otro del pool
        $otros = collect($estatusPool)
            ->filter(fn($e) => $e->id !== $estatusDisponible->id)
            ->values();

        if ($otros->isEmpty()) {
            return $estatusDisponible;
        }

        return $otros->random();
    }
}