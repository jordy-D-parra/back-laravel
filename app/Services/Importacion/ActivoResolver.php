<?php

declare(strict_types=1);

namespace App\Services\Importacion;

use App\Models\Categoria;
use App\Models\Departamento;
use App\Models\Estatus;
use App\Models\Institucion;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Responsable;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Servicio que resuelve todas las entidades relacionadas a una fila de importación.
 *
 * Una "fila" es un array asociativo con los campos mapeados:
 *   [
 *     'serial'              => 'SN-001',
 *     'marca'               => 'Dell',
 *     'modelo'              => 'Latitude 5540',
 *     'categoria'           => 'Laptop',
 *     'institucion'         => 'Gobernación',
 *     'departamento'        => 'Informática',
 *     'responsable'         => 'Juan Pérez',
 *     'ubicacion'           => 'Piso 3',
 *     'fecha_adquisicion'   => '15/03/2024',
 *     'fecha_fin_garantia'  => '15/03/2027',
 *     'vida_util_anos'      => 3,
 *     'estatus'             => 'Bueno',
 *     'observaciones'       => 'Equipo nuevo',
 *   ]
 *
 * El resolver devuelve:
 *   [
 *     'marca_id'         => int|null,
 *     'categoria_id'     => int|null,
 *     'modelo_id'        => int|null,
 *     'id_estatus'       => int,
 *     'institucion_id'   => int|null,
 *     'departamento_id'  => int|null,
 *     'responsable_id'   => int|null,
 *     'serial'           => string,
 *     'ubicacion'        => string|null,
 *     'fecha_adquisicion'    => string|null (Y-m-d),
 *     'fecha_fin_garantia'   => string|null (Y-m-d),
 *     'vida_util_anos'       => int|null,
 *     'observaciones'        => string|null,
 *     'datos_incompletos'    => array|null,  // null si todo se resolvió
 *   ]
 */
class ActivoResolver
{
    // ============================================================
    // CONSTANTES
    // ============================================================

    private const MARCA_POR_DEFECTO     = 'Sin marca';
    private const MODELO_POR_DEFECTO    = 'Sin modelo';
    private const CATEGORIA_POR_DEFECTO = 'Sin categoría';
    private const ESTATUS_POR_DEFECTO   = 'En bodega';

    /**
     * Mapeo de variantes de estatus al estatus canónico.
     * Las claves están normalizadas (minúsculas, sin acentos).
     */
    private const MAPEO_ESTATUS = [
        // Disponible
        'bueno'         => 'Disponible',
        'nuevo'         => 'Disponible',
        'optimo'        => 'Disponible',
        'funcional'     => 'Disponible',
        'operativo'     => 'Disponible',
        'disponible'    => 'Disponible',
        'activo'        => 'Disponible',

        // Prestado
        'en uso'        => 'Prestado',
        'en_uso'        => 'Prestado',
        'asignado'      => 'Prestado',
        'prestado'      => 'Prestado',
        'en prestamo'   => 'Prestado',
        'en_prestamo'   => 'Prestado',

        // En reparación
        'reparacion'    => 'En reparación',
        'en reparacion' => 'En reparación',
        'en_reparacion' => 'En reparación',
        'danado'        => 'En reparación',
        'dañado'        => 'En reparación',
        'averiado'      => 'En reparación',
        'malo'          => 'En reparación',
        'defectuoso'    => 'En reparación',
        'con fallas'    => 'En reparación',

        // En bodega
        'guardado'      => 'En bodega',
        'almacen'       => 'En bodega',
        'en almacen'    => 'En bodega',
        'en bodega'     => 'En bodega',
        'en_bodega'     => 'En bodega',
        'bodega'        => 'En bodega',
        'stock'         => 'En bodega',
        'sin uso'       => 'En bodega',

        // Desechado
        'baja'          => 'Desechado',
        'desecho'       => 'Desechado',
        'desechado'     => 'Desechado',
        'obsoleto'      => 'Desechado',
        'inservible'    => 'Desechado',
        'para desecho'  => 'Desechado',
    ];

    /**
     * Formatos de fecha aceptados (para Carbon::createFromFormat).
     */
    private const FORMATOS_FECHA = [
        'Y-m-d',
        'd/m/Y',
        'd-m-Y',
        'm/d/Y',
        'm-d-Y',
        'Y/m/d',
        'd.m.Y',
        'd.m.y',
        'Y.m.d',
    ];

    // ============================================================
    // DEPENDENCIAS
    // ============================================================

    private CategoriaInferida $categoriaInferida;

    /**
     * Decisiones del usuario sobre entidades no reconocidas.
     * Formato:
     *   [
     *     'instituciones' => ['Alcaldía X' => ['accion' => 'vincular', 'id' => 42], ...],
     *     'marcas'        => ['XYZ Corp'   => ['accion' => 'crear'], ...],
     *     'categorias'    => [...],
     *     'departamentos' => [...],
     *     'responsables'  => [...],
     *   ]
     */
    private array $decisiones = [];

    /**
     * Si es true, se permite crear marcas/categorías/modelos automáticamente
     * cuando NO hay decisión explícita del usuario.
     */
    private bool $crearEntidadesPorDefecto = true;

    // ============================================================
    // CONSTRUCTOR
    // ============================================================

    public function __construct(?CategoriaInferida $categoriaInferida = null)
    {
        $this->categoriaInferida = $categoriaInferida ?? new CategoriaInferida();
    }

    /**
     * Setea las decisiones del usuario (desde el preview).
     */
    public function setDecisiones(array $decisiones): self
    {
        $this->decisiones = $decisiones;
        return $this;
    }

    /**
     * Habilita/deshabilita la creación por defecto de entidades.
     */
    public function setCrearEntidadesPorDefecto(bool $valor): self
    {
        $this->crearEntidadesPorDefecto = $valor;
        return $this;
    }

    // ============================================================
    // PUNTO DE ENTRADA PRINCIPAL
    // ============================================================

    /**
     * Resuelve una fila completa y devuelve los IDs/valores listos
     * para insertar en `activos`.
     *
     * @param array $fila  Array asociativo con los campos mapeados
     * @return array{
     *     serial: string,
     *     modelo_id: int,
     *     id_estatus: int,
     *     institucion_id: int|null,
     *     departamento_id: int|null,
     *     responsable_id: int|null,
     *     ubicacion: string|null,
     *     fecha_adquisicion: string|null,
     *     fecha_fin_garantia: string|null,
     *     vida_util_anos: int|null,
     *     observaciones: string|null,
     *     datos_incompletos: array|null
     * }
     *
     * @throws \RuntimeException Si el serial es inválido o vacío
     */
    public function resolverFila(array $fila): array
    {
        // ============================================================
        // 1. SERIAL (crítico)
        // ============================================================
        $serial = $this->normalizarSerial($fila['serial'] ?? null);

        if ($serial === '') {
            throw new \RuntimeException('El serial es obligatorio y no puede estar vacío.');
        }

        // ============================================================
        // 2. MARCA
        // ============================================================
        $marcaNombreOriginal = $this->limpiar($fila['marca'] ?? null);
        $marca = $this->resolverMarca($marcaNombreOriginal);

        // ============================================================
        // 3. CATEGORÍA
        // ============================================================
        $categoriaNombreOriginal = $this->limpiar($fila['categoria'] ?? null);
        $modeloNombre = $this->limpiar($fila['modelo'] ?? null);

        $categoria = $this->resolverCategoria(
            $categoriaNombreOriginal,
            $modeloNombre,
            $marca?->nombre
        );

        // ============================================================
        // 4. MODELO
        // ============================================================
        $modelo = $this->resolverModelo($modeloNombre, $marca, $categoria);

        // ============================================================
        // 5. ESTATUS
        // ============================================================
        $idEstatus = $this->resolverEstatus($fila['estatus'] ?? null);

        // ============================================================
        // 6. INSTITUCIÓN + DEPARTAMENTO + RESPONSABLE
        // ============================================================
        $institucionNombreOriginal = $this->limpiar($fila['institucion'] ?? null);
        $departamentoNombreOriginal = $this->limpiar($fila['departamento'] ?? null);

        $institucion = $this->resolverInstitucion($institucionNombreOriginal);
        $departamento = $this->resolverDepartamento($departamentoNombreOriginal, $institucion);

        // El responsable se auto-asigna desde institución/departamento,
        // NO viene del Excel por diseño (el contexto original pide que sea
        // opcional y se auto-resuelva).
        $responsable = $this->resolverResponsable($institucion, $departamento);

        // ============================================================
        // 7. CAMPOS SIMPLES
        // ============================================================
        $ubicacion = $this->limpiar($fila['ubicacion'] ?? null);
        $observaciones = $this->limpiar($fila['observaciones'] ?? null);

        $fechaAdquisicion = $this->normalizarFecha($fila['fecha_adquisicion'] ?? null);
        $fechaFinGarantia = $this->normalizarFecha($fila['fecha_fin_garantia'] ?? null);
        $vidaUtilAnos = $this->normalizarEntero($fila['vida_util_anos'] ?? null);

        // ============================================================
        // 8. DATOS INCOMPLETOS
        // ============================================================
        $datosIncompletos = $this->construirDatosIncompletos([
            'institucion' => [
                'original' => $institucionNombreOriginal,
                'id'       => $institucion?->id,
                'resuelto' => $institucion !== null,
            ],
            'departamento' => [
                'original' => $departamentoNombreOriginal,
                'id'       => $departamento?->id,
                'resuelto' => $departamento !== null || $departamentoNombreOriginal === null,
            ],
            'responsable' => [
                'original' => null,
                'id'       => $responsable?->id,
                'resuelto' => $responsable !== null,
            ],
            'categoria' => [
                'original' => $categoriaNombreOriginal,
                'id'       => $categoria?->id,
                'resuelto' => $categoria !== null
                    && $categoria->nombre !== self::CATEGORIA_POR_DEFECTO,
            ],
            'marca' => [
                'original' => $marcaNombreOriginal,
                'id'       => $marca?->id,
                'resuelto' => $marca !== null
                    && $marca->nombre !== self::MARCA_POR_DEFECTO,
            ],
            'modelo' => [
                'original' => $modeloNombre,
                'id'       => $modelo?->id,
                'resuelto' => $modelo !== null
                    && $modelo->nombre !== self::MODELO_POR_DEFECTO,
            ],
            'ubicacion' => [
                'original' => $ubicacion,
                'id'       => null,
                'resuelto' => $ubicacion !== null && $ubicacion !== '',
            ],
        ]);

        // ============================================================
        // 9. RESULTADO
        // ============================================================
        return [
            'serial'              => $serial,
            'modelo_id'           => $modelo->id,
            'id_estatus'          => $idEstatus,
            'institucion_id'      => $institucion?->id,
            'departamento_id'     => $departamento?->id,
            'responsable_id'      => $responsable?->id,
            'ubicacion'           => $ubicacion,
            'fecha_adquisicion'   => $fechaAdquisicion,
            'fecha_fin_garantia'  => $fechaFinGarantia,
            'vida_util_anos'      => $vidaUtilAnos,
            'observaciones'       => $observaciones,
            'datos_incompletos'   => $datosIncompletos,
        ];
    }

    // ============================================================
    // RESOLUCIÓN: MARCA
    // ============================================================

    private function resolverMarca(?string $nombreOriginal): Marca
    {
        // 1. Sin nombre → usar "Sin marca"
        if ($nombreOriginal === null || $nombreOriginal === '') {
            return $this->obtenerOCrearMarcaPorDefecto();
        }

        // 2. Normalizar el nombre
        $nombreNormalizado = $this->normalizarNombreEntidad($nombreOriginal);

        // 3. Buscar en BD (insensible a mayúsculas/acentos)
        $marca = Marca::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombreNormalizado, 'UTF-8')])
            ->first();

        if ($marca !== null) {
            return $marca;
        }

        // 4. No existe → consultar decisiones del usuario
        $decision = $this->decisiones['marcas'][$nombreOriginal]
            ?? $this->decisiones['marcas'][$nombreNormalizado]
            ?? null;

        if ($decision !== null) {
            $accion = $decision['accion'] ?? 'crear';

            if ($accion === 'vincular' && !empty($decision['id'])) {
                $vinculada = Marca::find((int) $decision['id']);
                if ($vinculada !== null) {
                    return $vinculada;
                }
            }

            if ($accion === 'ignorar') {
                return $this->obtenerOCrearMarcaPorDefecto();
            }

            // Si es 'crear' o no hay acción válida → crear
        } elseif (!$this->crearEntidadesPorDefecto) {
            // Sin decisión explícita y no se permite crear → usar por defecto
            return $this->obtenerOCrearMarcaPorDefecto();
        }

        // 5. Crear la marca
        return Marca::firstOrCreate(
            ['nombre' => $nombreNormalizado],
            ['activo' => true]
        );
    }

    private function obtenerOCrearMarcaPorDefecto(): Marca
    {
        return Marca::firstOrCreate(
            ['nombre' => self::MARCA_POR_DEFECTO],
            ['descripcion' => 'Marca asignada automáticamente cuando no se especifica', 'activo' => true]
        );
    }

    // ============================================================
    // RESOLUCIÓN: CATEGORÍA
    // ============================================================

    private function resolverCategoria(
        ?string $categoriaOriginal,
        ?string $modeloNombre,
        ?string $marcaNombre
    ): Categoria {
        // 1. Si viene categoría explícita → resolverla directamente
        if ($categoriaOriginal !== null && $categoriaOriginal !== '') {
            $categoria = $this->buscarOCrearCategoria($categoriaOriginal);
            if ($categoria !== null) {
                return $categoria;
            }
        }

        // 2. Sin categoría pero con modelo → intentar heredar del modelo existente
        if ($modeloNombre !== null && $marcaNombre !== null) {
            $modeloExistente = Modelo::whereHas('marca', function ($q) use ($marcaNombre) {
                $q->whereRaw('LOWER(nombre) = ?', [mb_strtolower($marcaNombre, 'UTF-8')]);
            })
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($modeloNombre, 'UTF-8')])
            ->with('categoria')
            ->first();

            if ($modeloExistente !== null && $modeloExistente->categoria !== null) {
                return $modeloExistente->categoria;
            }
        }

        // 3. Sin categoría y modelo no existe → inferir del nombre
        if ($modeloNombre !== null) {
            $categoriaInferida = $this->categoriaInferida->inferir($modeloNombre, $marcaNombre);

            if ($categoriaInferida !== null) {
                $categoria = $this->buscarOCrearCategoria($categoriaInferida);
                if ($categoria !== null) {
                    return $categoria;
                }
            }
        }

        // 4. No se pudo resolver → "Sin categoría"
        return Categoria::firstOrCreate(
            ['nombre' => self::CATEGORIA_POR_DEFECTO],
            ['descripcion' => 'Categoría asignada automáticamente', 'activo' => true]
        );
    }

    private function buscarOCrearCategoria(string $nombreOriginal): ?Categoria
    {
        $nombreNormalizado = $this->normalizarNombreEntidad($nombreOriginal);

        if ($nombreNormalizado === '') {
            return null;
        }

        // Buscar existente
        $categoria = Categoria::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombreNormalizado, 'UTF-8')])
            ->first();

        if ($categoria !== null) {
            return $categoria;
        }

        // Consultar decisiones del usuario
        $decision = $this->decisiones['categorias'][$nombreOriginal]
            ?? $this->decisiones['categorias'][$nombreNormalizado]
            ?? null;

        if ($decision !== null) {
            $accion = $decision['accion'] ?? 'crear';

            if ($accion === 'vincular' && !empty($decision['id'])) {
                $vinculada = Categoria::find((int) $decision['id']);
                if ($vinculada !== null) {
                    return $vinculada;
                }
            }

            if ($accion === 'ignorar') {
                return null;
            }
        } elseif (!$this->crearEntidadesPorDefecto) {
            return null;
        }

        // Crear
        return Categoria::firstOrCreate(
            ['nombre' => $nombreNormalizado],
            ['activo' => true]
        );
    }

    // ============================================================
    // RESOLUCIÓN: MODELO
    // ============================================================

    private function resolverModelo(
        ?string $modeloNombre,
        Marca $marca,
        Categoria $categoria
    ): Modelo {
        $nombreModelo = $modeloNombre !== null && $modeloNombre !== ''
            ? $this->normalizarNombreEntidad($modeloNombre)
            : self::MODELO_POR_DEFECTO;

        // Si el modelo es "Sin modelo", buscar/crear el especial
        if ($nombreModelo === self::MODELO_POR_DEFECTO) {
            return $this->obtenerOCrearModeloPorDefecto();
        }

        // Buscar existente (por marca + nombre)
        $modelo = Modelo::where('marca_id', $marca->id)
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombreModelo, 'UTF-8')])
            ->first();

        if ($modelo !== null) {
            return $modelo;
        }

        // No existe → crear con la marca y categoría resueltas
        return Modelo::firstOrCreate(
            [
                'marca_id' => $marca->id,
                'nombre'   => $nombreModelo,
            ],
            [
                'categoria_id' => $categoria->id,
                'activo'       => true,
            ]
        );
    }

    private function obtenerOCrearModeloPorDefecto(): Modelo
    {
        $marcaDefault = $this->obtenerOCrearMarcaPorDefecto();

        $categoriaDefault = Categoria::firstOrCreate(
            ['nombre' => self::CATEGORIA_POR_DEFECTO],
            ['descripcion' => 'Categoría asignada automáticamente', 'activo' => true]
        );

        return Modelo::firstOrCreate(
            [
                'marca_id' => $marcaDefault->id,
                'nombre'   => self::MODELO_POR_DEFECTO,
            ],
            [
                'categoria_id' => $categoriaDefault->id,
                'activo'       => true,
            ]
        );
    }

    // ============================================================
    // RESOLUCIÓN: ESTATUS
    // ============================================================

    private function resolverEstatus(?string $estatusOriginal): int
    {
        $estatusNombre = self::ESTATUS_POR_DEFECTO;

        if ($estatusOriginal !== null && $estatusOriginal !== '') {
            $normalizado = $this->normalizarTexto($estatusOriginal);

            // Buscar en el mapa de variantes
            if (isset(self::MAPEO_ESTATUS[$normalizado])) {
                $estatusNombre = self::MAPEO_ESTATUS[$normalizado];
            } else {
                // Fallback: buscar directamente en BD por descripción
                $estatus = Estatus::whereRaw(
                    'LOWER(descripcion) = ?',
                    [mb_strtolower($estatusOriginal, 'UTF-8')]
                )->first();

                if ($estatus !== null) {
                    return $estatus->id;
                }
            }
        }

        // Buscar/crear el estatus canónico
        $estatus = Estatus::where('descripcion', $estatusNombre)->first();

        if ($estatus !== null) {
            return $estatus->id;
        }

        // Fallback final: cualquier estatus que exista
        $estatus = Estatus::orderBy('id')->first();

        if ($estatus === null) {
            throw new \RuntimeException(
                'No hay estatus configurados en el sistema. Ejecuta EstatusSeeder.'
            );
        }

        return $estatus->id;
    }

    // ============================================================
    // RESOLUCIÓN: INSTITUCIÓN
    // ============================================================

    private function resolverInstitucion(?string $nombreOriginal): ?Institucion
    {
        if ($nombreOriginal === null || $nombreOriginal === '') {
            return null;
        }

        $nombreNormalizado = $this->normalizarNombreEntidad($nombreOriginal);

        // Buscar existente (ILIKE)
        $institucion = Institucion::whereRaw(
            'LOWER(nombre) = ?',
            [mb_strtolower($nombreNormalizado, 'UTF-8')]
        )->first();

        if ($institucion !== null) {
            return $institucion;
        }

        // No existe → consultar decisiones del usuario
        $decision = $this->decisiones['instituciones'][$nombreOriginal]
            ?? $this->decisiones['instituciones'][$nombreNormalizado]
            ?? null;

        if ($decision !== null) {
            $accion = $decision['accion'] ?? 'ignorar';

            if ($accion === 'vincular' && !empty($decision['id'])) {
                return Institucion::find((int) $decision['id']);
            }

            // Si es 'ignorar' o 'crear' (no permitido desde import) → null
            return null;
        }

        // Sin decisión → null (NO se crean instituciones desde el import)
        return null;
    }

    // ============================================================
    // RESOLUCIÓN: DEPARTAMENTO
    // ============================================================

    private function resolverDepartamento(?string $nombreOriginal, ?Institucion $institucion): ?Departamento
    {
        if ($nombreOriginal === null || $nombreOriginal === '') {
            return null;
        }

        $nombreNormalizado = $this->normalizarNombreEntidad($nombreOriginal);

        // Buscar existente; si tenemos institución, priorizamos la del mismo ámbito
        $query = Departamento::whereRaw(
            'LOWER(nombre) = ?',
            [mb_strtolower($nombreNormalizado, 'UTF-8')]
        );

        if ($institucion !== null) {
            $query->where('institucion_id', $institucion->id);
        }

        $departamento = $query->first();

        if ($departamento !== null) {
            return $departamento;
        }

        // Si tenemos institución pero no encontramos el depto en ella, buscar en general
        if ($institucion !== null) {
            $departamento = Departamento::whereRaw(
                'LOWER(nombre) = ?',
                [mb_strtolower($nombreNormalizado, 'UTF-8')]
            )->first();

            if ($departamento !== null) {
                return $departamento;
            }
        }

        // No existe → consultar decisiones
        $decision = $this->decisiones['departamentos'][$nombreOriginal]
            ?? $this->decisiones['departamentos'][$nombreNormalizado]
            ?? null;

        if ($decision !== null && ($decision['accion'] ?? '') === 'vincular' && !empty($decision['id'])) {
            return Departamento::find((int) $decision['id']);
        }

        // Sin decisión o ignorar → null
        return null;
    }

    // ============================================================
    // RESOLUCIÓN: RESPONSABLE
    // ============================================================

    /**
     * El responsable se auto-resuelve siguiendo la misma prioridad
     * que en ActivoController::resolverResponsable():
     *   1. Responsable del departamento
     *   2. Responsable directo de la institución
     *   3. null
     */
    private function resolverResponsable(
        ?Institucion $institucion,
        ?Departamento $departamento
    ): ?Responsable {
        // Prioridad 1: Departamento
        if ($departamento !== null) {
            $responsable = Responsable::where('departamento_id', $departamento->id)
                ->where('activo', true)
                ->first();

            if ($responsable !== null) {
                return $responsable;
            }
        }

        // Prioridad 2: Institución (responsable directo)
        if ($institucion !== null) {
            $responsable = Responsable::where('institucion_id', $institucion->id)
                ->whereNull('departamento_id')
                ->where('activo', true)
                ->first();

            if ($responsable !== null) {
                return $responsable;
            }
        }

        return null;
    }

    // ============================================================
    // NORMALIZACIÓN DE SERIAL
    // ============================================================

    private function normalizarSerial(mixed $valor): string
    {
        if ($valor === null) {
            return '';
        }

        $serial = trim((string) $valor);
        $serial = mb_strtoupper($serial, 'UTF-8');

        // Colapsar espacios internos
        $serial = preg_replace('/\s+/', ' ', $serial) ?? $serial;

        return $serial;
    }

    // ============================================================
    // NORMALIZACIÓN DE FECHAS
    // ============================================================

    private function normalizarFecha(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        // Si ya es un objeto DateTime (PhpSpreadsheet puede devolverlo)
        if ($valor instanceof \DateTimeInterface) {
            return Carbon::instance($valor)->format('Y-m-d');
        }

        // Si es numérico y parece un serial de Excel (fecha de Excel)
        if (is_numeric($valor) && (float) $valor > 25569 && (float) $valor < 100000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $valor)
                    ->format('Y-m-d');
            } catch (\Throwable) {
                // continuar con el string
            }
        }

        $texto = trim((string) $valor);

        if ($texto === '') {
            return null;
        }

        // Intentar cada formato conocido
        foreach (self::FORMATOS_FECHA as $formato) {
            try {
                $fecha = Carbon::createFromFormat($formato, $texto);
                if ($fecha !== false) {
                    return $fecha->format('Y-m-d');
                }
            } catch (\Throwable) {
                // continuar
            }
        }

        // Último intento: Carbon::parse
        try {
            return Carbon::parse($texto)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    // ============================================================
    // NORMALIZACIÓN DE ENTEROS
    // ============================================================

    private function normalizarEntero(mixed $valor): ?int
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_numeric($valor)) {
            $entero = (int) $valor;
            return $entero > 0 ? $entero : null;
        }

        $texto = preg_replace('/[^0-9]/', '', (string) $valor);

        if ($texto === '') {
            return null;
        }

        $entero = (int) $texto;

        return $entero > 0 ? $entero : null;
    }

    // ============================================================
    // LIMPIEZA DE STRINGS
    // ============================================================

    private function limpiar(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        if ($texto === '') {
            return null;
        }

        // Colapsar espacios internos
        $texto = preg_replace('/\s+/', ' ', $texto) ?? $texto;

        return $texto;
    }

    /**
     * Normaliza nombres de entidades (marca, categoría, modelo):
     *   - trim
     *   - Title Case
     *   - quita sufijos corporativos comunes
     */
    private function normalizarNombreEntidad(string $nombre): string
    {
        $nombre = $this->limpiar($nombre) ?? '';

        if ($nombre === '') {
            return '';
        }

        // Quitar sufijos corporativos conocidos (case-insensitive)
        $sufijos = [
            ' Inc.', ' Inc', ' Corporation', ' Corp.', ' Corp',
            ' S.A.', ' S.A', ' C.A.', ' C.A', ' S.A.S.',
            ' Computer', ' Computers', ' Technologies', ' Technology',
            ' Company', ' Co.', ' Co', ' Ltd.', ' Ltd', ' LLC',
        ];

        foreach ($sufijos as $sufijo) {
            if (Str::endsWith(mb_strtolower($nombre, 'UTF-8'), mb_strtolower($sufijo, 'UTF-8'))) {
                $nombre = mb_substr($nombre, 0, mb_strlen($nombre, 'UTF-8') - mb_strlen($sufijo, 'UTF-8'));
                $nombre = trim($nombre);
            }
        }

        // Title Case
        $nombre = mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8');

        return trim($nombre);
    }

    /**
     * Normaliza texto para comparaciones (minúsculas, sin acentos).
     */
    private function normalizarTexto(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $texto = $this->quitarAcentos($texto);
        $texto = preg_replace('/\s+/', ' ', $texto) ?? $texto;

        return $texto;
    }

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

    // ============================================================
    // CONSTRUCCIÓN DE DATOS INCOMPLETOS
    // ============================================================

    /**
     * Recibe un array de resoluciones y construye el JSON de `datos_incompletos`.
     *
     * Formato:
     *   [
     *     'campos_faltantes'  => ['institucion', 'responsable'],
     *     'valores_originales' => [
     *       'institucion' => 'Alcaldía de Nirgua',
     *       'responsable' => null,
     *     ]
     *   ]
     *
     * @param array<string, array{original: mixed, id: mixed, resuelto: bool}> $resoluciones
     * @return array|null  null si no hay campos faltantes
     */
    private function construirDatosIncompletos(array $resoluciones): ?array
    {
        $camposFaltantes = [];
        $valoresOriginales = [];

        foreach ($resoluciones as $campo => $info) {
            if ($info['resuelto'] === true) {
                continue;
            }

            $camposFaltantes[] = $campo;

            if ($info['original'] !== null && $info['original'] !== '') {
                $valoresOriginales[$campo] = $info['original'];
            }
        }

        if (empty($camposFaltantes)) {
            return null;
        }

        return [
            'campos_faltantes'   => $camposFaltantes,
            'valores_originales' => $valoresOriginales,
        ];
    }
}