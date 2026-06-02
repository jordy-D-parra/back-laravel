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
        if (!auth()->user()->hasPermission('ver-solicitudes')) {
            abort(403, 'No tienes permiso para ver solicitudes');
        }

        $user = auth()->user();
        $userId = (int) $user->id;

        $query = Solicitud::with([
            'detalles',
            'institucion',
            'departamento',
            'responsable',
            'usuario'
        ])->where('usuario_id', $userId);

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

        $perPage = $request->input('per_page', 10);

        try {
            $solicitudes = $query->orderBy('created_at', 'desc')
                                ->paginate($perPage)
                                ->appends($request->query());
        } catch (\Exception $e) {
            Log::error('Error en consulta de solicitudes: ' . $e->getMessage());
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
                    'id' => $detalle->id,
                    'tipo_item' => $detalle->tipo_item,
                    'item_descripcion' => $detalle->descripcion_item,
                    'cantidad_solicitada' => $detalle->cantidad_solicitada
                ];
            }

            return response()->json([
                'success' => true,
                'id' => $solicitud->id,
                'tipo_solicitante' => $solicitud->tipo_solicitante,
                'prioridad' => $solicitud->prioridad,
                'estado_solicitud' => $solicitud->estado_solicitud,
                'fecha_solicitud' => $solicitud->fecha_solicitud,
                'fecha_requerida' => $solicitud->fecha_requerida,
                'fecha_fin_estimada' => $solicitud->fecha_fin_estimada,
                'justificacion' => $solicitud->justificacion,
                'observaciones' => $solicitud->observaciones,
                'departamento_id' => $solicitud->departamento_id,
                'institucion_id' => $solicitud->institucion_id,
                'responsable_id' => $solicitud->responsable_id,
                'responsable' => $solicitud->responsable ? [
                    'id' => $solicitud->responsable->id,
                    'nombre' => $solicitud->responsable->nombre,
                    'cargo' => $solicitud->responsable->cargo,
                    'telefono' => $solicitud->responsable->telefono,
                    'email' => $solicitud->responsable->email,
                ] : null,
                'departamento' => $solicitud->departamento ? [
                    'id' => $solicitud->departamento->id,
                    'nombre' => $solicitud->departamento->nombre
                ] : null,
                'institucion' => $solicitud->institucion ? [
                    'id' => $solicitud->institucion->id,
                    'nombre' => $solicitud->institucion->nombre
                ] : null,
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
            if (!auth()->user()->hasPermission('crear-solicitud')) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }

            $userId = (int) auth()->user()->id;

            $validator = validator($request->all(), [
                'tipo_solicitante' => 'required|in:interno,externo',
                'fecha_requerida' => 'required|date|after_or_equal:today',
                'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
                'justificacion' => 'required|string|min:20|max:1000',
                'prioridad' => 'required|in:baja,normal,alta,urgente',
                'observaciones' => 'nullable|string|max:500',
                'responsable_id' => 'nullable|exists:responsables,id',
                'items' => 'required|array|min:1',
                'items.*.tipo_item' => 'required|in:activo,componente',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.item_descripcion' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

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

            $solicitud = Solicitud::create([
                'usuario_id' => $userId,
                'tipo_solicitante' => $request->tipo_solicitante,
                'institucion_id' => $institucionId,
                'departamento_id' => $departamentoId,
                'responsable_id' => $request->responsable_id,
                'oficio_adjunto' => null,
                'fecha_solicitud' => now(),
                'fecha_requerida' => $request->fecha_requerida,
                'fecha_fin_estimada' => $request->fecha_fin_estimada,
                'justificacion' => $request->justificacion,
                'prioridad' => $request->prioridad,
                'estado_solicitud' => 'pendiente',
                'observaciones' => $request->observaciones ?? null,
            ]);

            foreach ($request->items as $item) {
                DetalleSolicitud::create([
                    'solicitud_id' => $solicitud->id,
                    'tipo_item' => $item['tipo_item'],
                    'cantidad_solicitada' => (int) $item['cantidad'],
                    'descripcion_personalizada' => $item['item_descripcion'],
                    'activo_id' => null,
                    'componente_id' => null,
                    'observaciones' => $item['observaciones'] ?? null
                ]);
            }

            DB::commit();

            $solicitudCreada = Solicitud::with(['responsable', 'departamento', 'institucion', 'detalles'])
                ->find($solicitud->id);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud creada exitosamente',
                'solicitud_id' => $solicitud->id,
                'data' => $solicitudCreada
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Verificación de permiso temporalmente comentada para pruebas
        // if (!auth()->user()->hasPermission('editar-solicitud')) {
        //     if ($request->ajax()) {
        //         return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        //     }
        //     abort(403);
        // }

        try {
            $solicitud = Solicitud::findOrFail($id);

            // Verificación temporalmente comentada
            // if ($solicitud->usuario_id !== auth()->id()) {
            //     return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            // }

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
                'responsable_id' => 'nullable|exists:responsables,id',
            ]);

            DB::beginTransaction();

            // Actualizar datos principales
            $solicitud->update([
                'tipo_solicitante' => $validated['tipo_solicitante'],
                'fecha_requerida' => $validated['fecha_requerida'],
                'fecha_fin_estimada' => $validated['fecha_fin_estimada'],
                'justificacion' => $validated['justificacion'],
                'prioridad' => $validated['prioridad'],
                'observaciones' => $validated['observaciones'] ?? null,
                'departamento_id' => $validated['departamento_id'] ?? null,
                'institucion_id' => $validated['institucion_id'] ?? null,
                'responsable_id' => $validated['responsable_id'] ?? null,
            ]);

            // Obtener items del request
            $items = $request->input('items', []);

            if (!empty($items) && is_array($items)) {
                $solicitud->detalles()->delete();

                foreach ($items as $item) {
                    $descripcion = $item['item_descripcion'] ?? '';
                    $cantidad = $item['cantidad'] ?? 0;

                    if (!empty($descripcion) && $cantidad > 0) {
                        DetalleSolicitud::create([
                            'solicitud_id' => $solicitud->id,
                            'tipo_item' => $item['tipo_item'] ?? 'activo',
                            'cantidad_solicitada' => (int) $cantidad,
                            'descripcion_personalizada' => $descripcion,
                            'observaciones' => $item['observaciones'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            $solicitudActualizada = Solicitud::with(['responsable', 'departamento', 'institucion', 'detalles'])
                ->find($solicitud->id);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud actualizada exitosamente',
                    'data' => $solicitudActualizada
                ]);
            }

            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Solicitud actualizada exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
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

    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar solicitudes. Solo administradores.'], 403);
            }
            abort(403);
        }

        try {
            $solicitud = Solicitud::findOrFail($id);

            DB::beginTransaction();

            $solicitud->detalles()->delete();
            $solicitud->delete();

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud eliminada exitosamente'
                ]);
            }

            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Solicitud eliminada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en destroy: ' . $e->getMessage());
            if (request()->ajax()) {
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
                return response()->json(['success' => false, 'message' => 'No tienes permiso para cancelar solicitudes'], 403);
            }
            abort(403);
        }

        try {
            $solicitud = Solicitud::findOrFail($id);

            if ($solicitud->usuario_id !== auth()->id()) {
                return response()->json(['success' => false, 'message' => 'No puedes cancelar una solicitud que no creaste'], 403);
            }

            if (!in_array($solicitud->estado_solicitud, ['pendiente', 'aprobada'])) {
                return response()->json(['success' => false, 'message' => 'No se puede cancelar esta solicitud porque ya fue ' . $solicitud->estado_solicitud], 422);
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
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud aprobada exitosamente'
                ]);
            }

            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Solicitud aprobada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en approve: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
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
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud rechazada exitosamente'
                ]);
            }

            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Solicitud rechazada');

        } catch (\Exception $e) {
            Log::error('Error en reject: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
