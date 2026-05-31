<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestamo;
use App\Models\Solicitud;
use App\Models\Responsable;
use App\Models\Activo;
use Illuminate\Http\Request;

class PrestamoController extends Controller
{
    /**
     * Vista de gestión de préstamos (refleja solicitudes para el administrador).
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-prestamos')) {
            abort(403, 'No tienes permiso para ver préstamos');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return $this->listarSolicitudes($request);
        }

        return view('admin.prestamo.index');
    }

    /**
     * Lista solicitudes de préstamo (todas, vista operativa de préstamos).
     */
    public function listarSolicitudes(Request $request)
    {
        $query = Solicitud::with([
            'detalles',
            'institucion',
            'departamento',
            'responsable',
            'usuario',
            'prestamos',
        ]);

        if ($request->filled('search') || $request->filled('buscar')) {
            $search = $request->input('search', $request->input('buscar'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('institucion', function ($sq) use ($search) {
                    $sq->where('nombre', 'ILIKE', "%{$search}%");
                })->orWhereHas('departamento', function ($sq) use ($search) {
                    $sq->where('nombre', 'ILIKE', "%{$search}%");
                })->orWhereHas('responsable', function ($sq) use ($search) {
                    $sq->where('nombre', 'ILIKE', "%{$search}%");
                })->orWhere('justificacion', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado_solicitud', $request->estado);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        $perPage = (int) $request->input('per_page', 10);

        return response()->json(
            $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->query())
        );
    }

    /**
     * Retorna préstamos registrados en formato JSON.
     */
    public function listar(Request $request)
    {
        $query = Prestamo::with(['responsable', 'activo', 'solicitud']);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('responsable', function ($sq) use ($buscar) {
                    $sq->where('nombre', 'LIKE', "%{$buscar}%");
                })->orWhereHas('activo', function ($sq) use ($buscar) {
                    $sq->where('serial', 'LIKE', "%{$buscar}%");
                });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('solicitud_id')) {
            $query->where('solicitud_id', $request->solicitud_id);
        }

        return response()->json($query->latest()->get());
    }

    /**
     * Crea préstamos a partir de una solicitud aprobada (ítems con activo asignado).
     */
    public function registrarDesdeSolicitud(Request $request, $solicitudId)
    {
        if (!auth()->user()->hasPermission('crear-prestamo')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $solicitud = Solicitud::with('detalles')->findOrFail($solicitudId);

        if ($solicitud->estado_solicitud !== 'aprobada') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden registrar préstamos desde solicitudes aprobadas',
            ], 422);
        }

        if (!$solicitud->responsable_id) {
            return response()->json([
                'success' => false,
                'message' => 'La solicitud no tiene responsable asignado',
            ], 422);
        }

        $creados = [];
        foreach ($solicitud->detalles as $detalle) {
            if (!$detalle->activo_id) {
                continue;
            }

            $existe = Prestamo::where('solicitud_id', $solicitud->id)
                ->where('activo_id', $detalle->activo_id)
                ->exists();

            if ($existe) {
                continue;
            }

            $creados[] = Prestamo::create([
                'solicitud_id' => $solicitud->id,
                'responsable_id' => $solicitud->responsable_id,
                'activo_id' => $detalle->activo_id,
                'fecha_salida' => $solicitud->fecha_requerida,
                'fecha_devolucion' => $solicitud->fecha_fin_estimada,
                'estado' => 'pendiente',
                'observaciones' => $solicitud->observaciones,
            ]);
        }

        if (empty($creados)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay ítems con activo asignado para registrar préstamos',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => count($creados) . ' préstamo(s) registrado(s)',
            'prestamos' => $creados,
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-prestamo')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $request->validate([
            'solicitud_id' => 'nullable|exists:solicitudes,id',
            'responsable_id' => 'required|exists:responsables,id',
            'activo_id' => 'required|exists:activos,id',
            'fecha_salida' => 'nullable|date',
            'fecha_devolucion' => 'nullable|date|after_or_equal:fecha_salida',
            'estado' => 'required|in:pendiente,entregado,vencido,devuelto',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $prestamo = Prestamo::updateOrCreate(
            ['id' => $request->id],
            $request->only([
                'solicitud_id',
                'responsable_id',
                'activo_id',
                'fecha_salida',
                'fecha_devolucion',
                'estado',
                'observaciones',
            ])
        );

        return response()->json(['success' => true, 'prestamo' => $prestamo->load(['responsable', 'activo', 'solicitud'])]);
    }

    public function show($id)
    {
        $prestamo = Prestamo::with(['responsable', 'activo', 'solicitud.detalles'])->findOrFail($id);
        return response()->json($prestamo);
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('eliminar-prestamo')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $prestamo = Prestamo::findOrFail($id);
        $prestamo->delete();

        return response()->json(['success' => true]);
    }

    public function datosForm()
    {
        return response()->json([
            'responsables' => Responsable::orderBy('nombre')->get(),
            'activos' => Activo::orderBy('serial')->get(),
        ]);
    }
}
