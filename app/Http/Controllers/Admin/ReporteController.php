<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Solicitud;
use App\Models\FichaSoporte;
use App\Models\Prestamo;
use App\Models\Usuario;
use App\Models\Institucion;
use App\Models\Departamento;
use App\Models\Auditoria;
use App\Models\Categoria;
use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteController extends Controller
{
    // ============================================================
    // PÁGINA CENTRAL DE REPORTES
    // ============================================================
    public function index()
    {
        if (!auth()->user()->hasPermission('ver-activos') &&
            !auth()->user()->hasPermission('ver-componentes') &&
            !auth()->user()->hasPermission('ver-solicitudes') &&
            !auth()->user()->hasPermission('ver-fichas-soporte')) {
            abort(403, 'No tienes permiso para ver reportes');
        }

        return view('admin.reportes.index', [
            'totalActivos' => Activo::count(),
            'totalComponentes' => Componente::count(),
            'totalSolicitudes' => Solicitud::count(),
            'totalSoportes' => FichaSoporte::count(),
            'totalPrestamos' => Prestamo::count(),
            'totalUsuarios' => Usuario::count(),
        ]);
    }

    // ============================================================
    // REPORTE DE INVENTARIO (AJAX - EN VIVO)
    // ✅ SIN CAMBIOS
    // ============================================================
    public function inventario(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-activos') &&
            !auth()->user()->hasPermission('ver-componentes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            [$fechaInicio, $fechaFin, $tipo] = $this->resolverFechas($request);

            $queryActivos = Activo::with(['modelo.marca', 'modelo.categoria', 'estatus', 'institucion', 'responsable']);
            $queryComponentes = Componente::with(['activo', 'institucion', 'responsable']);

            if (in_array($tipo, ['dia', 'rango', 'mes', 'fecha'])) {
                $queryActivos->whereBetween('created_at', [$fechaInicio, $fechaFin]);
                $queryComponentes->whereBetween('created_at', [$fechaInicio, $fechaFin]);
            }

            $activos = $queryActivos->orderBy('created_at', 'desc')->get();
            $componentes = $queryComponentes->orderBy('created_at', 'desc')->get();

            $stats = [
                'total_activos' => $activos->count(),
                'total_componentes' => $componentes->count(),
                'disponibles' => $activos->filter(fn($a) => $a->estatus?->descripcion === 'Disponible')->count(),
                'prestados' => $activos->filter(fn($a) => $a->estatus?->descripcion === 'Prestado')->count(),
                'reparacion' => $activos->filter(fn($a) => $a->estatus?->descripcion === 'En reparación')->count(),
                'bodega' => $componentes->filter(fn($c) => $c->estado === 'en_bodega')->count(),
                'instalados' => $componentes->filter(fn($c) => $c->estado === 'instalado')->count(),
            ];

            $porFecha = $activos->groupBy(fn($a) => $a->created_at->format('Y-m-d'))
                ->map(fn($g) => $g->count())
                ->sortKeys();

            return response()->json([
                'success' => true,
                'data' => [
                    'activos' => $activos,
                    'componentes' => $componentes,
                    'stats' => $stats,
                    'por_fecha' => $porFecha,
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_fin' => $fechaFin->format('Y-m-d'),
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // REPORTE DE SOLICITUDES (AJAX - EN VIVO)
    // ✅ SIN CAMBIOS
    // ============================================================
    public function solicitudes(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            [$fechaInicio, $fechaFin, $tipo] = $this->resolverFechas($request);

            $query = Solicitud::with(['usuario.trabajador', 'departamento', 'institucion', 'responsable', 'detalles']);

            if (in_array($tipo, ['dia', 'rango', 'mes', 'fecha'])) {
                $query->whereBetween('fecha_solicitud', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')]);
            }

            $solicitudes = $query->orderBy('fecha_solicitud', 'desc')->get();

            $stats = [
                'total' => $solicitudes->count(),
                'pendientes' => $solicitudes->where('estado_solicitud', 'pendiente')->count(),
                'aprobadas' => $solicitudes->where('estado_solicitud', 'aprobada')->count(),
                'rechazadas' => $solicitudes->where('estado_solicitud', 'rechazada')->count(),
                'canceladas' => $solicitudes->where('estado_solicitud', 'cancelada')->count(),
                'urgentes' => $solicitudes->where('prioridad', 'urgente')->count(),
                'altas' => $solicitudes->where('prioridad', 'alta')->count(),
                'normales' => $solicitudes->where('prioridad', 'normal')->count(),
                'bajas' => $solicitudes->where('prioridad', 'baja')->count(),
            ];

            $porFecha = $solicitudes->groupBy(fn($s) => $s->fecha_solicitud->format('Y-m-d'))
                ->map(fn($g) => $g->count())
                ->sortKeys();

            return response()->json([
                'success' => true,
                'data' => [
                    'solicitudes' => $solicitudes,
                    'stats' => $stats,
                    'por_fecha' => $porFecha,
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_fin' => $fechaFin->format('Y-m-d'),
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // REPORTE DE SOPORTE TÉCNICO (AJAX - EN VIVO)
    // ✅ SIN CAMBIOS
    // ============================================================
    public function soporte(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-fichas-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            [$fechaInicio, $fechaFin, $tipo] = $this->resolverFechas($request);

            $query = FichaSoporte::with(['activo.modelo.marca', 'tecnico.trabajador', 'detalles']);

            if (in_array($tipo, ['dia', 'rango', 'mes', 'fecha'])) {
                $query->whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin]);
            }

            $fichas = $query->orderBy('fecha_ingreso', 'desc')->get();

            $stats = [
                'total' => $fichas->count(),
                'en_proceso' => $fichas->where('estado', 'en_proceso')->count(),
                'finalizados' => $fichas->where('estado', 'finalizado')->count(),
                'con_tecnico' => $fichas->filter(fn($f) => !empty($f->tecnico_id))->count(),
                'sin_tecnico' => $fichas->filter(fn($f) => empty($f->tecnico_id))->count(),
                'vencidas' => $fichas->filter(fn($f) => $f->esta_vencida ?? false)->count(),
            ];

            $porFecha = $fichas->groupBy(fn($f) => $f->fecha_ingreso->format('Y-m-d'))
                ->map(fn($g) => $g->count())
                ->sortKeys();

            $porTecnico = $fichas->groupBy(fn($f) => $f->tecnico_nombre ?? 'Sin asignar')
                ->map(fn($g) => $g->count())
                ->sortDesc()
                ->take(10);

            return response()->json([
                'success' => true,
                'data' => [
                    'fichas' => $fichas,
                    'stats' => $stats,
                    'por_fecha' => $porFecha,
                    'por_tecnico' => $porTecnico,
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_fin' => $fechaFin->format('Y-m-d'),
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // EXPORTAR A PDF (LEGACY)
    // ✅ SIN CAMBIOS
    // ============================================================
    public function exportarPdf(Request $request)
    {
        $seccion = $request->get('seccion', 'inventario');

        $response = match ($seccion) {
            'inventario' => $this->inventario($request),
            'solicitudes' => $this->solicitudes($request),
            'soporte' => $this->soporte($request),
            default => null,
        };

        if (!$response) {
            abort(400, 'Sección no válida');
        }

        $payload = $response->getData(true);
        if (!($payload['success'] ?? false)) {
            abort(403, $payload['message'] ?? 'No autorizado');
        }

        $titulo = match ($seccion) {
            'inventario' => 'Reporte de Inventario',
            'solicitudes' => 'Reporte de Solicitudes',
            'soporte' => 'Reporte de Soporte Técnico',
            default => 'Reporte',
        };

        return view('reportes.pdf-generico', [
            'seccion' => $seccion,
            'titulo' => $titulo,
            'data' => $payload['data'],
        ]);
    }

    // ============================================================
    // HELPER: resolver fechas
    // ✅ SIN CAMBIOS
    // ============================================================
    private function resolverFechas(Request $request): array
    {
        $tipo = $request->get('tipo_filtro', 'todos');

        switch ($tipo) {
            case 'dia':
                $fecha = $request->get('fecha', now()->format('Y-m-d'));
                $inicio = Carbon::parse($fecha)->startOfDay();
                $fin = Carbon::parse($fecha)->endOfDay();
                break;

            case 'mes':
                $mes = (int) $request->get('mes', now()->month);
                $anio = (int) $request->get('anio', now()->year);
                $inicio = Carbon::create($anio, $mes, 1)->startOfMonth();
                $fin = Carbon::create($anio, $mes, 1)->endOfMonth();
                break;

            case 'rango':
            case 'fecha':
                $desde = $request->get('fecha_desde', now()->startOfMonth()->format('Y-m-d'));
                $hasta = $request->get('fecha_hasta', now()->format('Y-m-d'));
                $inicio = Carbon::parse($desde)->startOfDay();
                $fin = Carbon::parse($hasta)->endOfDay();
                break;

            default:
                $inicio = Carbon::create(2000, 1, 1)->startOfDay();
                $fin = now()->endOfDay();
                break;
        }

        return [$inicio, $fin, $tipo];
    }

    // ============================================================
    // ============================================================
    //          NUEVOS REPORTES IMPRIMIBLES
    //          ✅ CAMBIADO: 'reportes.pdf.*' en vez de
    //             'reportes.pdf.*'
    // ============================================================
    // ============================================================

    // ============================================================
    // 1. PRÉSTAMOS POR PERÍODO
    // ============================================================
    public function prestamos(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-prestamos')) {
            abort(403, 'No tienes permiso para ver reportes de préstamos');
        }

        [$inicio, $fin, $tipo] = $this->resolverFechas($request);

        $query = Prestamo::with([
            'departamento:id,nombre',
            'institucion:id,nombre',
            'responsableReceptor:id,nombre,telefono,email',
            'responsableEmisor:id,nombre',
            'detalles.prestable',
        ]);

        if (in_array($tipo, ['dia', 'rango', 'mes', 'fecha'])) {
            $query->whereBetween('fecha_prestamo', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')]);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('departamento_id')) {
            $query->where('departamento_id', $request->departamento_id);
        }

        if ($request->filled('institucion_id')) {
            $query->where('institucion_id', $request->institucion_id);
        }

        $prestamos = $query->orderBy('fecha_prestamo', 'desc')->get();

        $stats = [
            'total' => $prestamos->count(),
            'activos' => $prestamos->whereIn('estado', ['entregado', 'extendido'])->count(),
            'vencidos' => $prestamos->filter(fn($p) => $p->esta_vencido)->count(),
            'devueltos' => $prestamos->where('estado', 'devuelto')->count(),
            'pendientes' => $prestamos->whereIn('estado', ['pendiente', 'aprobado'])->count(),
        ];

        $topDestinos = $prestamos->groupBy(function ($p) {
            return $p->departamento->nombre ?? $p->institucion->nombre ?? 'Sin destino';
        })->map->count()->sortDesc()->take(10);

        $porMes = $prestamos->groupBy(fn($p) => $p->fecha_prestamo->format('Y-m'))
            ->map->count()->sortKeys();

        $filtrosAplicados = $this->construirFiltrosAplicados($request, [
            'estado' => 'Estado',
            'departamento_id' => 'Departamento',
            'institucion_id' => 'Institución',
        ], $inicio, $fin, $tipo);

        return view('reportes.pdf.prestamos-periodo', [
            'titulo' => 'Reporte de Préstamos por Período',
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'usuario_genero' => auth()->user()->nombre_completo ?? auth()->user()->usuario,
            'filtrosAplicados' => $filtrosAplicados,
            'prestamos' => $prestamos,
            'stats' => $stats,
            'topDestinos' => $topDestinos,
            'porMes' => $porMes,
            'fecha_inicio' => $inicio->format('d/m/Y'),
            'fecha_fin' => $fin->format('d/m/Y'),
        ]);
    }

    // ============================================================
    // 2. PRÉSTAMOS VENCIDOS Y POR VENCER
    // ============================================================
    public function prestamosVencidos(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-prestamos')) {
            abort(403, 'No tienes permiso para ver reportes de préstamos');
        }

        $hoy = now()->startOfDay();
        $en7dias = $hoy->copy()->addDays(7);

        $vencidos = Prestamo::with([
            'responsableReceptor:id,nombre,telefono,email',
            'departamento:id,nombre',
            'institucion:id,nombre',
            'detalles.prestable',
        ])
            ->whereIn('estado', ['entregado', 'extendido'])
            ->whereNull('fecha_devolucion_real')
            ->whereDate('fecha_devolucion_esperada', '<', $hoy->format('Y-m-d'))
            ->orderBy('fecha_devolucion_esperada', 'asc')
            ->get();

        $porVencer = Prestamo::with([
            'responsableReceptor:id,nombre,telefono,email',
            'departamento:id,nombre',
            'institucion:id,nombre',
            'detalles.prestable',
        ])
            ->whereIn('estado', ['entregado', 'extendido'])
            ->whereNull('fecha_devolucion_real')
            ->whereBetween('fecha_devolucion_esperada', [$hoy->format('Y-m-d'), $en7dias->format('Y-m-d')])
            ->orderBy('fecha_devolucion_esperada', 'asc')
            ->get();

        return view('reportes.pdf.prestamos-vencidos', [
            'titulo' => 'Reporte de Préstamos Vencidos y Por Vencer',
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'usuario_genero' => auth()->user()->nombre_completo ?? auth()->user()->usuario,
            'filtrosAplicados' => 'Fecha de generación: ' . now()->format('d/m/Y'),
            'vencidos' => $vencidos,
            'porVencer' => $porVencer,
        ]);
    }

    // ============================================================
    // 3. INVENTARIO COMPLETO CON COMPONENTES
    // ============================================================
    public function inventarioCompleto(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-activos') && !auth()->user()->hasPermission('ver-componentes')) {
            abort(403, 'No tienes permiso para ver reportes de inventario');
        }

        [$inicio, $fin, $tipo] = $this->resolverFechas($request);

        $query = Activo::with([
            'modelo.marca:id,nombre',
            'modelo.categoria:id,nombre',
            'estatus:id,descripcion',
            'institucion:id,nombre',
            'departamento:id,nombre',
            'responsable:id,nombre',
            'componentes',
        ]);

        if ($request->filled('categoria_id')) {
            $query->whereHas('modelo', fn($q) => $q->where('categoria_id', $request->categoria_id));
        }
        if ($request->filled('marca_id')) {
            $query->whereHas('modelo', fn($q) => $q->where('marca_id', $request->marca_id));
        }
        if ($request->filled('estado')) {
            $query->whereHas('estatus', fn($q) => $q->where('descripcion', $request->estado));
        }

        $activos = $query->orderBy('serial')->get();

        $stats = [
            'total_activos' => $activos->count(),
            'total_componentes' => $activos->sum(fn($a) => $a->componentes->count()),
            'disponibles' => $activos->filter(fn($a) => $a->estatus?->descripcion === 'Disponible')->count(),
            'prestados' => $activos->filter(fn($a) => $a->estatus?->descripcion === 'Prestado')->count(),
            'reparacion' => $activos->filter(fn($a) => $a->estatus?->descripcion === 'En reparación')->count(),
        ];

        $porCategoria = $activos->groupBy(fn($a) => $a->modelo?->categoria?->nombre ?? 'Sin categoría')
            ->map->count()->sortDesc();

        $filtrosAplicados = $this->construirFiltrosAplicados($request, [
            'categoria_id' => 'Categoría',
            'marca_id' => 'Marca',
            'estado' => 'Estado',
        ], $inicio, $fin, $tipo);

        return view('reportes.pdf.inventario-completo', [
            'titulo' => 'Reporte de Inventario Completo',
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'usuario_genero' => auth()->user()->nombre_completo ?? auth()->user()->usuario,
            'filtrosAplicados' => $filtrosAplicados,
            'activos' => $activos,
            'stats' => $stats,
            'porCategoria' => $porCategoria,
            'orientacion' => 'letter landscape',
        ]);
    }

    // ============================================================
    // 4. SOLICITUDES DETALLADAS (PDF)
    // ============================================================
    public function solicitudesDetalle(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-solicitudes')) {
            abort(403, 'No tienes permiso para ver reportes de solicitudes');
        }

        [$inicio, $fin, $tipo] = $this->resolverFechas($request);

        $query = Solicitud::with([
            'usuario.trabajador',
            'departamento:id,nombre',
            'institucion:id,nombre',
            'responsable:id,nombre',
            'detalles',
            'aprobadoPor:id,usuario',
        ]);

        if (in_array($tipo, ['dia', 'rango', 'mes', 'fecha'])) {
            $query->whereBetween('fecha_solicitud', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')]);
        }

        if ($request->filled('estado')) {
            $query->where('estado_solicitud', $request->estado);
        }
        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        $solicitudes = $query->orderBy('fecha_solicitud', 'desc')->get();

        $stats = [
            'total' => $solicitudes->count(),
            'pendientes' => $solicitudes->where('estado_solicitud', 'pendiente')->count(),
            'aprobadas' => $solicitudes->where('estado_solicitud', 'aprobada')->count(),
            'rechazadas' => $solicitudes->where('estado_solicitud', 'rechazada')->count(),
            'canceladas' => $solicitudes->where('estado_solicitud', 'cancelada')->count(),
            'urgentes' => $solicitudes->where('prioridad', 'urgente')->count(),
        ];

        $aprobadas = $solicitudes->whereNotNull('fecha_aprobacion');
        $promedioDias = $aprobadas->count() > 0
            ? round($aprobadas->avg(fn($s) => $s->fecha_solicitud->diffInDays($s->fecha_aprobacion)), 1)
            : 0;

        $porPrioridad = $solicitudes->groupBy('prioridad')->map->count();
        $porEstado = $solicitudes->groupBy('estado_solicitud')->map->count();

        $filtrosAplicados = $this->construirFiltrosAplicados($request, [
            'estado' => 'Estado',
            'prioridad' => 'Prioridad',
        ], $inicio, $fin, $tipo);

        return view('reportes.pdf.solicitudes-detalle', [
            'titulo' => 'Reporte de Solicitudes de Préstamo',
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'usuario_genero' => auth()->user()->nombre_completo ?? auth()->user()->usuario,
            'filtrosAplicados' => $filtrosAplicados,
            'solicitudes' => $solicitudes,
            'stats' => $stats,
            'promedioDias' => $promedioDias,
            'porPrioridad' => $porPrioridad,
            'porEstado' => $porEstado,
            'fecha_inicio' => $inicio->format('d/m/Y'),
            'fecha_fin' => $fin->format('d/m/Y'),
        ]);
    }

    // ============================================================
    // 5. SOPORTE TÉCNICO DETALLADO (PDF)
    // ============================================================
    public function soporteDetalle(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-fichas-soporte')) {
            abort(403, 'No tienes permiso para ver reportes de soporte');
        }

        [$inicio, $fin, $tipo] = $this->resolverFechas($request);

        $query = FichaSoporte::with([
            'activo.modelo.marca',
            'activo.modelo.categoria',
            'activo.institucion',
            'tecnico.trabajador',
            'detalles.componente',
        ]);

        if (in_array($tipo, ['dia', 'rango', 'mes', 'fecha'])) {
            $query->whereBetween('fecha_ingreso', [$inicio, $fin]);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('tecnico_id')) {
            $query->where('tecnico_id', $request->tecnico_id);
        }

        $fichas = $query->orderBy('fecha_ingreso', 'desc')->get();

        $stats = [
            'total' => $fichas->count(),
            'en_proceso' => $fichas->where('estado', 'en_proceso')->count(),
            'finalizados' => $fichas->where('estado', 'finalizado')->count(),
            'vencidas' => $fichas->filter(fn($f) => $f->esta_vencida)->count(),
            'con_tecnico' => $fichas->filter(fn($f) => $f->tecnico_id)->count(),
        ];

        $finalizadas = $fichas->where('estado', 'finalizado')->whereNotNull('fecha_salida');
        $promedioDias = $finalizadas->count() > 0
            ? round($finalizadas->avg(fn($f) => $f->fecha_ingreso->diffInDays($f->fecha_salida)), 1)
            : 0;

        $porTecnico = $fichas->groupBy(fn($f) => $f->tecnico_nombre ?? 'Sin asignar')
            ->map(fn($g) => [
                'total' => $g->count(),
                'finalizadas' => $g->where('estado', 'finalizado')->count(),
                'en_proceso' => $g->where('estado', 'en_proceso')->count(),
            ])->sortByDesc('total');

        $filtrosAplicados = $this->construirFiltrosAplicados($request, [
            'estado' => 'Estado',
            'tecnico_id' => 'Técnico',
        ], $inicio, $fin, $tipo);

        return view('reportes.pdf.soporte-detalle', [
            'titulo' => 'Reporte de Soporte Técnico',
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'usuario_genero' => auth()->user()->nombre_completo ?? auth()->user()->usuario,
            'filtrosAplicados' => $filtrosAplicados,
            'fichas' => $fichas,
            'stats' => $stats,
            'promedioDias' => $promedioDias,
            'porTecnico' => $porTecnico,
            'fecha_inicio' => $inicio->format('d/m/Y'),
            'fecha_fin' => $fin->format('d/m/Y'),
        ]);
    }

    // ============================================================
    // 6. ACTIVOS POR ENTIDAD
    // ============================================================
    public function activosPorEntidad(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-activos')) {
            abort(403, 'No tienes permiso para ver reportes de activos');
        }

        $instituciones = Institucion::with([
            'departamentos' => function ($q) {
                $q->with(['responsables', 'activos.modelo.marca', 'activos.estatus']);
            },
        ])->where('activo', true)->orderBy('nombre')->get();

        $stats = [
            'total_instituciones' => $instituciones->count(),
            'total_departamentos' => $instituciones->sum(fn($i) => $i->departamentos->count()),
            'total_activos' => $instituciones->sum(fn($i) => $i->departamentos->sum(fn($d) => $d->activos->count())),
        ];

        return view('reportes.pdf.activos-por-entidad', [
            'titulo' => 'Reporte de Activos por Institución y Departamento',
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'usuario_genero' => auth()->user()->nombre_completo ?? auth()->user()->usuario,
            'filtrosAplicados' => 'Solo instituciones activas',
            'instituciones' => $instituciones,
            'stats' => $stats,
            'orientacion' => 'letter landscape',
        ]);
    }

    // ============================================================
    // 7. KARDEX DE UN ACTIVO
    // ============================================================
    public function kardex(Request $request, $activoId)
    {
        if (!auth()->user()->hasPermission('ver-activos')) {
            abort(403, 'No tienes permiso para ver reportes de activos');
        }

        $activo = Activo::with([
            'modelo.marca',
            'modelo.categoria',
            'estatus',
            'institucion',
            'departamento',
            'responsable',
            'componentes',
        ])->findOrFail($activoId);

        $prestamos = Prestamo::whereHas('detalles', function ($q) use ($activoId) {
            $q->where('prestable_type', Activo::class)
              ->where('prestable_id', $activoId);
        })
            ->with(['responsableReceptor:id,nombre', 'departamento:id,nombre', 'institucion:id,nombre'])
            ->orderBy('fecha_prestamo', 'desc')
            ->get();

        $fichas = FichaSoporte::where('activo_id', $activoId)
            ->with('tecnico.trabajador')
            ->orderBy('fecha_ingreso', 'desc')
            ->get();

        return view('reportes.pdf.kardex', [
            'titulo' => 'Kardex de Activo - ' . $activo->serial,
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'usuario_genero' => auth()->user()->nombre_completo ?? auth()->user()->usuario,
            'filtrosAplicados' => 'Activo: ' . $activo->serial,
            'activo' => $activo,
            'prestamos' => $prestamos,
            'fichas' => $fichas,
        ]);
    }

    // ============================================================
    // 8. USUARIOS Y ROLES
    // ============================================================
    public function usuarios(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-usuarios')) {
            abort(403, 'No tienes permiso para ver reportes de usuarios');
        }

        $usuarios = Usuario::with(['trabajador', 'rol'])
            ->orderBy('usuario')->get();

        $stats = [
            'total' => $usuarios->count(),
            'activos' => $usuarios->where('status', 'activo')->count(),
            'inactivos' => $usuarios->where('status', 'inactivo')->count(),
            'pendientes_cambio' => $usuarios->where('must_change_password', true)->count(),
            'nunca_logeado' => $usuarios->whereNull('ultimo_login')->count(),
        ];

        $porRol = $usuarios->groupBy(fn($u) => $u->rol->nombre ?? 'Sin rol')
            ->map->count()->sortDesc();

        return view('reportes.pdf.usuarios-detalle', [
            'titulo' => 'Reporte de Usuarios y Roles',
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'usuario_genero' => auth()->user()->nombre_completo ?? auth()->user()->usuario,
            'filtrosAplicados' => 'Todos los usuarios',
            'usuarios' => $usuarios,
            'stats' => $stats,
            'porRol' => $porRol,
        ]);
    }

    // ============================================================
    // 9. AUDITORÍA
    // ============================================================
    public function auditoria(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-auditoria')) {
            abort(403, 'No tienes permiso para ver reportes de auditoría');
        }

        [$inicio, $fin, $tipo] = $this->resolverFechas($request);

        $query = Auditoria::with('usuario.trabajador');

        if (in_array($tipo, ['dia', 'rango', 'mes', 'fecha'])) {
            $query->whereBetween('created_at', [$inicio, $fin]);
        }
        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }
        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        $registros = $query->orderBy('created_at', 'desc')->limit(500)->get();

        $stats = [
            'total' => $registros->count(),
            'creaciones' => $registros->where('accion', 'crear')->count(),
            'ediciones' => $registros->where('accion', 'editar')->count(),
            'eliminaciones' => $registros->where('accion', 'eliminar')->count(),
            'logins' => $registros->where('accion', 'login')->count(),
        ];

        $porModulo = $registros->groupBy('modulo')->map->count()->sortDesc();

        $filtrosAplicados = $this->construirFiltrosAplicados($request, [
            'modulo' => 'Módulo',
            'accion' => 'Acción',
        ], $inicio, $fin, $tipo);

        return view('reportes.pdf.auditoria', [
            'titulo' => 'Reporte de Auditoría',
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'usuario_genero' => auth()->user()->nombre_completo ?? auth()->user()->usuario,
            'filtrosAplicados' => $filtrosAplicados,
            'registros' => $registros,
            'stats' => $stats,
            'porModulo' => $porModulo,
            'orientacion' => 'letter landscape',
        ]);
    }

    // ============================================================
    // HELPER: Construir texto de filtros aplicados
    // ============================================================
    private function construirFiltrosAplicados(Request $request, array $etiquetas, Carbon $inicio, Carbon $fin, string $tipo): string
    {
        $aplicados = [];

        foreach ($etiquetas as $campo => $etiqueta) {
            if ($request->filled($campo)) {
                $aplicados[] = $etiqueta . ': ' . $request->$campo;
            }
        }

        $rangoTexto = match ($tipo) {
            'dia' => 'Fecha: ' . $inicio->format('d/m/Y'),
            'mes' => 'Mes: ' . $inicio->format('m/Y'),
            'rango', 'fecha' => 'Desde: ' . $inicio->format('d/m/Y') . ' hasta: ' . $fin->format('d/m/Y'),
            default => 'Todos los registros',
        };

        array_unshift($aplicados, $rangoTexto);

        return implode(' | ', $aplicados);
    }
}