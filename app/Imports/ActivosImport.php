<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Activo;
use App\Services\Importacion\ActivoResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Clase orquestadora de la importación de activos desde Excel.
 *
 * Flujo:
 *   1. Se instancia con: ruta del archivo, mapeo de columnas, decisiones del usuario.
 *   2. Se llama a ->procesar().
 *   3. Devuelve un objeto ResultadoImportacion con exitos, errores, advertencias y tiempo.
 *
 * Características:
 *   - Transacción por fila (una falla no rompe el resto).
 *   - Detección de seriales duplicados (dentro del archivo y en BD).
 *   - Modo "solo insertar" (los seriales existentes se rechazan).
 *   - Acumula errores con contexto (fila, columna, valor, motivo).
 *   - Registra advertencias cuando el activo queda incompleto.
 */
class ActivosImport
{
    // ============================================================
    // CONSTANTES
    // ============================================================

    private const MAX_FILAS_PROCESAR = 50000;      // límite de seguridad
    private const LOG_CADA_N_FILAS   = 100;         // log cada N filas para progreso

    // ============================================================
    // PROPIEDADES
    // ============================================================

    private string $rutaArchivo;

    /**
     * Mapeo: nombre_columna_excel => [
     *     'campo'         => 'serial',
     *     'confianza'     => 'alta',
     *     'columna_excel' => 'N° Serie',
     *     'indice'        => 1,
     * ]
     */
    private array $mapeo;

    /**
     * Decisiones del usuario sobre entidades no reconocidas.
     */
    private array $decisiones;

    /**
     * Modo de importación:
     *   - 'solo_insertar'        → rechaza seriales que ya existen en BD
     *   - 'crear_entidades'      → (default) permite crear marca/categoría/modelo
     */
    private string $modoImportacion;

    /**
     * Resolver de entidades.
     */
    private ActivoResolver $resolver;

    // ============================================================
    // RESULTADOS ACUMULADOS
    // ============================================================

    /** @var array<int, array{fila: int, columna: string, valor: mixed, motivo: string}> */
    private array $errores = [];

    /** @var array<int, array{fila: int, serial: string, motivo: string}> */
    private array $advertencias = [];

    /** @var array<int, array{fila: int, serial: string, activo_id: int}> */
    private array $exitos = [];

    /**
     * Seriales procesados en este archivo (para detectar duplicados internos).
     * @var array<string, int>  serial => fila donde apareció primero
     */
    private array $serialesEnArchivo = [];

    // ============================================================
    // CONSTRUCTOR
    // ============================================================

    public function __construct(
        string $rutaArchivo,
        array $mapeo,
        array $decisiones = [],
        string $modoImportacion = 'crear_entidades'
    ) {
        $this->rutaArchivo = $rutaArchivo;
        $this->mapeo = $mapeo;
        $this->decisiones = $decisiones;
        $this->modoImportacion = $modoImportacion;

        $this->resolver = (new ActivoResolver())
            ->setDecisiones($decisiones)
            ->setCrearEntidadesPorDefecto(true);
    }

    // ============================================================
    // PUNTO DE ENTRADA
    // ============================================================

    /**
     * Procesa el archivo completo y devuelve el resultado.
     *
     * @return array{
     *     exitos: int,
     *     errores: array,
     *     advertencias: array,
     *     total_filas: int,
     *     tiempo_segundos: float,
     *     seriales_importados: array<int, string>
     * }
     */
    public function procesar(): array
    {
        $inicio = microtime(true);

        try {
            $hoja = $this->abrirPrimeraHoja();
        } catch (\Throwable $e) {
            Log::error('[ActivosImport] Error al abrir el archivo: ' . $e->getMessage());

            return [
                'exitos'              => 0,
                'errores'             => [[
                    'fila'    => 0,
                    'columna' => 'archivo',
                    'valor'   => $this->rutaArchivo,
                    'motivo'  => 'No se pudo abrir el archivo Excel: ' . $e->getMessage(),
                ]],
                'advertencias'        => [],
                'total_filas'         => 0,
                'tiempo_segundos'     => round(microtime(true) - $inicio, 2),
                'seriales_importados' => [],
            ];
        }

        // Validar que el mapeo tenga al menos el campo "serial"
        if (!$this->mapeoTieneSerial()) {
            return [
                'exitos'              => 0,
                'errores'             => [[
                    'fila'    => 0,
                    'columna' => 'mapeo',
                    'valor'   => null,
                    'motivo'  => 'El mapeo no incluye la columna "serial", que es obligatoria.',
                ]],
                'advertencias'        => [],
                'total_filas'         => 0,
                'tiempo_segundos'     => round(microtime(true) - $inicio, 2),
                'seriales_importados' => [],
            ];
        }

        $filaMax = $hoja->getHighestDataRow();
        $totalFilas = max(0, $filaMax - 1);

        if ($totalFilas > self::MAX_FILAS_PROCESAR) {
            return [
                'exitos'              => 0,
                'errores'             => [[
                    'fila'    => 0,
                    'columna' => 'archivo',
                    'valor'   => $totalFilas,
                    'motivo'  => 'El archivo excede el límite de ' . self::MAX_FILAS_PROCESAR . ' filas.',
                ]],
                'advertencias'        => [],
                'total_filas'         => $totalFilas,
                'tiempo_segundos'     => round(microtime(true) - $inicio, 2),
                'seriales_importados' => [],
            ];
        }

        // Índices de columnas por campo (para leer rápido)
        $indicesPorCampo = $this->indicesPorCampo();

        // ============================================================
        // RECORRER FILAS
        // ============================================================
        for ($fila = 2; $fila <= $filaMax; $fila++) {
            // Log de progreso cada N filas
            if (($fila - 1) % self::LOG_CADA_N_FILAS === 0 && $fila > 2) {
                Log::info("[ActivosImport] Progreso: fila {$fila} de {$filaMax}");
            }

            $this->procesarFila($hoja, $fila, $indicesPorCampo);
        }

        $tiempoTotal = round(microtime(true) - $inicio, 2);

        Log::info('[ActivosImport] Importación finalizada', [
            'exitos'      => count($this->exitos),
            'errores'     => count($this->errores),
            'advertencias' => count($this->advertencias),
            'tiempo'      => $tiempoTotal . 's',
        ]);

        return [
            'exitos'              => count($this->exitos),
            'errores'             => $this->errores,
            'advertencias'        => $this->advertencias,
            'total_filas'         => $totalFilas,
            'tiempo_segundos'     => $tiempoTotal,
            'seriales_importados' => array_column($this->exitos, 'serial'),
        ];
    }

    // ============================================================
    // PROCESAMIENTO DE UNA FILA
    // ============================================================

    /**
     * Procesa una fila individual. Acumula resultado en $this->exitos/errores/advertencias.
     */
    private function procesarFila(Worksheet $hoja, int $fila, array $indicesPorCampo): void
    {
        // ------------------------------------------------------------
        // 1. Extraer los datos de la fila según el mapeo
        // ------------------------------------------------------------
        $datosFila = $this->extraerFila($hoja, $fila, $indicesPorCampo);

        // ------------------------------------------------------------
        // 2. Saltar filas vacías (todas las celdas del mapeo vacías)
        // ------------------------------------------------------------
        if ($this->filaVacia($datosFila)) {
            return;
        }

        // ------------------------------------------------------------
        // 3. Validar serial
        // ------------------------------------------------------------
        $serial = $this->extraerSerial($datosFila);

        if ($serial === '') {
            $this->agregarError(
                $fila,
                'serial',
                $datosFila['serial'] ?? null,
                'El serial es obligatorio y no puede estar vacío.'
            );
            return;
        }

        // ------------------------------------------------------------
        // 4. Detectar duplicado DENTRO del archivo
        // ------------------------------------------------------------
        if (isset($this->serialesEnArchivo[$serial])) {
            $filaOriginal = $this->serialesEnArchivo[$serial];
            $this->agregarError(
                $fila,
                'serial',
                $serial,
                "Serial duplicado dentro del archivo. Ya apareció en la fila {$filaOriginal}."
            );
            return;
        }

        // ------------------------------------------------------------
        // 5. Detectar duplicado EN LA BD
        // ------------------------------------------------------------
        if (Activo::where('serial', $serial)->exists()) {
            $this->agregarError(
                $fila,
                'serial',
                $serial,
                "El serial '{$serial}' ya existe en el sistema."
            );
            return;
        }

        // ------------------------------------------------------------
        // 6. Resolver entidades y validar la fila
        // ------------------------------------------------------------
        try {
            $resuelto = $this->resolver->resolverFila($datosFila);
        } catch (\RuntimeException $e) {
            $this->agregarError($fila, 'serial', $serial, $e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->agregarError($fila, 'desconocido', $serial, 'Error inesperado: ' . $e->getMessage());
            Log::error('[ActivosImport] Error inesperado en fila ' . $fila, [
                'mensaje' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return;
        }

        // ------------------------------------------------------------
        // 7. Insertar el activo en una transacción aislada
        // ------------------------------------------------------------
        try {
            $activo = DB::transaction(function () use ($resuelto, $fila) {
                return Activo::create([
                    'serial'                    => $resuelto['serial'],
                    'modelo_id'                 => $resuelto['modelo_id'],
                    'id_estatus'                => $resuelto['id_estatus'],
                    'institucion_id'            => $resuelto['institucion_id'],
                    'departamento_id'           => $resuelto['departamento_id'],
                    'responsable_id'            => $resuelto['responsable_id'],
                    'ubicacion'                 => $resuelto['ubicacion'],
                    'fecha_adquisicion'         => $resuelto['fecha_adquisicion'],
                    'fecha_fin_garantia'        => $resuelto['fecha_fin_garantia'],
                    'vida_util_anos'            => $resuelto['vida_util_anos'],
                    'observaciones'             => $resuelto['observaciones'],
                    'datos_incompletos'         => $resuelto['datos_incompletos'],
                ]);
            });

            // --------------------------------------------------------
            // 8. Registrar éxito
            // --------------------------------------------------------
            $this->exitos[] = [
                'fila'       => $fila,
                'serial'     => $resuelto['serial'],
                'activo_id'  => $activo->id,
            ];

            $this->serialesEnArchivo[$serial] = $fila;

            // --------------------------------------------------------
            // 9. Advertencia si el activo quedó incompleto
            // --------------------------------------------------------
            if (!empty($resuelto['datos_incompletos'])) {
                $campos = implode(', ', $resuelto['datos_incompletos']['campos_faltantes']);

                $this->advertencias[] = [
                    'fila'   => $fila,
                    'serial' => $resuelto['serial'],
                    'motivo' => "Activo importado con campos incompletos: {$campos}",
                ];
            }

        } catch (\Illuminate\Database\QueryException $e) {
            // Error de BD (ej: FK rota, unique constraint, etc.)
            $this->agregarError(
                $fila,
                'bd',
                $serial,
                'Error al insertar en base de datos: ' . $this->mensajeCorto($e->getMessage())
            );

            Log::warning('[ActivosImport] QueryException en fila ' . $fila, [
                'serial'  => $serial,
                'mensaje' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            $this->agregarError(
                $fila,
                'desconocido',
                $serial,
                'Error inesperado al insertar: ' . $e->getMessage()
            );

            Log::error('[ActivosImport] Error al insertar fila ' . $fila, [
                'serial'  => $serial,
                'mensaje' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }

    // ============================================================
    // HELPERS DE EXTRACCIÓN
    // ============================================================

    /**
     * Lee el Excel y construye un array asociativo campo => valor.
     *
     * @return array<string, mixed>
     */
    private function extraerFila(Worksheet $hoja, int $fila, array $indicesPorCampo): array
    {
        $datos = [];

        foreach ($indicesPorCampo as $campo => $indiceColumna) {
            $valor = $hoja->getCell([$indiceColumna, $fila])->getValue();
            $datos[$campo] = $valor;
        }

        return $datos;
    }

    /**
     * Determina si una fila está completamente vacía (ningún campo del mapeo
     * tiene valor útil).
     */
    private function filaVacia(array $datosFila): bool
    {
        foreach ($datosFila as $valor) {
            if ($valor === null) {
                continue;
            }

            if (is_string($valor) && trim($valor) === '') {
                continue;
            }

            return false;
        }

        return true;
    }

    /**
     * Extrae y normaliza el serial de la fila.
     */
    private function extraerSerial(array $datosFila): string
    {
        $valor = $datosFila['serial'] ?? null;

        if ($valor === null) {
            return '';
        }

        $serial = trim((string) $valor);
        $serial = mb_strtoupper($serial, 'UTF-8');
        $serial = preg_replace('/\s+/', ' ', $serial) ?? $serial;

        return $serial;
    }

    // ============================================================
    // HELPERS DE MAPEO
    // ============================================================

    /**
     * Convierte el mapeo (nombre_columna => info) a un array
     * campo => índice_columna.
     *
     * @return array<string, int>
     */
    private function indicesPorCampo(): array
    {
        $resultado = [];

        foreach ($this->mapeo as $entrada) {
            if (!is_array($entrada)) {
                continue;
            }

            $campo = $entrada['campo'] ?? null;
            $indice = $entrada['indice'] ?? null;

            if ($campo && is_int($indice)) {
                $resultado[$campo] = $indice;
            }
        }

        return $resultado;
    }

    /**
     * Verifica que el mapeo contenga el campo "serial".
     */
    private function mapeoTieneSerial(): bool
    {
        foreach ($this->mapeo as $entrada) {
            if (is_array($entrada) && ($entrada['campo'] ?? null) === 'serial') {
                return true;
            }
        }

        return false;
    }

    // ============================================================
    // HELPERS DE ARCHIVO
    // ============================================================

    /**
     * Abre la primera hoja del archivo Excel en modo solo lectura.
     */
    private function abrirPrimeraHoja(): Worksheet
    {
        $reader = IOFactory::createReaderForFile($this->rutaArchivo);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($this->rutaArchivo);

        return $spreadsheet->getSheet(0);
    }

    // ============================================================
    // HELPERS DE REGISTRO
    // ============================================================

    private function agregarError(int $fila, string $columna, mixed $valor, string $motivo): void
    {
        $this->errores[] = [
            'fila'    => $fila,
            'columna' => $columna,
            'valor'   => $valor,
            'motivo'  => $motivo,
        ];
    }

    /**
     * Recorta un mensaje de error de BD para que no sature el Excel de errores.
     */
    private function mensajeCorto(string $mensaje, int $max = 200): string
    {
        $mensaje = preg_replace('/\s+/', ' ', $mensaje) ?? $mensaje;

        if (mb_strlen($mensaje) <= $max) {
            return $mensaje;
        }

        return mb_substr($mensaje, 0, $max - 3) . '...';
    }

    // ============================================================
    // GETTERS PÚBLICOS (para debug o para el controlador)
    // ============================================================

    /** @return array<int, array> */
    public function getErrores(): array
    {
        return $this->errores;
    }

    /** @return array<int, array> */
    public function getAdvertencias(): array
    {
        return $this->advertencias;
    }

    /** @return array<int, array> */
    public function getExitos(): array
    {
        return $this->exitos;
    }

    /** @return array<string, int> */
    public function getSerialesEnArchivo(): array
    {
        return $this->serialesEnArchivo;
    }
}