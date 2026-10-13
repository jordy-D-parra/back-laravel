<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ActivosImport;
use App\Services\Importacion\ExcelColumnMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Controlador del módulo de importación de activos desde Excel.
 *
 * Endpoints:
 *   GET  /admin/importar                       → redirect al tab de inventario
 *   GET  /admin/importar/plantilla             → descarga plantilla Excel
 *   POST /admin/importar/analizar              → sube Excel, detecta mapeo, devuelve JSON
 *   POST /admin/importar/procesar              → procesa con mapeo+decisiones, devuelve resultado
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
    // REDIRECT AL TAB DE IMPORTAR EN INVENTARIO
    // ============================================================

    /**
     * Redirige al tab de importar dentro del módulo de Inventario.
     */
    public function index()
    {
        if (!auth()->user()->hasPermission('crear-activo')) {
            abort(403, 'No tienes permiso para importar activos');
        }

        return redirect()->route('admin.inventario.index', ['tab' => 'importar']);
    }

    // ============================================================
    // DESCARGA DE PLANTILLA (generada inline)
    // ============================================================

    /**
     * Descarga la plantilla de Excel con las columnas exactas que espera el sistema.
     * Se genera inline con PhpSpreadsheet (sin clase Export separada).
     */
    public function descargarPlantilla()
    {
        if (!auth()->user()->hasPermission('crear-activo')) {
            abort(403, 'No tienes permiso para descargar la plantilla');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla Activos');

        // ============================================================
        // Cabeceras oficiales (orden recomendado)
        // ============================================================
        $cabeceras = [
            'Serial'              => 22,
            'Marca'               => 18,
            'Modelo'              => 26,
            'Categoría'           => 18,
            'Institución'         => 30,
            'Departamento'        => 22,
            'Ubicación'           => 22,
            'Fecha Adquisición'   => 18,
            'Fecha Fin Garantía'  => 18,
            'Vida Útil (años)'    => 16,
            'Estatus'             => 16,
            'Observaciones'       => 40,
        ];

        $col = 1;
        foreach ($cabeceras as $titulo => $ancho) {
            $letra = Coordinate::stringFromColumnIndex($col);

            $sheet->setCellValue("{$letra}1", $titulo);
            $sheet->getColumnDimension($letra)->setWidth($ancho);

            $sheet->getStyle("{$letra}1")->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3C72'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'FFFFFF'],
                    ],
                ],
            ]);

            $col++;
        }

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->freezePane('A2');

        // ============================================================
        // Fila de ejemplo
        // ============================================================
        $letraFinal = Coordinate::stringFromColumnIndex(count($cabeceras));

        $sheet->mergeCells("A2:{$letraFinal}2");
        $sheet->setCellValue(
            'A2',
            'Ejemplo: SN-001 | Dell | Latitude 5540 | Laptop | Gobernación del Estado Yaracuy | Informática | Piso 3 | 15/03/2024 | 15/03/2027 | 3 | Bueno | Equipo nuevo'
        );
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'italic' => true,
                'color'  => ['rgb' => '6C757D'],
                'size'   => 9,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // ============================================================
        // Nota al pie
        // ============================================================
        $sheet->mergeCells("A4:{$letraFinal}4");
        $sheet->setCellValue(
            'A4',
            'Nota: La fila de ejemplo puede eliminarse. Las columnas "Serial" y "Modelo" son obligatorias. Si una institución o marca no existe, se te pedirá resolverla antes de importar.'
        );
        $sheet->getStyle('A4')->applyFromArray([
            'font' => [
                'italic' => true,
                'color'  => ['rgb' => '856404'],
                'size'   => 9,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF9E6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'wrapText'   => true,
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(30);

        // ============================================================
        // Descargar
        // ============================================================
        $nombreArchivo = 'plantilla_activos_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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

            // 1. Guardar el archivo temporalmente
            $rutaRelativa = $this->guardarArchivoTemporal($archivo, $sessionId);
            $rutaAbsoluta = Storage::disk('local')->path($rutaRelativa);

            // 2. Detectar mapeo de columnas
            $mapeo = $this->mapper->detectarMapeo($rutaAbsoluta);

            // 3. Detectar entidades no reconocidas
            $entidadesNoReconocidas = $this->mapper->detectarEntidadesNoReconocidas(
                $rutaAbsoluta,
                $mapeo['mapeo']
            );

            // 4. Guardar metadata en caché
            $metadata = [
                'session_id'      => $sessionId,
                'ruta_relativa'   => $rutaRelativa,
                'ruta_absoluta'   => $rutaAbsoluta,
                'nombre_original' => $archivo->getClientOriginalName(),
                'cabeceras'       => $mapeo['cabeceras'],
                'mapeo'           => $mapeo['mapeo'],
                'total_filas'     => $mapeo['total_filas'],
                'creado_por'      => auth()->id(),
                'creado_en'       => now()->toDateTimeString(),
            ];

            Cache::put(
                self::PREFIJO_CACHE . $sessionId,
                $metadata,
                now()->addMinutes(self::TTL_CACHE_MINUTOS)
            );

            Log::info('[ImportController] Excel analizado', [
                'session_id'  => $sessionId,
                'archivo'     => $archivo->getClientOriginalName(),
                'total_filas' => $mapeo['total_filas'],
                'user_id'     => auth()->id(),
            ]);

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

        if (($metadata['creado_por'] ?? null) !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para procesar esta importación.',
            ], 403);
        }

        $rutaAbsoluta = $metadata['ruta_absoluta'] ?? null;

        if ($rutaAbsoluta === null || !File::exists($rutaAbsoluta)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo temporal ya no está disponible. Vuelve a subirlo.',
            ], 410);
        }

        // ------------------------------------------------------------
        // 3. Normalizar el mapeo
        // ------------------------------------------------------------
        $mapeoNormalizado = $this->normalizarMapeoEntrada($mapeo, $metadata);

        if (empty($mapeoNormalizado)) {
            return response()->json([
                'success' => false,
                'message' => 'El mapeo enviado está vacío o mal formado.',
            ], 422);
        }

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
            // 5. Enriquecer advertencias con el activo_id
            // ------------------------------------------------------------
            $advertenciasConId = [];
            $exitosPorSerial = [];

            foreach ($import->getExitos() as $exito) {
                $exitosPorSerial[$exito['serial']] = $exito['activo_id'];
            }

            foreach ($resultado['advertencias'] as $adv) {
                $serial = $adv['serial'] ?? null;
                $activoId = $serial !== null ? ($exitosPorSerial[$serial] ?? null) : null;

                $advertenciasConId[] = array_merge($adv, ['activo_id' => $activoId]);
            }

            // ------------------------------------------------------------
            // 6. Log
            // ------------------------------------------------------------
            Log::info('[ImportController] Importación procesada', [
                'session_id'   => $sessionId,
                'exitos'       => $resultado['exitos'],
                'errores'      => count($resultado['errores']),
                'advertencias' => count($resultado['advertencias']),
                'tiempo'       => $resultado['tiempo_segundos'],
                'user_id'      => auth()->id(),
            ]);

            // ------------------------------------------------------------
            // 7. Respuesta (SIN url_errores porque no hay Excel de errores)
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
                    'advertencias'        => $advertenciasConId,
                    'total_errores'       => count($resultado['errores']),
                    'total_advertencias'  => count($advertenciasConId),
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

        if ($metadata !== null && !empty($metadata['ruta_relativa'])) {
            try {
                Storage::disk('local')->delete($metadata['ruta_relativa']);
            } catch (\Throwable $e) {
                Log::warning('[ImportController] No se pudo borrar el archivo temporal', [
                    'ruta'  => $metadata['ruta_relativa'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Cache::forget($cacheKey);

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
     */
    private function guardarArchivoTemporal($archivo, string $sessionId): string
    {
        $extension = strtolower($archivo->getClientOriginalExtension());

        if (!in_array($extension, self::EXTENSIONES_EXCEL, true)) {
            $extension = 'xlsx';
        }

        $nombreArchivo = $sessionId . '.' . $extension;

        return $archivo->storeAs(
            self::DIRECTORIO_TEMP,
            $nombreArchivo,
            'local'
        );
    }

    /**
     * Formatea el mapeo para enviarlo al frontend de manera consistente.
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
     * Normaliza el mapeo enviado desde el frontend.
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
            $partes[] = "{$resultado['exitos']} activo(s) importado(s)";
        }

        if (count($resultado['errores']) > 0) {
            $partes[] = count($resultado['errores']) . " fila(s) con error";
        }

        if (count($resultado['advertencias']) > 0) {
            $partes[] = count($resultado['advertencias']) . " advertencia(s)";
        }

        if (empty($partes)) {
            return 'No se encontraron filas para procesar.';
        }

        return implode(' · ', $partes);
    }
}