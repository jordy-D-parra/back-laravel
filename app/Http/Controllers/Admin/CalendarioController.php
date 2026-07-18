<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestamo;
use App\Models\Solicitud;
use Illuminate\Http\Request;

class CalendarioController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-prestamos')) {
            abort(403, 'No tienes permiso para ver el calendario');
        }

        // Estadísticas
        $totalPrestamos = Prestamo::count();
        $prestamosActivos = Prestamo::whereIn('estado', ['entregado', 'extendido'])->count();
        $prestamosVencidos = Prestamo::whereIn('estado', ['entregado', 'extendido'])
            ->where('fecha_devolucion_esperada', '<', now()->format('Y-m-d'))
            ->count();
        $solicitudesPendientes = Solicitud::where('estado_solicitud', 'pendiente')->count();
        $prestamosDevueltos = Prestamo::where('estado', 'devuelto')->count();

        // Préstamos para el timeline
        $timelinePrestamos = Prestamo::with([
            'responsableReceptor',
            'responsableEmisor',
            'departamento',
            'institucion',
            'detalles.prestable'
        ])
        ->whereIn('estado', ['entregado', 'extendido', 'devuelto'])
        ->orderBy('fecha_prestamo', 'desc')
        ->limit(50)
        ->get();

        // Agrupar por fecha para el timeline
        $eventosPorFecha = [];
        foreach ($timelinePrestamos as $prestamo) {
            $fechaKey = $prestamo->fecha_prestamo->format('Y-m-d');
            if (!isset($eventosPorFecha[$fechaKey])) {
                $eventosPorFecha[$fechaKey] = [
                    'fecha' => $prestamo->fecha_prestamo,
                    'eventos' => collect()
                ];
            }
            $eventosPorFecha[$fechaKey]['eventos']->push($prestamo);
        }

        krsort($eventosPorFecha);

        // Préstamos para Kanban agrupados por estado
        $kanbanData = [
            'pendiente' => Prestamo::with(['responsableReceptor', 'departamento', 'institucion'])
                ->where('estado', 'pendiente')
                ->orderBy('created_at', 'desc')
                ->get(),
            'aprobado' => Prestamo::with(['responsableReceptor', 'departamento', 'institucion'])
                ->where('estado', 'aprobado')
                ->orderBy('created_at', 'desc')
                ->get(),
            'entregado' => Prestamo::with(['responsableReceptor', 'departamento', 'institucion'])
                ->where('estado', 'entregado')
                ->orderBy('created_at', 'desc')
                ->get(),
            'extendido' => Prestamo::with(['responsableReceptor', 'departamento', 'institucion'])
                ->where('estado', 'extendido')
                ->orderBy('created_at', 'desc')
                ->get(),
            'devuelto' => Prestamo::with(['responsableReceptor', 'departamento', 'institucion'])
                ->where('estado', 'devuelto')
                ->orderBy('created_at', 'desc')
                ->get(),
        ];

        return view('admin.calendario.index', compact(
            'totalPrestamos',
            'prestamosActivos',
            'prestamosVencidos',
            'solicitudesPendientes',
            'prestamosDevueltos',
            'eventosPorFecha',
            'timelinePrestamos',
            'kanbanData'
        ));
    }

    public function getEventos(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-prestamos')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $eventos = [];
        $limite = 500;

        $prestamos = Prestamo::with([
            'responsableReceptor',
            'responsableEmisor',
            'departamento',
            'institucion',
            'detalles.prestable'
        ])
        ->whereIn('estado', ['aprobado', 'entregado', 'extendido'])
        ->limit($limite)
        ->get();

        foreach ($prestamos as $prestamo) {
            $color = '#1e3c72';
            $textColor = '#ffffff';
            
            if ($prestamo->esta_vencido) {
                $color = '#dc3545';
            } elseif ($prestamo->estado === 'extendido') {
                $color = '#ffc107';
                $textColor = '#000000';
            } elseif ($prestamo->estado === 'entregado') {
                $color = '#28a745';
            }

            $eventos[] = [
                'id' => 'prestamo-' . $prestamo->id,
                'title' => $prestamo->codigo,
                'start' => $prestamo->fecha_prestamo->format('Y-m-d'),
                'end' => $prestamo->fecha_devolucion_esperada->format('Y-m-d'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => $textColor,
                'extendedProps' => [
                    'tipo' => 'prestamo',
                    'codigo' => $prestamo->codigo,
                    'estado' => $prestamo->estado,
                    'destino' => $prestamo->destino_nombre,
                    'responsable' => $prestamo->responsableReceptor?->nombre ?? 'Sin responsable',
                    'esta_vencido' => $prestamo->esta_vencido,
                    'dias_restantes' => $prestamo->dias_restantes
                ]
            ];
        }

        $solicitudes = Solicitud::with([
            'departamento',
            'institucion',
            'responsable'
        ])
        ->where('estado_solicitud', 'pendiente')
        ->orWhere('estado_solicitud', 'aprobada')
        ->limit($limite)
        ->get();

        foreach ($solicitudes as $solicitud) {
            $eventos[] = [
                'id' => 'solicitud-' . $solicitud->id,
                'title' => 'SOL-' . ($solicitud->codigo ?? $solicitud->id),
                'start' => $solicitud->fecha_solicitud->format('Y-m-d'),
                'end' => $solicitud->fecha_requerida?->format('Y-m-d') ?? $solicitud->fecha_solicitud->format('Y-m-d'),
                'backgroundColor' => '#6c757d',
                'borderColor' => '#6c757d',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'tipo' => 'solicitud',
                    'estado' => $solicitud->estado_solicitud,
                    'prioridad' => $solicitud->prioridad,
                    'entidad' => $solicitud->nombre_entidad
                ]
            ];
        }

        return response()->json($eventos);
    }

    public function getEventoDetalle(Request $request)
    {
        $tipo = $request->tipo;
        $id = $request->id;

        if ($tipo === 'prestamo') {
            $prestamo = Prestamo::with([
                'responsableReceptor',
                'responsableEmisor',
                'departamento',
                'institucion',
                'detalles.prestable'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'tipo' => 'prestamo',
                    'codigo' => $prestamo->codigo,
                    'estado' => $prestamo->estado,
                    'destino' => $prestamo->destino_nombre,
                    'fecha_prestamo' => $prestamo->fecha_prestamo->format('d/m/Y'),
                    'fecha_devolucion_esperada' => $prestamo->fecha_devolucion_esperada->format('d/m/Y'),
                    'fecha_devolucion_real' => $prestamo->fecha_devolucion_real?->format('d/m/Y'),
                    'responsable_receptor' => $prestamo->responsableReceptor?->nombre,
                    'responsable_emisor' => $prestamo->responsableEmisor?->nombre,
                    'observaciones' => $prestamo->observaciones,
                    'items' => $prestamo->detalles->map(function($detalle) {
                        return [
                            'nombre' => $detalle->nombre_item,
                            'cantidad' => $detalle->cantidad,
                            'estado_entrega' => $detalle->estado_entrega
                        ];
                    })
                ]
            ]);
        }

        if ($tipo === 'solicitud') {
            $solicitud = Solicitud::with([
                'departamento',
                'institucion',
                'responsable'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'tipo' => 'solicitud',
                    'estado' => $solicitud->estado_solicitud,
                    'prioridad' => $solicitud->prioridad,
                    'entidad' => $solicitud->nombre_entidad,
                    'fecha_solicitud' => $solicitud->fecha_solicitud->format('d/m/Y'),
                    'fecha_requerida' => $solicitud->fecha_requerida?->format('d/m/Y'),
                    'responsable' => $solicitud->responsable?->nombre,
                    'justificacion' => $solicitud->justificacion
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Evento no encontrado'], 404);
    }
}