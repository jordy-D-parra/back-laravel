<?php

declare(strict_types=1);

namespace App\Services\Importacion;

use App\Models\Categoria;
use App\Models\Departamento;
use App\Models\Institucion;
use App\Models\Marca;
use App\Models\Responsable;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Servicio para detectar automáticamente el mapeo de columnas de un Excel.
 *
 * Uso típico:
 *   $mapper = new ExcelColumnMapper();
 *   $mapeo  = $mapper->detectarMapeo($rutaExcel);
 *   $faltantes = $mapper->detectarEntidadesNoReconocidas($rutaExcel, $mapeo);
 */
class ExcelColumnMapper
{
    /**
     * Diccionario de sinónimos por campo.
     *
     * La clave es el nombre del campo en el sistema y el valor es la lista
     * de sinónimos que se buscarán (normalizados) en las cabeceras del Excel.
     */
    private const SINONIMOS = [
        'serial' => [
            'serial', 'n serie', 'numero de serie', 'nro serie',
            'no. serie', 'no serie', 'sn', 's/n', 'num serie',
            'numero serie', 'serie',
        ],
        'marca' => [
            'marca', 'fabricante', 'brand', 'marca equipo',
        ],
        'modelo' => [
            'modelo', 'equipo', 'descripcion', 'producto', 'nombre',
            'modelo equipo', 'nombre equipo', 'descripcion equipo',
        ],
        'categoria' => [
            'categoria', 'tipo', 'clase', 'tipo equipo', 'categoria equipo',
        ],
        'institucion' => [
            'institucion', 'entidad', 'organizacion', 'organismo',
            'institucion destino', 'ente',
        ],
        'departamento' => [
            'departamento', 'area', 'division', 'dpto', 'depto',
            'departamento destino',
        ],
        'responsable' => [
            'responsable', 'usuario', 'encargado', 'asignado',
            'responsable actual', 'portador',
        ],
        'ubicacion' => [
            'ubicacion', 'lugar', 'oficina', 'piso', 'espacio',
            'sitio', 'localizacion',
        ],
        'fecha_adquisicion' => [
            'fecha compra', 'fecha adquisicion', 'fecha de compra',
            'fecha', 'fecha adq', 'adquisicion', 'fecha ingreso',
        ],
        'fecha_fin_garantia' => [
            'fin garantia', 'vencimiento garantia', 'fecha fin garantia',
            'fin de garantia', 'garantia hasta', 'vence garantia',
        ],
        'vida_util_anos' => [
            'vida util', 'vida útil años', 'vida util anos',
            'vida util (anos)', 'vida util (años)', 'anios vida util',
            'años de vida util', 'anos de vida util',
        ],
        'estatus' => [
            'estado', 'estatus', 'situacion', 'condicion', 'status',
            'estado equipo',
        ],
        'observaciones' => [
            'notas', 'observaciones', 'comentarios', 'obs',
            'observacion', 'nota', 'detalles',
        ],
    ];

    /**
     * Niveles de confianza internos (ordenados de mayor a menor).
     */
    private const CONFIANZA_ALTA  = 'alta';
    private const CONFIANZA_MEDIA = 'media';
    private const CONFIANZA_BAJA  = 'baja';
    private const CONFIANZA_NULA  = 'nula';

    /**
     * Campos que se consideran "críticos" para el import.
     * Si alguno falta, el usuario DEBE mapearlo manualmente.
     */
    private const CAMPOS_CRITICOS = ['serial'];

    // ============================================================
    // API PÚBLICA
    // ============================================================

    /**
     * Detecta el mapeo de columnas de un archivo Excel.
     *
     * @param string $rutaArchivo  Ruta absoluta al archivo .xlsx
     * @return array{
     *     total_filas: int,
     *     cabeceras: array<int, string>,
     *     mapeo: array<string, array{campo: string, confianza: string, columna_excel: string}>,
     *     campos_criticos_faltantes: array<int, string>
     * }
     */
    public function detectarMapeo(string $rutaArchivo): array
    {
        $hoja = $this->abrirPrimeraHoja($rutaArchivo);
        $cabeceras = $this->leerCabeceras($hoja);
        $totalFilas = max(0, $hoja->getHighestDataRow() - 1);

        $mapeo = [];
        $camposUsados = [];

        // Primera pasada: coincidencia EXACTA
        foreach ($cabeceras as $indice => $cabecera) {
            $cabeceraNorm = $this->normalizar($cabecera);

            if ($cabeceraNorm === '') {
                continue;
            }

            $campo = $this->buscarCampoPorCoincidenciaExacta($cabeceraNorm);

            if ($campo === null || in_array($campo, $camposUsados, true)) {
                continue;
            }

            $mapeo[$cabecera] = [
                'campo'         => $campo,
                'confianza'     => self::CONFIANZA_ALTA,
                'columna_excel' => $cabecera,
                'indice'        => $indice,
            ];
            $camposUsados[] = $campo;
        }

        // Segunda pasada: coincidencia PARCIAL (contiene)
        foreach ($cabeceras as $indice => $cabecera) {
            if (isset($mapeo[$cabecera])) {
                continue;
            }

            $cabeceraNorm = $this->normalizar($cabecera);

            if ($cabeceraNorm === '') {
                continue;
            }

            $campo = $this->buscarCampoPorCoincidenciaParcial($cabeceraNorm, $camposUsados);

            if ($campo === null) {
                continue;
            }

            $mapeo[$cabecera] = [
                'campo'         => $campo,
                'confianza'     => self::CONFIANZA_MEDIA,
                'columna_excel' => $cabecera,
                'indice'        => $indice,
            ];
            $camposUsados[] = $campo;
        }

        // Tercera pasada: heurística por contenido (solo si quedan columnas sin mapear
        // y hay campos críticos sin asignar)
        $camposFaltantesCriticos = array_diff(self::CAMPOS_CRITICOS, $camposUsados);

        if (!empty($camposFaltantesCriticos)) {
            foreach ($cabeceras as $indice => $cabecera) {
                if (isset($mapeo[$cabecera])) {
                    continue;
                }

                // Intentar adivinar si la columna parece un serial
                if (in_array('serial', $camposFaltantesCriticos, true)
                    && $this->pareceSerial($hoja, $indice)
                ) {
                    $mapeo[$cabecera] = [
                        'campo'         => 'serial',
                        'confianza'     => self::CONFIANZA_BAJA,
                        'columna_excel' => $cabecera,
                        'indice'        => $indice,
                    ];
                    $camposUsados[] = 'serial';
                    break;
                }
            }
        }

        // Recalcular faltantes al final
        $camposFaltantesCriticos = array_values(
            array_diff(self::CAMPOS_CRITICOS, $camposUsados)
        );

        return [
            'total_filas'                => $totalFilas,
            'cabeceras'                  => array_values($cabeceras),
            'mapeo'                      => $mapeo,
            'campos_criticos_faltantes'  => $camposFaltantesCriticos,
        ];
    }

    /**
     * Lee el Excel y detecta entidades (instituciones, marcas, categorías,
     * departamentos, responsables) que aparecen en el archivo pero NO existen
     * en la base de datos.
     *
     * @param string $rutaArchivo
     * @param array  $mapeo  Mapeo detectado (o corregido por el usuario)
     * @return array{
     *     instituciones: array<int, string>,
     *     departamentos: array<int, string>,
     *     responsables: array<int, string>,
     *     marcas: array<int, string>,
     *     categorias: array<int, string>
     * }
     */
    public function detectarEntidadesNoReconocidas(string $rutaArchivo, array $mapeo): array
    {
        $hoja = $this->abrirPrimeraHoja($rutaArchivo);

        // Índice de columnas por campo
        $indicesPorCampo = $this->indicesPorCampo($mapeo);

        // Acumuladores de valores únicos por campo
        $valores = [
            'institucion'  => [],
            'departamento' => [],
            'responsable'  => [],
            'marca'        => [],
            'categoria'    => [],
        ];

        $filaMax = $hoja->getHighestDataRow();

        for ($fila = 2; $fila <= $filaMax; $fila++) {
            foreach ($valores as $campo => $_) {
                if (!isset($indicesPorCampo[$campo])) {
                    continue;
                }

                $valor = $this->leerCelda($hoja, $indicesPorCampo[$campo], $fila);
                $valor = trim((string) $valor);

                if ($valor !== '') {
                    $valores[$campo][$valor] = true;
                }
            }
        }

        // Ahora cruzamos cada lista con la BD
        return [
            'instituciones' => $this->filtrarNoExistentes(
                array_keys($valores['institucion']),
                'instituciones'
            ),
            'departamentos' => $this->filtrarNoExistentes(
                array_keys($valores['departamento']),
                'departamentos'
            ),
            'responsables'  => $this->filtrarNoExistentes(
                array_keys($valores['responsable']),
                'responsables'
            ),
            'marcas'        => $this->filtrarNoExistentes(
                array_keys($valores['marca']),
                'marcas'
            ),
            'categorias'    => $this->filtrarNoExistentes(
                array_keys($valores['categoria']),
                'categorias'
            ),
        ];
    }

    /**
     * Devuelve el diccionario completo de sinónimos.
     *
     * @return array<string, array<int, string>>
     */
    public function getSinonimos(): array
    {
        return self::SINONIMOS;
    }

    /**
     * Devuelve la lista de campos críticos.
     *
     * @return array<int, string>
     */
    public function getCamposCriticos(): array
    {
        return self::CAMPOS_CRITICOS;
    }

    // ============================================================
    // HELPERS INTERNOS - LECTURA DE EXCEL
    // ============================================================

    /**
     * Abre la primera hoja de un archivo Excel.
     */
    private function abrirPrimeraHoja(string $rutaArchivo): Worksheet
    {
        $reader = IOFactory::createReaderForFile($rutaArchivo);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($rutaArchivo);

        return $spreadsheet->getSheet(0);
    }

    /**
     * Lee la fila 1 (cabeceras).
     *
     * @return array<int, string>  índice => valor
     */
    private function leerCabeceras(Worksheet $hoja): array
    {
        $cabeceras = [];
        $colMax = $hoja->getHighestDataColumn();

        // Convertir letra de columna a índice numérico (A=1, B=2, ...)
        $colMaxIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colMax);

        for ($col = 1; $col <= $colMaxIndex; $col++) {
            $valor = $hoja->getCell([$col, 1])->getValue();
            $valor = trim((string) $valor);

            if ($valor !== '') {
                $cabeceras[$col] = $valor;
            }
        }

        return $cabeceras;
    }

    /**
     * Lee una celda dada una columna y fila (1-indexed).
     */
    private function leerCelda(Worksheet $hoja, int $columna, int $fila): mixed
    {
        return $hoja->getCell([$columna, $fila])->getValue();
    }

    /**
     * Dado un mapeo (columna_excel => campo), devuelve un array
     * campo => índice_columna.
     *
     * @param array $mapeo
     * @return array<string, int>
     */
    private function indicesPorCampo(array $mapeo): array
    {
        $resultado = [];

        foreach ($mapeo as $entrada) {
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

    // ============================================================
    // HELPERS INTERNOS - BÚSQUEDA DE CAMPOS
    // ============================================================

    /**
     * Busca un campo por coincidencia exacta con alguno de sus sinónimos.
     */
    private function buscarCampoPorCoincidenciaExacta(string $cabeceraNorm): ?string
    {
        foreach (self::SINONIMOS as $campo => $sinonimos) {
            foreach ($sinonimos as $sinonimo) {
                if ($cabeceraNorm === $this->normalizar($sinonimo)) {
                    return $campo;
                }
            }
        }

        return null;
    }

    /**
     * Busca un campo por coincidencia parcial, excluyendo los ya usados.
     */
    private function buscarCampoPorCoincidenciaParcial(string $cabeceraNorm, array $camposUsados): ?string
    {
        // Orden: primero sinónimos más largos (más específicos)
        foreach (self::SINONIMOS as $campo => $sinonimos) {
            if (in_array($campo, $camposUsados, true)) {
                continue;
            }

            $sinonimosOrdenados = $sinonimos;
            usort($sinonimosOrdenados, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

            foreach ($sinonimosOrdenados as $sinonimo) {
                $sinonimoNorm = $this->normalizar($sinonimo);

                if ($sinonimoNorm === '') {
                    continue;
                }

                // Contiene el sinónimo como substring
                if (str_contains($cabeceraNorm, $sinonimoNorm)) {
                    return $campo;
                }
            }
        }

        return null;
    }

    /**
     * Heurística: ¿la columna parece contener seriales?
     * Toma hasta 5 valores de muestra y verifica que al menos 3
     * cumplan con un patrón de serial (alfanumérico, con guiones).
     */
    private function pareceSerial(Worksheet $hoja, int $columna): bool
    {
        $muestras = [];
        $filaMax = min(6, $hoja->getHighestDataRow()); // máximo 5 filas de datos

        for ($fila = 2; $fila <= $filaMax; $fila++) {
            $valor = trim((string) $this->leerCelda($hoja, $columna, $fila));

            if ($valor !== '') {
                $muestras[] = $valor;
            }
        }

        if (count($muestras) < 3) {
            return false;
        }

        $cumple = 0;

        foreach ($muestras as $muestra) {
            // Patrón: al menos 5 caracteres, alfanuméricos con guiones y letras mayúsculas
            if (preg_match('/^[A-Z0-9][A-Z0-9\-_]{4,}$/i', $muestra)) {
                $cumple++;
            }
        }

        return $cumple >= 3;
    }

    // ============================================================
    // HELPERS INTERNOS - FILTRADO CONTRA BD
    // ============================================================

    /**
     * Dada una lista de valores y una tabla, devuelve los valores
     * que NO existen en la BD (comparación insensible a mayúsculas
     * y acentos, pero devolviendo el valor ORIGINAL).
     *
     * @param array<int, string> $valores
     * @param string             $tabla   'instituciones' | 'departamentos' | 'responsables' | 'marcas' | 'categorias'
     * @return array<int, string>
     */
    private function filtrarNoExistentes(array $valores, string $tabla): array
    {
        if (empty($valores)) {
            return [];
        }

        // Extraer todos los nombres existentes (normalizados)
        $existentes = $this->nombresExistentes($tabla);

        $faltantes = [];

        foreach ($valores as $valor) {
            $norm = $this->normalizar($valor);

            if (!isset($existentes[$norm])) {
                $faltantes[] = $valor;
            }
        }

        // Ordenar alfabéticamente para presentación estable
        sort($faltantes, SORT_NATURAL | SORT_FLAG_CASE);

        return array_values(array_unique($faltantes));
    }

    /**
     * Devuelve los nombres de una tabla como un set indexado por
     * nombre normalizado: ['dell' => true, 'hp' => true, ...]
     *
     * @return array<string, bool>
     */
    private function nombresExistentes(string $tabla): array
    {
        $columnas = match ($tabla) {
            'instituciones' => Institucion::query()->pluck('nombre'),
            'departamentos' => Departamento::query()->pluck('nombre'),
            'responsables'  => Responsable::query()->pluck('nombre'),
            'marcas'        => Marca::query()->pluck('nombre'),
            'categorias'    => Categoria::query()->pluck('nombre'),
            default         => collect(),
        };

        $resultado = [];

        foreach ($columnas as $nombre) {
            $norm = $this->normalizar((string) $nombre);
            if ($norm !== '') {
                $resultado[$norm] = true;
            }
        }

        return $resultado;
    }

    // ============================================================
    // HELPERS INTERNOS - NORMALIZACIÓN
    // ============================================================

    /**
     * Normaliza un texto para comparaciones:
     *   - minúsculas
     *   - sin acentos
     *   - sin espacios múltiples
     *   - sin puntos finales
     */
    private function normalizar(?string $texto): string
    {
        if ($texto === null) {
            return '';
        }

        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $texto = $this->quitarAcentos($texto);
        $texto = preg_replace('/\s+/', ' ', $texto) ?? $texto;
        $texto = rtrim($texto, '.');

        return $texto;
    }

    /**
     * Quita acentos de un string UTF-8.
     */
    private function quitarAcentos(string $texto): string
    {
        $reemplazos = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
            'ã' => 'a', 'õ' => 'o',
            'ñ' => 'n', 'ç' => 'c',
        ];

        return strtr($texto, $reemplazos);
    }
}