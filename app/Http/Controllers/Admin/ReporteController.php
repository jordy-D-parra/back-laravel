<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Solicitud;
use App\Models\FichaSoporte;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReporteController extends Controller
{
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
        ]);
    }

    // ============================================================
    // REPORTE DE INVENTARIO
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
    // REPORTE DE SOLICITUDES
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
    // REPORTE DE SOPORTE TÉCNICO
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
    // EXPORTAR A PDF (redirige a vista imprimible)
    // ============================================================
    public function exportarPdf(Request $request)
    {
        $seccion = $request->get('seccion', 'inventario');

        // Resolver datos según sección
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

        return view('admin.reportes.pdf-generico', [
            'seccion' => $seccion,
            'titulo' => $titulo,
            'data' => $payload['data'],
        ]);
    }

    // ============================================================
    // HELPER: resolver fechas según filtro
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

            default: // todos
                $inicio = Carbon::create(2000, 1, 1)->startOfDay();
                $fin = now()->endOfDay();
                break;
        }

        return [$inicio, $fin, $tipo];
    }
}