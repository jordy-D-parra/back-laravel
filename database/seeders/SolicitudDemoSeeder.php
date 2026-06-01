<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use App\Models\DetalleSolicitud;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Institucion;
use App\Models\Departamento;
use App\Models\Responsable;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SolicitudController extends Controller
{
    public function index(Request $request)
{
    // Verificar permiso
    if (!auth()->user()->hasPermission('ver-solicitudes')) {
        abort(403, 'No tienes permiso para ver solicitudes');
    }

    // Obtener el usuario autenticado y su ID numérico
    $user = auth()->user();
    $userId = (int) $user->id; // Forzar a entero

    // Log para depuración
    \Log::info('=== SOLICITUD INDEX ===');
    \Log::info('Usuario: ' . $user->usuario);
    \Log::info('Usuario ID (entero): ' . $userId);
    \Log::info('Tipo de dato: ' . gettype($userId));

    $query = Solicitud::with([
        'detalles',
        'institucion',
        'departamento',
        'responsable',
        'usuario'
    ])->where('usuario_id', $userId);

    // Filtros
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->whereHas('institucion', function ($sq) use ($search) {
                $sq->where('nombre', 'ILIKE', "%{$search}%");
            })->orWhereHas('departamento', function ($sq) use ($search) {
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

    if ($request->filled('fecha_desde')) {
        $query->whereDate('fecha_requerida', '>=', $request->fecha_desde);
    }

    if ($request->filled('fecha_hasta')) {
        $query->whereDate('fecha_requerida', '<=', $request->fecha_hasta);
    }

    $perPage = $request->input('per_page', 10);

    try {
        $solicitudes = $query->orderBy('created_at', 'desc')
                            ->paginate($perPage)
                            ->appends($request->query());

        \Log::info('Solicitudes encontradas: ' . $solicitudes->total());

    } catch (\Exception $e) {
        \Log::error('Error en consulta de solicitudes: ' . $e->getMessage());
        // Si hay error, devolver paginación vacía
        $solicitudes = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
    }

    if ($request->ajax() || $request->wantsJson()) {
        return response()->json($solicitudes);
    }

    $activos = Activo::with(['modelo.marca', 'estatus'])->get();
    $componentes = Componente::where('estado', 'en_bodega')->get();
    $instituciones = Institucion::where('activo', true)->orderBy('nombre')->get();
    $departamentos = Departamento::where('activo', true)->orderBy('nombre')->get();

    return view('admin.solicitudes.index', compact(
        'solicitudes',
        'activos',
        'componentes',
        'instituciones',
        'departamentos'
    ));
}

    public function getDetalles($id)
    {
        $user = auth()->user();
        if (!$user->hasPermission('ver-solicitudes') && !$user->hasPermission('ver-prestamos')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $solicitud = Solicitud::with([
                'detalles',
                'institucion',
                'departamento',
                'responsable',
                'usuario'
            ])->findOrFail($id);

            if (
                !$user->hasPermission('aprobar-solicitudes')
                && !$user->hasPermission('ver-prestamos')
                && $user->id !== $solicitud->usuario_id
            ) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $detalles = [];
            foreach ($solicitud->detalles as $detalle) {
                $detalles[] = [
                    'tipo_item' => $detalle->tipo_item,
                    'item_descripcion' => $detalle->descripcion_item,
                    'cantidad_solicitada' => $detalle->cantidad_solicitada
                ];
            }

            $nombreResponsable = $solicitud->responsable ? $solicitud->responsable->nombre : 'No especificado';

            return response()->json([
                'id' => $solicitud->id,
                'tipo_solicitante' => $solicitud->tipo_solicitante,
                'prioridad' => $solicitud->prioridad,
                'estado_solicitud' => $solicitud->estado_solicitud,
                'fecha_solicitud' => $solicitud->fecha_solicitud,
                'fecha_requerida' => $solicitud->fecha_requerida,
                'fecha_fin_estimada' => $solicitud->fecha_fin_estimada,
                'justificacion' => $solicitud->justificacion,
                'observaciones' => $solicitud->observaciones,
                'departamento' => $solicitud->departamento ? [
                    'id' => $solicitud->departamento->id,
                    'nombre' => $solicitud->departamento->nombre
                ] : null,
                'institucion' => $solicitud->institucion ? [
                    'id' => $solicitud->institucion->id,
                    'nombre' => $solicitud->institucion->nombre
                ] : null,
                'responsable' => [
                    'nombre' => $nombreResponsable
                ],
                'detalles' => $detalles
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getDetalles: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

  public function store(Request $request)
{
    try {
        // Verificar permiso
        if (!auth()->user()->hasPermission('crear-solicitud')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        // Log de los datos recibidos
        \Log::info('Datos recibidos en store:', $request->all());

        // Validación
        $validator = validator($request->all(), [
            'tipo_solicitante' => 'required|in:interno,externo',
            'fecha_requerida' => 'required|date|after_or_equal:today',
            'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
            'justificacion' => 'required|string|min:20|max:1000',
            'prioridad' => 'required|in:baja,normal,alta,urgente',
            'observaciones' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.tipo_item' => 'required|in:activo,componente',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.item_descripcion' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            \Log::error('Error de validación:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        // Obtener IDs correctamente
        $userId = (int) auth()->id();
        $institucionId = null;
        $departamentoId = null;

        if ($request->tipo_solicitante === 'interno') {
            if ($request->filled('departamento_id') && $request->departamento_id !== 'otro') {
                $departamentoId = (int) $request->departamento_id;
            }
        } else {
            if ($request->filled('institucion_id') && $request->institucion_id !== 'otro') {
                $institucionId = (int) $request->institucion_id;
            }
        }

        // Crear la solicitud
        $solicitud = Solicitud::create([
            'usuario_id' => $userId,
            'tipo_solicitante' => $request->tipo_solicitante,
            'institucion_id' => $institucionId,
            'departamento_id' => $departamentoId,
            'responsable_id' => null,
            'oficio_adjunto' => null,
            'fecha_solicitud' => now(),
            'fecha_requerida' => $request->fecha_requerida,
            'fecha_fin_estimada' => $request->fecha_fin_estimada,
            'justificacion' => $request->justificacion,
            'prioridad' => $request->prioridad,
            'estado_solicitud' => 'pendiente',
            'observaciones' => $request->observaciones ?? null,
        ]);

        \Log::info('Solicitud creada ID: ' . $solicitud->id);

        // Crear los detalles
        foreach ($request->items as $index => $item) {
            DetalleSolicitud::create([
                'solicitud_id' => $solicitud->id,
                'tipo_item' => $item['tipo_item'],
                'cantidad_solicitada' => (int) $item['cantidad'],
                'descripcion_personalizada' => $item['item_descripcion'],
                'activo_id' => null,
                'componente_id' => null,
                'observaciones' => $item['observaciones'] ?? null
            ]);
            \Log::info('Detalle ' . $index . ' creado');
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud creada exitosamente',
            'solicitud_id' => $solicitud->id
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error en store: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Error al crear la solicitud: ' . $e->getMessage()
        ], 500);
    }
}

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('editar-solicitud')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }
            abort(403);
        }

        try {
            $solicitud = Solicitud::findOrFail($id);

            if ($solicitud->usuario_id !== auth()->id()) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }

            if ($solicitud->estado_solicitud !== 'pendiente') {
                return response()->json(['success' => false, 'message' => 'Solo se pueden editar solicitudes pendientes'], 422);
            }

            $validated = $request->validate([
                'tipo_solicitante' => 'required|in:interno,externo',
                'fecha_requerida' => 'required|date',
                'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
                'justificacion' => 'required|string|min:20|max:1000',
                'prioridad' => 'required|in:baja,normal,alta,urgente',
                'observaciones' => 'nullable|string|max:500',
                'departamento_id' => 'nullable|exists:departamentos,id',
                'institucion_id' => 'nullable|exists:instituciones,id',
            ]);

            $solicitud->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud actualizada exitosamente',
                    'solicitud' => $solicitud->fresh()
                ]);
            }

            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Solicitud actualizada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en update: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel($id)
    {
        if (!auth()->user()->hasPermission('cancelar-solicitud')) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }
            abort(403);
        }

        try {
            $solicitud = Solicitud::findOrFail($id);

            if ($solicitud->usuario_id !== auth()->id()) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }

            if (!in_array($solicitud->estado_solicitud, ['pendiente', 'aprobada'])) {
                return response()->json(['success' => false, 'message' => 'No se puede cancelar esta solicitud'], 422);
            }

            $solicitud->update(['estado_solicitud' => 'cancelada']);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud cancelada exitosamente'
                ]);
            }

            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Solicitud cancelada');
        } catch (\Exception $e) {
            Log::error('Error en cancel: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve($id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }
            abort(403);
        }

        try {
            $solicitud = Solicitud::findOrFail($id);

            if ($solicitud->estado_solicitud !== 'pendiente') {
                return response()->json(['success' => false, 'message' => 'Solo se pueden aprobar solicitudes pendientes'], 422);
            }

            DB::beginTransaction();

            $solicitud->update([
                'estado_solicitud' => 'aprobada',
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => now()
            ]);

            DB::commit();

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Solicitud aprobada exitosamente']);
            }

            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Solicitud aprobada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en approve: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }
            abort(403);
        }

        try {
            $solicitud = Solicitud::findOrFail($id);

            if ($solicitud->estado_solicitud !== 'pendiente') {
                return response()->json(['success' => false, 'message' => 'Solo se pueden rechazar solicitudes pendientes'], 422);
            }

            $motivo = $request->input('motivo', 'Rechazada por el administrador');

            $solicitud->update([
                'estado_solicitud' => 'rechazada',
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => now(),
                'observaciones' => $motivo
            ]);

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Solicitud rechazada exitosamente']);
            }

            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Solicitud rechazada');
        } catch (\Exception $e) {
            Log::error('Error en reject: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
