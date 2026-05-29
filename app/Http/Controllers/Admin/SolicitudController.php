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

class SolicitudController extends Controller
{
    public function index(Request $request)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('ver-solicitudes')) {
            abort(403, 'No tienes permiso para ver solicitudes');
        }

        $query = Solicitud::with([
            'detalles',
            'institucion',
            'departamento',
            'responsable',
            'usuario'
        ])->where('usuario_id', auth()->id());

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
        $solicitudes = $query->orderBy('created_at', 'desc')
                            ->paginate($perPage)
                            ->appends($request->query());

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
        if (!auth()->user()->hasPermission('ver-solicitudes')) {
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

            $user = auth()->user();
            if (!$user->hasPermission('aprobar-solicitudes') && $user->id !== $solicitud->usuario_id) {
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
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-solicitud')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }
            abort(403);
        }

        try {
            $rules = [
                'tipo_solicitante' => 'required|in:interno,externo',
                'fecha_requerida' => 'required|date|after_or_equal:today',
                'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
                'justificacion' => 'required|string|min:20|max:1000',
                'prioridad' => 'required|in:baja,normal,alta,urgente',
                'observaciones' => 'nullable|string|max:500',
                'items' => 'required|array|min:1',
                'items.*.tipo_item' => 'required|in:activo,componente',
                'items.*.cantidad' => 'required|integer|min:1',
                'oficio_adjunto' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
            ];

            $rules['items.*.item_id'] = 'nullable|integer';
            $rules['items.*.item_descripcion'] = 'nullable|string|max:255';

            $validated = $request->validate($rules);

            DB::beginTransaction();

            $institucionId = null;
            $departamentoId = null;
            $responsableId = null;

            if ($request->tipo_solicitante === 'interno') {
                if ($request->filled('departamento_id') && $request->departamento_id !== 'otro') {
                    $departamentoId = $request->departamento_id;
                    $departamento = Departamento::find($departamentoId);
                    if ($departamento && $departamento->representante) {
                        $responsable = Responsable::where('departamento_id', $departamentoId)
                            ->where('cargo', 'Jefe de Departamento')
                            ->first();
                        $responsableId = $responsable ? $responsable->id : null;
                    }
                } elseif ($request->filled('nuevo_departamento')) {
                    $departamento = Departamento::create([
                        'nombre' => $request->nuevo_departamento,
                        'informacion' => $request->departamento_informacion ?? null,
                        'representante' => $request->departamento_representante ?? null,
                        'ubicacion' => $request->departamento_ubicacion ?? null,
                        'activo' => true,
                        'institucion_id' => null
                    ]);
                    $departamentoId = $departamento->id;
                }
            } else {
                if ($request->filled('institucion_id') && $request->institucion_id !== 'otro') {
                    $institucionId = $request->institucion_id;
                    $institucion = Institucion::find($institucionId);
                    if ($institucion && $institucion->representante) {
                        $responsable = Responsable::where('institucion_id', $institucionId)
                            ->whereNull('departamento_id')
                            ->first();
                        $responsableId = $responsable ? $responsable->id : null;
                    }
                } elseif ($request->filled('nueva_institucion')) {
                    $institucion = Institucion::create([
                        'nombre' => $request->nueva_institucion,
                        'informacion' => $request->informacion ?? null,
                        'representante' => $request->representante ?? null,
                        'ubicacion' => $request->ubicacion ?? null,
                        'activo' => true
                    ]);
                    $institucionId = $institucion->id;
                }
            }

            if ($request->filled('responsable_id') && $request->responsable_id !== 'otro') {
                $responsableId = $request->responsable_id;
            } elseif ($request->filled('nuevo_responsable')) {
                $responsable = Responsable::create([
                    'nombre' => $request->nuevo_responsable,
                    'cargo' => $request->responsable_cargo ?? 'Representante',
                    'telefono' => $request->responsable_telefono ?? null,
                    'email' => $request->responsable_email ?? null,
                    'documento' => $request->responsable_documento ?? null,
                    'activo' => true,
                    'institucion_id' => $institucionId,
                    'departamento_id' => $departamentoId
                ]);
                $responsableId = $responsable->id;
            }

            $oficioPath = null;
            if ($request->hasFile('oficio_adjunto')) {
                $oficioPath = $request->file('oficio_adjunto')->store('solicitudes/oficios', 'public');
            }

            $solicitud = Solicitud::create([
                'usuario_id' => auth()->id(),
                'tipo_solicitante' => $request->tipo_solicitante,
                'institucion_id' => $institucionId,
                'departamento_id' => $departamentoId,
                'responsable_id' => $responsableId,
                'oficio_adjunto' => $oficioPath,
                'fecha_solicitud' => now(),
                'fecha_requerida' => $request->fecha_requerida,
                'fecha_fin_estimada' => $request->fecha_fin_estimada,
                'justificacion' => $request->justificacion,
                'prioridad' => $request->prioridad,
                'estado_solicitud' => 'pendiente',
                'observaciones' => $request->observaciones
            ]);

            foreach ($request->items as $item) {
                $activoId = null;
                $componenteId = null;
                $descripcionPersonalizada = null;

                if (isset($item['item_id']) && !empty($item['item_id'])) {
                    if ($item['tipo_item'] === 'activo') {
                        $activoId = $item['item_id'];
                    } elseif ($item['tipo_item'] === 'componente') {
                        $componenteId = $item['item_id'];
                    }
                } elseif (isset($item['item_descripcion']) && !empty($item['item_descripcion'])) {
                    $descripcionPersonalizada = $item['item_descripcion'];
                } else {
                    throw new \Exception("Debe especificar el item");
                }

                DetalleSolicitud::create([
                    'solicitud_id' => $solicitud->id,
                    'tipo_item' => $item['tipo_item'],
                    'cantidad_solicitada' => $item['cantidad'],
                    'activo_id' => $activoId,
                    'componente_id' => $componenteId,
                    'descripcion_personalizada' => $descripcionPersonalizada,
                    'observaciones' => $item['observaciones'] ?? null
                ]);
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud creada exitosamente',
                    'solicitud_id' => $solicitud->id
                ]);
            }

            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Solicitud creada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', $e->getMessage())->withInput();
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
            abort(403, 'No tienes permiso para aprobar solicitudes');
        }

        try {
            $solicitud = Solicitud::findOrFail($id);

            if ($solicitud->estado_solicitud !== 'pendiente') {
                return back()->with('error', 'Solo se pueden aprobar solicitudes pendientes');
            }

            DB::beginTransaction();

            $solicitud->update([
                'estado_solicitud' => 'aprobada',
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => now()
            ]);

            DB::commit();

            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Solicitud aprobada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            abort(403, 'No tienes permiso para rechazar solicitudes');
        }

        $request->validate([
            'motivo' => 'required|string|min:10'
        ]);

        try {
            $solicitud = Solicitud::findOrFail($id);

            $solicitud->update([
                'estado_solicitud' => 'rechazada',
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => now(),
                'observaciones' => $request->motivo
            ]);

            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Solicitud rechazada');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
