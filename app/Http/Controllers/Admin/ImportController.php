<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\ErroresImportExport;
use App\Exports\PlantillaActivosExport;
use App\Imports\ActivosImport;
use App\Services\Importacion\ExcelColumnMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Controlador del módulo de importación de activos desde Excel.
 *
 * Endpoints:
 *   GET  /admin/importar                       → vista principal
 *   GET  /admin/importar/plantilla             → descarga plantilla Excel
 *   POST /admin/importar/analizar              → sube Excel, detecta mapeo, devuelve JSON
 *   POST /admin/importar/procesar              → procesa con mapeo+decisiones, devuelve resultado
 *   GET  /admin/importar/errores/{session_id}  → descarga Excel de errores
 *   POST /admin/importar/cancelar/{session_id} → limpia archivo temporal
 */
class ImportController extends Controller
{
    // ============================================================
    // CONSTANTES
    // ============================================================

    private const PREFIJO_CACHE     = 'import_activos_';
    private const PREFIJO_SESION    = 'import_';
    private const TTL_CACHE_MINUTOS = 120;                 // 2 horas
    private const DIRECTORIO_TEMP   = 'imports';           // dentro de storage/app/private
    private const EXTENSIONES_EXCEL = ['xlsx', 'xls'];
    private const MAX_TAMANO_KB     = 10240;               // 10 MB

    // ============================================================
    // DEPENDENCIAS
    // ============================================================

    private ExcelColumnMapper $mapper;

    public function __construct(ExcelColumnMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    // ============================================================
    // VISTA PRINCIPAL
    // ============================================================

    /**
     * Muestra la vista principal del módulo de importación.
     */
    public function index()
    {
        if (!auth()->user()->hasPermission('crear-activo')) {
            abort(403, 'No tienes permiso para importar activos');
        }

        return view('admin.importar.index');
    }

    // ============================================================
    // DESCARGA DE PLANTILLA
    // ============================================================

    /**
     * Descarga la plantilla de Excel con las columnas exactas que espera el sistema.
     */
    public function descargarPlantilla()
    {
        if (!auth()->user()->hasPermission('crear-activo')) {
            abort(403, 'No tienes permiso para descargar la plantilla');
        }

        $export = new PlantillaActivosExport();

        return $export->download();
    }

    // ============================================================
    // ANALIZAR EXCEL
    // ============================================================

    /**
     * Sube un Excel, detecta el mapeo de columnas y las entidades no reconocidas,
     * y devuelve todo en JSON para que el frontend muestre el preview.
     */
    public function analizar(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-activo')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para importar activos',
            ], 403);
        }

        // ------------------------------------------------------------
        // 1. Validar el archivo
        // ------------------------------------------------------------
        $request->validate([
            'archivo' => [
                'required',
                'file',
                'mimes:' . implode(',', self::EXTENSIONES_EXCEL),
                'max:' . self::MAX_TAMANO_KB,
            ],
        ], [
            'archivo.required' => 'Debes seleccionar un archivo Excel.',
            'archivo.mimes'    => 'El archivo debe ser .xlsx o .xls.',
            'archivo.max'      => 'El archivo no puede superar los 10 MB.',
        ]);

        try {
            $archivo = $request->file('archivo');
            $sessionId = $this->generarSessionId();

            // ------------------------------------------------------------
            // 2. Guardar el archivo temporalmente
            // ------------------------------------------------------------
            $rutaRelativa = $this->guardarArchivoTemporal($archivo, $sessionId);
            $rutaAbsoluta = Storage::disk('local')->path($rutaRelativa);

            // ------------------------------------------------------------
            // 3. Detectar mapeo de columnas
            // ------------------------------------------------------------
            $mapeo = $this->mapper->detectarMapeo($rutaAbsoluta);

            // ------------------------------------------------------------
            // 4. Detectar entidades no reconocidas
            // ------------------------------------------------------------
            $entidadesNoReconocidas = $this->mapper->detectarEntidadesNoReconocidas(
                $rutaAbsoluta,
                $mapeo['mapeo']
            );

            // ------------------------------------------------------------
            // 5. Guardar metadata en caché (para el paso de "procesar")
            // ------------------------------------------------------------
            $metadata = [
                'session_id'    => $sessionId,
                'ruta_relativa' => $rutaRelativa,
                'ruta_absoluta' => $rutaAbsoluta,
                'nombre_original' => $archivo->getClientOriginalName(),
                'cabeceras'     => $mapeo['cabeceras'],
                'mapeo'         => $mapeo['mapeo'],
                'total_filas'   => $mapeo['total_filas'],
                'creado_por'    => auth()->id(),
                'creado_en'     => now()->toDateTimeString(),
            ];

            Cache::put(
                self::PREFIJO_CACHE . $sessionId,
                $metadata,
                now()->addMinutes(self::TTL_CACHE_MINUTOS)
            );

            // ------------------------------------------------------------
            // 6. Log
            // ------------------------------------------------------------
            Log::info('[ImportController] Excel analizado', [
                'session_id'  => $sessionId,
                'archivo'     => $archivo->getClientOriginalName(),
                'total_filas' => $mapeo['total_filas'],
                'user_id'     => auth()->id(),
            ]);

            // ------------------------------------------------------------
            // 7. Respuesta
            // ------------------------------------------------------------
            return response()->json([
                'success'    => true,
                'message'    => 'Archivo analizado correctamente.',
                'session_id' => $sessionId,
                'data' => [
                    'nombre_archivo'  => $archivo->getClientOriginalName(),
                    'total_filas'     => $mapeo['total_filas'],
                    'cabeceras'       => $mapeo['cabeceras'],
                    'mapeo'           => $this->formatearMapeoParaFrontend($mapeo['mapeo']),
                    'campos_criticos_faltantes' => $mapeo['campos_criticos_faltantes'],
                    'entidades_no_reconocidas'  => $entidadesNoReconocidas,
                    'campos_disponibles'        => $this->camposDisponiblesParaSelect(),
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            Log::error('[ImportController] Error en analizar: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al analizar el archivo: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // PROCESAR IMPORTACIÓN
    // ============================================================

    /**
     * Procesa la importación con el mapeo y las decisiones confirmadas por el usuario.
     */
    public function procesar(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-activo')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para importar activos',
            ], 403);
        }

        // ------------------------------------------------------------
        // 1. Validar payload
        // ------------------------------------------------------------
        $validated = $request->validate([
            'session_id'  => 'required|string|max:100',
            'mapeo'       => 'required|array|min:1',
            'decisiones'  => 'nullable|array',
            'modo'        => 'nullable|in:solo_insertar,crear_entidades',
        ], [
            'session_id.required' => 'Falta el identificador de sesión.',
            'mapeo.required'      => 'Falta el mapeo de columnas.',
        ]);

        $sessionId  = (string) $validated['session_id'];
        $mapeo      = $validated['mapeo'];
        $decisiones = $validated['decisiones'] ?? [];
        $modo       = $validated['modo'] ?? 'crear_entidades';

        // ------------------------------------------------------------
        // 2. Recuperar metadata del caché
        // ------------------------------------------------------------
        $cacheKey = self::PREFIJO_CACHE . $sessionId;
        $metadata = Cache::get($cacheKey);

        if ($metadata === null) {
            return response()->json([
                'success' => false,
                'message' => 'La sesión de importación expiró. Vuelve a subir el archivo.',
            ], 410);
        }

        // Validar que el usuario que procesa sea el mismo que subió el archivo
        if (($metadata['creado_por'] ?? null) !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para procesar esta importación.',
            ], 403);
        }

        // Validar que el archivo sigue existiendo
        $rutaAbsoluta = $metadata['ruta_absoluta'] ?? null;

        if ($rutaAbsoluta === null || !File::exists($rutaAbsoluta)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo temporal ya no está disponible. Vuelve a subirlo.',
            ], 410);
        }

        // ------------------------------------------------------------
        // 3. Normalizar el mapeo (el frontend puede enviarlo como array
        //    o como objeto con índices)
        // ------------------------------------------------------------
        $mapeoNormalizado = $this->normalizarMapeoEntrada($mapeo, $metadata);

        if (empty($mapeoNormalizado)) {
            return response()->json([
                'success' => false,
                'message' => 'El mapeo enviado está vacío o mal formado.',
            ], 422);
        }

        // Validar que el mapeo tenga al menos el campo "serial"
        $tieneSerial = false;
        foreach ($mapeoNormalizado as $entrada) {
            if (($entrada['campo'] ?? null) === 'serial') {
                $tieneSerial = true;
                break;
            }
        }

        if (!$tieneSerial) {
            return response()->json([
                'success' => false,
                'message' => 'El mapeo debe incluir la columna "serial" (es obligatoria).',
            ], 422);
        }

        // ------------------------------------------------------------
        // 4. Ejecutar la importación
        // ------------------------------------------------------------
        try {
            $import = new ActivosImport(
                $rutaAbsoluta,
                $mapeoNormalizado,
                $decisiones,
                $modo
            );

            $resultado = $import->procesar();

            // ------------------------------------------------------------
            // 5. Guardar resultado en caché para permitir descarga de errores
            // ------------------------------------------------------------
            if (!empty($resultado['errores'])) {
                Cache::put(
                    $cacheKey . '_errores',
                    [
                        'errores'         => $resultado['errores'],
                        'nombre_original' => $metadata['nombre_original'] ?? 'importacion.xlsx',
                        'cabeceras'       => $metadata['cabeceras'] ?? [],
                        'mapeo'           => $mapeoNormalizado,
                    ],
                    now()->addMinutes(self::TTL_CACHE_MINUTOS)
                );
            }

            // ------------------------------------------------------------
            // 6. Log
            // ------------------------------------------------------------
            Log::info('[ImportController] Importación procesada', [
                'session_id'  => $sessionId,
                'exitos'      => $resultado['exitos'],
                'errores'     => count($resultado['errores']),
                'advertencias' => count($resultado['advertencias']),
                'tiempo'      => $resultado['tiempo_segundos'],
                'user_id'     => auth()->id(),
            ]);

            // ------------------------------------------------------------
            // 7. Respuesta
            // ------------------------------------------------------------
            return response()->json([
                'success' => true,
                'message' => $this->construirMensajeResultado($resultado),
                'data'    => [
                    'session_id'          => $sessionId,
                    'exitos'              => $resultado['exitos'],
                    'total_filas'         => $resultado['total_filas'],
                    'tiempo_segundos'     => $resultado['tiempo_segundos'],
                    'errores'             => $resultado['errores'],
                    'advertencias'        => $resultado['advertencias'],
                    'total_errores'       => count($resultado['errores']),
                    'total_advertencias'  => count($resultado['advertencias']),
                    'url_errores'         => !empty($resultado['errores'])
                        ? route('admin.importar.errores', ['session_id' => $sessionId])
                        : null,
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('[ImportController] Error al procesar importación', [
                'session_id' => $sessionId,
                'mensaje'    => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la importación: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // DESCARGA DE ERRORES
    // ============================================================

    /**
     * Genera y descarga un Excel con las filas que fallaron durante la importación.
     */
    public function descargarErrores(string $sessionId)
    {
        if (!auth()->user()->hasPermission('crear-activo')) {
            abort(403, 'No tienes permiso para descargar el reporte de errores');
        }

        $cacheKey = self::PREFIJO_CACHE . $sessionId . '_errores';
        $metadata = Cache::get($cacheKey);

        if ($metadata === null) {
            abort(404, 'El reporte de errores ya no está disponible. Vuelve a procesar la importación.');
        }

        try {
            $export = new ErroresImportExport(
                errores:        $metadata['errores'] ?? [],
                nombreOriginal: $metadata['nombre_original'] ?? 'importacion.xlsx',
                cabeceras:      $metadata['cabeceras'] ?? [],
                mapeo:          $metadata['mapeo'] ?? []
            );

            return $export->download($sessionId);

        } catch (\Throwable $e) {
            Log::error('[ImportController] Error al generar Excel de errores: ' . $e->getMessage());

            abort(500, 'Error al generar el Excel de errores: ' . $e->getMessage());
        }
    }

    // ============================================================
    // CANCELAR IMPORTACIÓN
    // ============================================================

    /**
     * Limpia el archivo temporal y los datos de caché cuando el usuario
     * abandona el proceso sin procesar.
     */
    public function cancelar(string $sessionId)
    {
        if (!auth()->user()->hasPermission('crear-activo')) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        $cacheKey = self::PREFIJO_CACHE . $sessionId;
        $metadata = Cache::get($cacheKey);

        // Limpiar archivo temporal
        if ($metadata !== null && !empty($metadata['ruta_relativa'])) {
            try {
                Storage::disk('local')->delete($metadata['ruta_relativa']);
            } catch (\Throwable $e) {
                Log::warning('[ImportController] No se pudo borrar el archivo temporal', [
                    'ruta' => $metadata['ruta_relativa'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Limpiar caché
        Cache::forget($cacheKey);
        Cache::forget($cacheKey . '_errores');

        return response()->json([
            'success' => true,
            'message' => 'Importación cancelada y archivos temporales eliminados.',
        ]);
    }

    // ============================================================
    // HELPERS PRIVADOS
    // ============================================================

    /**
     * Genera un identificador único y seguro para la sesión de importación.
     */
    private function generarSessionId(): string
    {
        return self::PREFIJO_SESION . Str::lower(Str::random(16)) . '_' . time();
    }

    /**
     * Guarda el archivo subido en storage/app/private/imports/{sessionId}.{ext}.
     *
     * @return string  Ruta relativa (para usar con Storage::disk('local'))
     */
    private function guardarArchivoTemporal($archivo, string $sessionId): string
    {
        $extension = strtolower($archivo->getClientOriginalExtension());

        if (!in_array($extension, self::EXTENSIONES_EXCEL, true)) {
            $extension = 'xlsx';
        }

        $nombreArchivo = $sessionId . '.' . $extension;

        // Guardar en storage/app/private/imports/
        $rutaRelativa = $archivo->storeAs(
            self::DIRECTORIO_TEMP,
            $nombreArchivo,
            'local'
        );

        return $rutaRelativa;
    }

    /**
     * Formatea el mapeo para enviarlo al frontend de manera consistente.
     *
     * @param array $mapeo  Mapeo interno (nombre_columna => info)
     * @return array<int, array>
     */
    private function formatearMapeoParaFrontend(array $mapeo): array
    {
        $resultado = [];

        foreach ($mapeo as $nombreColumna => $info) {
            $resultado[] = [
                'columna_excel' => $nombreColumna,
                'campo'         => $info['campo'] ?? null,
                'confianza'     => $info['confianza'] ?? 'nula',
                'indice'        => $info['indice'] ?? null,
            ];
        }

        return $resultado;
    }

    /**
     * Lista de campos disponibles para el select de mapeo manual.
     */
    private function camposDisponiblesParaSelect(): array
    {
        return [
            ['valor' => '',                    'etiqueta' => '— Ignorar columna —'],
            ['valor' => 'serial',              'etiqueta' => 'Serial (obligatorio)'],
            ['valor' => 'marca',               'etiqueta' => 'Marca'],
            ['valor' => 'modelo',              'etiqueta' => 'Modelo'],
            ['valor' => 'categoria',           'etiqueta' => 'Categoría'],
            ['valor' => 'institucion',         'etiqueta' => 'Institución'],
            ['valor' => 'departamento',        'etiqueta' => 'Departamento'],
            ['valor' => 'ubicacion',           'etiqueta' => 'Ubicación'],
            ['valor' => 'fecha_adquisicion',   'etiqueta' => 'Fecha de adquisición'],
            ['valor' => 'fecha_fin_garantia',  'etiqueta' => 'Fecha fin de garantía'],
            ['valor' => 'vida_util_anos',      'etiqueta' => 'Vida útil (años)'],
            ['valor' => 'estatus',             'etiqueta' => 'Estatus'],
            ['valor' => 'observaciones',       'etiqueta' => 'Observaciones'],
        ];
    }

    /**
     * Normaliza el mapeo enviado desde el frontend. El frontend puede enviarlo:
     *   - Como array de objetos: [ {columna_excel, campo, indice}, ... ]
     *   - Como objeto indexado:  { "N° Serie": {campo, indice}, ... }
     *
     * @return array<string, array>  mapeo normalizado
     */
    private function normalizarMapeoEntrada(array $mapeo, array $metadata): array
    {
        // Caso 1: array de objetos con "columna_excel"
        if (isset($mapeo[0]) && is_array($mapeo[0]) && isset($mapeo[0]['columna_excel'])) {
            $normalizado = [];

            foreach ($mapeo as $entrada) {
                $nombreColumna = (string) ($entrada['columna_excel'] ?? '');
                $campo         = (string) ($entrada['campo'] ?? '');
                $indice        = $entrada['indice'] ?? null;

                if ($nombreColumna === '' || $campo === '') {
                    continue;
                }

                // Recuperar índice si no viene
                if ($indice === null) {
                    $indice = $this->buscarIndiceColumna($nombreColumna, $metadata['mapeo'] ?? []);
                }

                $normalizado[$nombreColumna] = [
                    'campo'         => $campo,
                    'confianza'     => $entrada['confianza'] ?? 'alta',
                    'columna_excel' => $nombreColumna,
                    'indice'        => $indice,
                ];
            }

            return $normalizado;
        }

        // Caso 2: objeto indexado por nombre de columna
        $normalizado = [];

        foreach ($mapeo as $nombreColumna => $entrada) {
            if (!is_array($entrada)) {
                continue;
            }

            $campo = (string) ($entrada['campo'] ?? '');

            if ($campo === '') {
                continue;
            }

            $indice = $entrada['indice']
                ?? $this->buscarIndiceColumna((string) $nombreColumna, $metadata['mapeo'] ?? []);

            $normalizado[(string) $nombreColumna] = [
                'campo'         => $campo,
                'confianza'     => $entrada['confianza'] ?? 'alta',
                'columna_excel' => (string) $nombreColumna,
                'indice'        => $indice,
            ];
        }

        return $normalizado;
    }

    /**
     * Busca el índice de una columna en el mapeo original guardado en caché.
     */
    private function buscarIndiceColumna(string $nombreColumna, array $mapeoOriginal): ?int
    {
        if (isset($mapeoOriginal[$nombreColumna]['indice'])) {
            return (int) $mapeoOriginal[$nombreColumna]['indice'];
        }

        foreach ($mapeoOriginal as $nombre => $info) {
            if ($nombre === $nombreColumna && isset($info['indice'])) {
                return (int) $info['indice'];
            }
        }

        return null;
    }

    /**
     * Construye el mensaje de resultado para el toast del frontend.
     */
    private function construirMensajeResultado(array $resultado): string
    {
        $partes = [];

        if ($resultado['exitos'] > 0) {
            $partes[] = "✅ {$resultado['exitos']} activo(s) importado(s)";
        }

        if (count($resultado['errores']) > 0) {
            $partes[] = "❌ " . count($resultado['errores']) . " fila(s) con error";
        }

        if (count($resultado['advertencias']) > 0) {
            $partes[] = "⚠️ " . count($resultado['advertencias']) . " advertencia(s)";
        }

        if (empty($partes)) {
            return 'No se encontraron filas para procesar.';
        }

        return implode(' · ', $partes);
    }
}