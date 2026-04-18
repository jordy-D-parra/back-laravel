<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Prestamo;
use App\Models\Activo;
use App\Models\Periferico;
use App\Models\FichaSoporte;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{


    public function index()
    {
        // Obtener datos para los filtros
        $usuarios = User::where('activo', true)->get();
        $tecnicos = User::whereHas('rol', function($q) {
            $q->whereIn('nombre', ['tecnico', 'super_admin', 'admin']);
        })->get();

        // Estadísticas rápidas
        $stats = [
            'solicitudes_pendientes' => Solicitud::where('estado_solicitud', 'pendiente')->count(),
            'prestamos_activos' => Prestamo::where('estado_prestamo', 'activo')->count(),
            'equipos_disponibles' => Activo::where('cantidad', '>', 0)->sum('cantidad'),
            'soportes_pendientes' => FichaSoporte::where('estado', 'pendiente')->count(),
        ];

        return view('reportes.index', compact('usuarios', 'tecnicos', 'stats'));
    }

    /**
     * Reporte de solicitudes por período
     */
    public function solicitudesPeriodo(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'estado' => 'nullable|string'
        ]);

        $query = Solicitud::with(['solicitante', 'institucion'])
            ->whereBetween('fecha_solicitud', [$request->fecha_inicio, $request->fecha_fin]);

        if ($request->estado && $request->estado != 'todos') {
            $query->where('estado_solicitud', $request->estado);
        }

        $solicitudes = $query->orderBy('fecha_solicitud', 'desc')->get();

        $data = [
            'titulo' => 'Reporte de Solicitudes',
            'subtitulo' => "Período: " . date('d/m/Y', strtotime($request->fecha_inicio)) . " al " . date('d/m/Y', strtotime($request->fecha_fin)),
            'solicitudes' => $solicitudes,
            'fecha_generacion' => now(),
            'usuario_genero' => auth()->user()->name
        ];

        $pdf = Pdf::loadView('reportes.pdf.solicitudes', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('solicitudes_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Reporte de préstamos activos
     */
    public function prestamosActivos(Request $request)
    {
        $prestamos = Prestamo::with(['solicitud.solicitante', 'detalles.activo', 'detalles.periferico'])
            ->where('estado_prestamo', 'activo')
            ->orderBy('fecha_retorno_estimada', 'asc')
            ->get();

        $vencidos = $prestamos->filter(function($p) {
            return $p->fecha_retorno_estimada < now()->toDateString();
        });

        $data = [
            'titulo' => 'Reporte de Préstamos Activos',
            'prestamos' => $prestamos,
            'vencidos' => $vencidos,
            'total' => $prestamos->count(),
            'fecha_generacion' => now(),
            'usuario_genero' => auth()->user()->name
        ];

        $pdf = Pdf::loadView('reportes.pdf.prestamos_activos', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('prestamos_activos_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Reporte de inventario general
     */
    public function inventarioGeneral(Request $request)
    {
        $activos = Activo::with(['estatus', 'tipoActivo'])
            ->orderBy('tipo_equipo')
            ->orderBy('marca_modelo')
            ->get();

        $perifericos = Periferico::orderBy('tipo')->orderBy('nombre')->get();

        $resumen = [
            'total_activos' => $activos->sum('cantidad'),
            'total_perifericos' => $perifericos->sum('cantidad_total'),
            'activos_disponibles' => $activos->where('id_estatus', 1)->sum('cantidad'),
            'activos_prestados' => $activos->where('id_estatus', 2)->sum('cantidad'),
            'valor_total' => $activos->sum('valor_compra'),
        ];

        $data = [
            'titulo' => 'Reporte de Inventario General',
            'activos' => $activos,
            'perifericos' => $perifericos,
            'resumen' => $resumen,
            'fecha_generacion' => now(),
            'usuario_genero' => auth()->user()->name
        ];

        $pdf = Pdf::loadView('reportes.pdf.inventario', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('inventario_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Reporte de equipos disponibles
     */
    public function equiposDisponibles(Request $request)
    {
        $activos = Activo::where('id_estatus', 1)
            ->where('cantidad', '>', 0)
            ->orderBy('tipo_equipo')
            ->get();

        $perifericos = Periferico::where('cantidad_disponible', '>', 0)
            ->orderBy('tipo')
            ->get();

        $data = [
            'titulo' => 'Reporte de Equipos Disponibles',
            'activos' => $activos,
            'perifericos' => $perifericos,
            'fecha_generacion' => now(),
            'usuario_genero' => auth()->user()->name
        ];

        $pdf = Pdf::loadView('reportes.pdf.equipos_disponibles', $data);

        return $pdf->download('equipos_disponibles_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Acta de préstamo
     */
    public function actaPrestamo(Request $request)
    {
        $request->validate([
            'prestamo_id' => 'required|exists:prestamo,id'
        ]);

        $prestamo = Prestamo::with(['solicitud.solicitante', 'solicitud.institucion', 'detalles.activo', 'detalles.periferico', 'tecnico', 'responsable'])
            ->find($request->prestamo_id);

        $data = [
            'prestamo' => $prestamo,
            'fecha_generacion' => now(),
            'usuario_genero' => auth()->user()->name
        ];

        $pdf = Pdf::loadView('reportes.pdf.acta_prestamo', $data);
        $pdf->setPaper('letter');

        return $pdf->download('acta_prestamo_' . $prestamo->id . '.pdf');
    }

    /**
     * Acta de devolución
     */
    public function actaDevolucion(Request $request)
    {
        $request->validate([
            'prestamo_id' => 'required|exists:prestamo,id'
        ]);

        $prestamo = Prestamo::with(['solicitud.solicitante', 'detalles.activo', 'detalles.periferico'])
            ->find($request->prestamo_id);

        $data = [
            'prestamo' => $prestamo,
            'fecha_generacion' => now(),
            'usuario_genero' => auth()->user()->name
        ];

        $pdf = Pdf::loadView('reportes.pdf.acta_devolucion', $data);
        $pdf->setPaper('letter');

        return $pdf->download('acta_devolucion_' . $prestamo->id . '.pdf');
    }

    /**
     * Reporte de soporte/mantenimiento
     */
    public function soportePeriodo(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'estado' => 'nullable|string'
        ]);

        $query = FichaSoporte::with(['activo', 'tecnico', 'usuarioReporta'])
            ->whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin]);

        if ($request->estado && $request->estado != 'todos') {
            $query->where('estado', $request->estado);
        }

        $fichas = $query->orderBy('created_at', 'desc')->get();

        $data = [
            'titulo' => 'Reporte de Soporte Técnico',
            'subtitulo' => "Período: " . date('d/m/Y', strtotime($request->fecha_inicio)) . " al " . date('d/m/Y', strtotime($request->fecha_fin)),
            'fichas' => $fichas,
            'fecha_generacion' => now(),
            'usuario_genero' => auth()->user()->name
        ];

        $pdf = Pdf::loadView('reportes.pdf.soporte', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('soporte_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Reporte de usuarios activos
     */
    public function usuariosActivos(Request $request)
    {
        $usuarios = User::with('rol')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $data = [
            'titulo' => 'Reporte de Usuarios Activos',
            'usuarios' => $usuarios,
            'fecha_generacion' => now(),
            'usuario_genero' => auth()->user()->name
        ];

        $pdf = Pdf::loadView('reportes.pdf.usuarios', $data);

        return $pdf->download('usuarios_activos_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Resumen ejecutivo (Dashboard en PDF)
     */
    public function resumenEjecutivo(Request $request)
    {
        // Solicitudes por mes (últimos 12 meses)
        $solicitudesPorMes = Solicitud::select(
            DB::raw('EXTRACT(YEAR FROM fecha_solicitud) as año'),
            DB::raw('EXTRACT(MONTH FROM fecha_solicitud) as mes'),
            DB::raw('COUNT(*) as total')
        )
        ->where('fecha_solicitud', '>=', now()->subMonths(12))
        ->groupBy('año', 'mes')
        ->orderBy('año', 'desc')
        ->orderBy('mes', 'desc')
        ->get();

        // Préstamos por mes
        $prestamosPorMes = Prestamo::select(
            DB::raw('EXTRACT(YEAR FROM fecha_prestamo) as año'),
            DB::raw('EXTRACT(MONTH FROM fecha_prestamo) as mes'),
            DB::raw('COUNT(*) as total')
        )
        ->where('fecha_prestamo', '>=', now()->subMonths(12))
        ->groupBy('año', 'mes')
        ->orderBy('año', 'desc')
        ->orderBy('mes', 'desc')
        ->get();

        // Top solicitantes
        $topSolicitantes = Solicitud::select('id_solicitante', DB::raw('COUNT(*) as total'))
            ->with('solicitante')
            ->groupBy('id_solicitante')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Top equipos prestados
        $topEquipos = DB::table('detalle_prestamo')
            ->select('tipo_item', 'item_id', DB::raw('SUM(cantidad) as total'))
            ->groupBy('tipo_item', 'item_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        $stats = [
            'total_solicitudes' => Solicitud::count(),
            'total_prestamos' => Prestamo::count(),
            'total_usuarios' => User::where('activo', true)->count(),
            'total_equipos' => Activo::sum('cantidad') + Periferico::sum('cantidad_total'),
            'solicitudes_pendientes' => Solicitud::where('estado_solicitud', 'pendiente')->count(),
            'prestamos_activos' => Prestamo::where('estado_prestamo', 'activo')->count(),
        ];

        $data = [
            'titulo' => 'Resumen Ejecutivo',
            'stats' => $stats,
            'solicitudesPorMes' => $solicitudesPorMes,
            'prestamosPorMes' => $prestamosPorMes,
            'topSolicitantes' => $topSolicitantes,
            'topEquipos' => $topEquipos,
            'fecha_generacion' => now(),
            'usuario_genero' => auth()->user()->name
        ];

        $pdf = Pdf::loadView('reportes.pdf.resumen_ejecutivo', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('resumen_ejecutivo_' . date('Ymd_His') . '.pdf');
    }
}
