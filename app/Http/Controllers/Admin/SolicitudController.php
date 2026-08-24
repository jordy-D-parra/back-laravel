<?php
// app/Http/Controllers/Admin/SolicitudController.php

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
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolicitudController extends Controller
{
    protected NotificacionService $notificacionService;

    public function __construct(NotificacionService $notificacionService)
    {
        $this->notificacionService = $notificacionService;
    }

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
            'usuario',
            'usuario.trabajador',
            'estado',
            'municipio',
            'parroquia'
        ])->where('usuario_id', $userId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('institucion', function ($sq) use ($search) {
                    $sq->where('nombre', 'ILIKE', "%{$search}%");
                })->orWhereHas('departamento', function ($sq) use ($search) {
                    $sq->where('nombre', 'ILIKE', "%{$search}%");
                })->orWhere('justificacion', 'ILIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%");
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

    public function paraPrestamo(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('ver-prestamos') && !$user->hasPermission('ver-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $query = Solicitud::with(['departamento', 'institucion', 'responsable', 'usuario.trabajador'])
            ->withCount('detalles')
            ->whereIn('estado_solicitud', ['pendiente', 'aprobada'])
            ->whereDoesntHave('prestamos');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('id', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('departamento', fn($q) => $q->where('nombre', 'ILIKE', "%{$buscar}%"))
                  ->orWhereHas('institucion', fn($q) => $q->where('nombre', 'ILIKE', "%{$buscar}%"))
                  ->orWhere('justificacion', 'ILIKE', "%{$buscar}%");
            });
        }

        $solicitudes = $query->orderBy('created_at', 'desc')->paginate(10);
        return response()->json($solicitudes);
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
                'usuario',
                'usuario.trabajador',
                'estado',
                'municipio',
                'parroquia'
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
                    'item_descripcion' => $detalle->descripcion_personalizada ?? 'Item',
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
                'estado_id' => $solicitud->estado_id,
                'municipio_id' => $solicitud->municipio_id,
                'parroquia_id' => $solicitud->parroquia_id,
                'lugar_evento' => $solicitud->lugar_evento,
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

        // ✅ VALIDACIÓN CORREGIDA - AGREGAR responsable_id COMO REQUERIDO
        $validated = $request->validate([
            'tipo_solicitante' => 'required|in:interno,externo',
            'fecha_requerida' => 'required|date|after_or_equal:today',
            'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
            'justificacion' => 'required|string|min:20|max:1000',
            'prioridad' => 'required|in:baja,normal,alta,urgente',
            'observaciones' => 'nullable|string|max:500',
            'responsable_id' => 'required|exists:responsables,id', // ✅ AGREGADO
            'estado_id' => 'nullable|exists:estados,id',
            'municipio_id' => 'nullable|exists:municipios,id',
            'parroquia_id' => 'nullable|exists:parroquias,id',
            'lugar_evento' => 'nullable|string|max:200',
            'items' => 'required|array|min:1',
            'items.*.tipo_item' => 'required|in:activo,componente',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.item_descripcion' => 'required|string|max:255',
        ]);

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
            'responsable_id' => $request->responsable_id, // ✅ AHORA VIENE DEL VALIDATED
            'oficio_adjunto' => null,
            'fecha_solicitud' => now(),
            'fecha_requerida' => $request->fecha_requerida,
            'fecha_fin_estimada' => $request->fecha_fin_estimada,
            'justificacion' => $request->justificacion,
            'prioridad' => $request->prioridad,
            'estado_solicitud' => 'pendiente',
            'observaciones' => $request->observaciones ?? null,
            'estado_id' => $request->estado_id,
            'municipio_id' => $request->municipio_id,
            'parroquia_id' => $request->parroquia_id,
            'lugar_evento' => $request->lugar_evento,
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

        // ========== ENVIAR NOTIFICACIÓN POR CORREO ==========
        try {
            $this->enviarNotificacionCreacion($solicitud);
        } catch (\Exception $e) {
            Log::error('Error al enviar notificación de creación: ' . $e->getMessage());
        }

        $solicitudCreada = Solicitud::with([
            'responsable',
            'departamento',
            'institucion',
            'detalles',
            'estado',
            'municipio',
            'parroquia',
            'usuario.trabajador'
        ])->find($solicitud->id);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud creada exitosamente',
            'solicitud_id' => $solicitud->id,
            'data' => $solicitudCreada
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
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
    try {
        $solicitud = Solicitud::findOrFail($id);

        if ($solicitud->estado_solicitud !== 'pendiente') {
            return response()->json(['success' => false, 'message' => 'Solo se pueden editar solicitudes pendientes'], 422);
        }

        // ✅ VALIDACIÓN CORREGIDA - AGREGAR responsable_id COMO REQUERIDO
        $validated = $request->validate([
            'tipo_solicitante' => 'required|in:interno,externo',
            'fecha_requerida' => 'required|date',
            'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
            'justificacion' => 'required|string|min:20|max:1000',
            'prioridad' => 'required|in:baja,normal,alta,urgente',
            'observaciones' => 'nullable|string|max:500',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'institucion_id' => 'nullable|exists:instituciones,id',
            'responsable_id' => 'required|exists:responsables,id', // ✅ AGREGADO
            'estado_id' => 'nullable|exists:estados,id',
            'municipio_id' => 'nullable|exists:municipios,id',
            'parroquia_id' => 'nullable|exists:parroquias,id',
            'lugar_evento' => 'nullable|string|max:200',
        ]);

        DB::beginTransaction();

        $solicitud->update([
            'tipo_solicitante' => $validated['tipo_solicitante'],
            'fecha_requerida' => $validated['fecha_requerida'],
            'fecha_fin_estimada' => $validated['fecha_fin_estimada'],
            'justificacion' => $validated['justificacion'],
            'prioridad' => $validated['prioridad'],
            'observaciones' => $validated['observaciones'] ?? null,
            'departamento_id' => $validated['departamento_id'] ?? null,
            'institucion_id' => $validated['institucion_id'] ?? null,
            'responsable_id' => $validated['responsable_id'], // ✅ AHORA SE ACTUALIZA
            'estado_id' => $validated['estado_id'] ?? null,
            'municipio_id' => $validated['municipio_id'] ?? null,
            'parroquia_id' => $validated['parroquia_id'] ?? null,
            'lugar_evento' => $validated['lugar_evento'] ?? null,
        ]);

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

        $solicitudActualizada = Solicitud::with([
            'responsable',
            'departamento',
            'institucion',
            'detalles',
            'estado',
            'municipio',
            'parroquia'
        ])->find($solicitud->id);

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

            // ========== ENVIAR NOTIFICACIÓN DE CANCELACIÓN ==========
            try {
                $this->enviarNotificacionCancelacion($solicitud);
            } catch (\Exception $e) {
                Log::error('Error al enviar notificación de cancelación: ' . $e->getMessage());
            }

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

            // ========== ENVIAR NOTIFICACIÓN DE APROBACIÓN ==========
            try {
                $this->enviarNotificacionAprobacion($solicitud);
            } catch (\Exception $e) {
                Log::error('Error al enviar notificación de aprobación: ' . $e->getMessage());
            }

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

            // ========== ENVIAR NOTIFICACIÓN DE RECHAZO ==========
            try {
                $this->enviarNotificacionRechazo($solicitud, $motivo);
            } catch (\Exception $e) {
                Log::error('Error al enviar notificación de rechazo: ' . $e->getMessage());
            }

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

    // ========== MÉTODOS PARA ENVIAR NOTIFICACIONES ==========

    /**
     * Enviar notificación cuando se crea una solicitud
     */
    protected function enviarNotificacionCreacion(Solicitud $solicitud): void
    {
        // 1. Notificar al creador de la solicitud
        $usuarioCreador = $solicitud->usuario;
        if ($usuarioCreador && $usuarioCreador->email) {
            $this->notificacionService->enviarAUsuario(
                $usuarioCreador,
                '📝 Solicitud creada exitosamente',
                "Tu solicitud #{$solicitud->id} ha sido creada y está pendiente de revisión.\n\n" .
                "📌 Solicitud: #{$solicitud->id}\n" .
                "🔴 Prioridad: " . ucfirst($solicitud->prioridad) . "\n" .
                "📅 Fecha requerida: " . ($solicitud->fecha_requerida ? $solicitud->fecha_requerida->format('d/m/Y') : 'No especificada') . "\n" .
                "📅 Fecha fin estimada: " . ($solicitud->fecha_fin_estimada ? $solicitud->fecha_fin_estimada->format('d/m/Y') : 'No especificada') . "\n\n" .
                "📝 Justificación:\n" . substr($solicitud->justificacion, 0, 300) . (strlen($solicitud->justificacion) > 300 ? '...' : '') . "\n\n" .
                "El administrador revisará tu solicitud pronto. Recibirás una notificación cuando sea aprobada o rechazada.",
                'solicitud',
                route('admin.solicitudes.index')
            );
        }

        // 2. ✅ ENVIAR CORREO AL RESPONSABLE (si tiene email)
        $responsable = $solicitud->responsable;
        if ($responsable && $responsable->email) {
            // Buscar si el responsable tiene un usuario asociado
            $usuarioResponsable = Usuario::where('trabajador_id', $responsable->id)->first();
            
            if ($usuarioResponsable && $usuarioResponsable->email) {
                $this->notificacionService->enviarAUsuario(
                    $usuarioResponsable,
                    '📋 Nueva solicitud de préstamo asignada',
                    "Se ha creado una nueva solicitud de préstamo en la que eres responsable.\n\n" .
                    "📌 Solicitud #{$solicitud->id}\n" .
                    "👤 Solicitante: " . ($solicitud->usuario?->trabajador?->nombre ?? 'Usuario') . "\n" .
                    "🏢 Entidad: " . ($solicitud->tipo_solicitante === 'interno' 
                        ? ($solicitud->departamento?->nombre ?? 'No especificado')
                        : ($solicitud->institucion?->nombre ?? 'No especificado')) . "\n" .
                    "🔴 Prioridad: " . ucfirst($solicitud->prioridad) . "\n" .
                    "📅 Fecha requerida: " . ($solicitud->fecha_requerida ? $solicitud->fecha_requerida->format('d/m/Y') : 'No especificada') . "\n" .
                    "📅 Fecha fin estimada: " . ($solicitud->fecha_fin_estimada ? $solicitud->fecha_fin_estimada->format('d/m/Y') : 'No especificada') . "\n\n" .
                    "📝 Justificación:\n" . substr($solicitud->justificacion, 0, 200) . (strlen($solicitud->justificacion) > 200 ? '...' : '') . "\n\n" .
                    "Como responsable, debes estar atento a la aprobación de esta solicitud.",
                    'solicitud',
                    route('admin.solicitudes.index')
                );
            } else {
                // Si no tiene usuario, intentar enviar directamente al email del responsable
                try {
                    $this->notificacionService->enviarAUsuario(
                        $usuarioCreador, // Usamos al creador como fallback para usar el servicio
                        '📋 Nueva solicitud de préstamo',
                        "Se ha creado una nueva solicitud de préstamo.\n\n" .
                        "📌 Solicitud #{$solicitud->id}\n" .
                        "👤 Responsable: {$responsable->nombre}\n" .
                        "📧 Email del responsable: {$responsable->email}\n" .
                        "🏢 Entidad: " . ($solicitud->tipo_solicitante === 'interno' 
                            ? ($solicitud->departamento?->nombre ?? 'No especificado')
                            : ($solicitud->institucion?->nombre ?? 'No especificado')) . "\n" .
                        "🔴 Prioridad: " . ucfirst($solicitud->prioridad) . "\n\n" .
                        "📝 Justificación:\n" . substr($solicitud->justificacion, 0, 200) . (strlen($solicitud->justificacion) > 200 ? '...' : ''),
                        'solicitud',
                        route('admin.solicitudes.index')
                    );
                    
                    // También enviar un correo directo al responsable
                    \Illuminate\Support\Facades\Mail::to($responsable->email)->send(
                        new \App\Mail\NotificacionMail(
                            new \App\Models\Notificacion([
                                'titulo' => '📋 Nueva solicitud de préstamo',
                                'mensaje' => "Se ha creado una nueva solicitud de préstamo #{$solicitud->id}.\n\n" .
                                    "Eres el responsable de esta solicitud.\n" .
                                    "Prioridad: " . ucfirst($solicitud->prioridad) . "\n" .
                                    "Fecha requerida: " . ($solicitud->fecha_requerida ? $solicitud->fecha_requerida->format('d/m/Y') : 'No especificada') . "\n\n" .
                                    "Justificación: " . substr($solicitud->justificacion, 0, 200),
                                'fecha_envio' => now(),
                                'tipo' => 'solicitud',
                                'url' => route('admin.solicitudes.index'),
                            ]),
                            $responsable->nombre
                        )
                    );
                } catch (\Exception $e) {
                    Log::error('Error al enviar correo al responsable: ' . $e->getMessage());
                }
            }
        }

        // 3. Notificar a los administradores
        $administradores = Usuario::whereHas('rol', function($query) {
            $query->where('nombre', 'admin');
        })->where('status', 'activo')->get();

        $responsableNombre = $solicitud->responsable?->nombre ?? 'No especificado';
        $entidadNombre = $solicitud->tipo_solicitante === 'interno'
            ? ($solicitud->departamento?->nombre ?? 'No especificado')
            : ($solicitud->institucion?->nombre ?? 'No especificado');

        $itemsLista = '';
        foreach ($solicitud->detalles as $detalle) {
            $itemsLista .= "  • " . $detalle->tipo_item . ": " . $detalle->descripcion_personalizada . " (Cant: " . $detalle->cantidad_solicitada . ")\n";
        }

        foreach ($administradores as $admin) {
            if ($admin->email) {
                $this->notificacionService->enviarAUsuario(
                    $admin,
                    '📋 Nueva solicitud de préstamo',
                    "Se ha creado una nueva solicitud de préstamo.\n\n" .
                    "📌 Solicitud #{$solicitud->id}\n" .
                    "👤 Solicitante: " . ($solicitud->usuario?->trabajador?->nombre ?? 'Usuario') . "\n" .
                    "🏢 Entidad: {$entidadNombre}\n" .
                    "👤 Responsable: {$responsableNombre}\n" .
                    "📧 Email responsable: " . ($solicitud->responsable?->email ?? 'No registrado') . "\n" .
                    "🔴 Prioridad: " . ucfirst($solicitud->prioridad) . "\n" .
                    "📅 Fecha requerida: " . ($solicitud->fecha_requerida ? $solicitud->fecha_requerida->format('d/m/Y') : 'No especificada') . "\n" .
                    "📅 Fecha fin estimada: " . ($solicitud->fecha_fin_estimada ? $solicitud->fecha_fin_estimada->format('d/m/Y') : 'No especificada') . "\n" .
                    "📍 Lugar del evento: " . ($solicitud->lugar_evento ?? 'No especificado') . "\n\n" .
                    "📦 Items solicitados:\n" . $itemsLista . "\n" .
                    "📝 Justificación:\n" . substr($solicitud->justificacion, 0, 200) . (strlen($solicitud->justificacion) > 200 ? '...' : '') . "\n\n" .
                    "Por favor, revise y apruebe o rechace la solicitud.",
                    'solicitud',
                    route('admin.solicitudes.index')
                );
            }
        }
    }

    /**
     * Enviar notificación cuando se aprueba una solicitud
     */
    protected function enviarNotificacionAprobacion(Solicitud $solicitud): void
    {
        $usuarioCreador = $solicitud->usuario;
        $aprobador = auth()->user();

        // 1. Notificar al creador
        if ($usuarioCreador && $usuarioCreador->email) {
            $this->notificacionService->enviarAUsuario(
                $usuarioCreador,
                '✅ Solicitud aprobada',
                "¡Tu solicitud #{$solicitud->id} ha sido aprobada!\n\n" .
                "📌 Solicitud: #{$solicitud->id}\n" .
                "✅ Aprobada por: " . ($aprobador?->trabajador?->nombre ?? $aprobador?->usuario ?? 'Administrador') . "\n" .
                "📅 Fecha de aprobación: " . now()->format('d/m/Y H:i') . "\n" .
                "🔴 Prioridad: " . ucfirst($solicitud->prioridad) . "\n\n" .
                "📝 Justificación:\n" . substr($solicitud->justificacion, 0, 200) . (strlen($solicitud->justificacion) > 200 ? '...' : '') . "\n\n" .
                "Tu solicitud está lista para ser convertida en préstamo. Dirígete al módulo de préstamos para continuar con el proceso.",
                'solicitud',
                route('admin.prestamos.index')
            );
        }

        // 2. ✅ Notificar al responsable de la solicitud
        if ($solicitud->responsable_id) {
            $responsable = $solicitud->responsable;
            if ($responsable && $responsable->email) {
                $usuarioResponsable = Usuario::where('trabajador_id', $responsable->id)->first();
                if ($usuarioResponsable && $usuarioResponsable->email) {
                    $this->notificacionService->enviarAUsuario(
                        $usuarioResponsable,
                        '✅ Solicitud aprobada - Acción requerida',
                        "La solicitud #{$solicitud->id} ha sido aprobada.\n\n" .
                        "📌 Solicitud: #{$solicitud->id}\n" .
                        "✅ Aprobada por: " . ($aprobador?->trabajador?->nombre ?? $aprobador?->usuario ?? 'Administrador') . "\n" .
                        "📅 Fecha de aprobación: " . now()->format('d/m/Y H:i') . "\n\n" .
                        "🔴 Prioridad: " . ucfirst($solicitud->prioridad) . "\n" .
                        "📅 Fecha requerida: " . ($solicitud->fecha_requerida ? $solicitud->fecha_requerida->format('d/m/Y') : 'No especificada') . "\n\n" .
                        "Como responsable de esta solicitud, debes coordinar la entrega o gestión del préstamo.\n\n" .
                        "La solicitud está lista para ser gestionada como préstamo en el sistema.",
                        'solicitud',
                        route('admin.prestamos.index')
                    );
                } else {
                    // Enviar correo directo al responsable
                    try {
                        \Illuminate\Support\Facades\Mail::to($responsable->email)->send(
                            new \App\Mail\NotificacionMail(
                                new \App\Models\Notificacion([
                                    'titulo' => '✅ Solicitud aprobada - ' . $solicitud->id,
                                    'mensaje' => "La solicitud #{$solicitud->id} ha sido aprobada.\n\n" .
                                        "Aprobada por: " . ($aprobador?->trabajador?->nombre ?? $aprobador?->usuario ?? 'Administrador') . "\n" .
                                        "Fecha de aprobación: " . now()->format('d/m/Y H:i') . "\n\n" .
                                        "Como responsable, debes coordinar la gestión del préstamo.",
                                    'fecha_envio' => now(),
                                    'tipo' => 'solicitud',
                                    'url' => route('admin.prestamos.index'),
                                ]),
                                $responsable->nombre
                            )
                        );
                    } catch (\Exception $e) {
                        Log::error('Error al enviar correo al responsable (aprobación): ' . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Enviar notificación cuando se rechaza una solicitud
     */
    protected function enviarNotificacionRechazo(Solicitud $solicitud, string $motivo): void
    {
        $usuarioCreador = $solicitud->usuario;
        $rechazador = auth()->user();

        if ($usuarioCreador && $usuarioCreador->email) {
            $this->notificacionService->enviarAUsuario(
                $usuarioCreador,
                '❌ Solicitud rechazada',
                "Tu solicitud #{$solicitud->id} ha sido rechazada.\n\n" .
                "📌 Solicitud: #{$solicitud->id}\n" .
                "❌ Rechazada por: " . ($rechazador?->trabajador?->nombre ?? $rechazador?->usuario ?? 'Administrador') . "\n" .
                "📅 Fecha de rechazo: " . now()->format('d/m/Y H:i') . "\n\n" .
                "📝 Motivo del rechazo:\n{$motivo}\n\n" .
                "Si tienes preguntas o necesitas aclaraciones, contacta al administrador del sistema.\n\n" .
                "Puedes crear una nueva solicitud con la información corregida si lo deseas.",
                'solicitud',
                route('admin.solicitudes.index')
            );
        }

        // Notificar al responsable también
        if ($solicitud->responsable_id) {
            $responsable = $solicitud->responsable;
            if ($responsable && $responsable->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($responsable->email)->send(
                        new \App\Mail\NotificacionMail(
                            new \App\Models\Notificacion([
                                'titulo' => '❌ Solicitud rechazada - ' . $solicitud->id,
                                'mensaje' => "La solicitud #{$solicitud->id} ha sido rechazada.\n\n" .
                                    "Rechazada por: " . ($rechazador?->trabajador?->nombre ?? $rechazador?->usuario ?? 'Administrador') . "\n" .
                                    "Fecha de rechazo: " . now()->format('d/m/Y H:i') . "\n\n" .
                                    "Motivo: {$motivo}\n\n" .
                                    "Como responsable, debes estar informado de esta decisión.",
                                'fecha_envio' => now(),
                                'tipo' => 'solicitud',
                                'url' => route('admin.solicitudes.index'),
                            ]),
                            $responsable->nombre
                        )
                    );
                } catch (\Exception $e) {
                    Log::error('Error al enviar correo al responsable (rechazo): ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Enviar notificación cuando se cancela una solicitud
     */
    protected function enviarNotificacionCancelacion(Solicitud $solicitud): void
    {
        $administradores = Usuario::whereHas('rol', function($query) {
            $query->where('nombre', 'admin');
        })->where('status', 'activo')->get();

        $cancelador = auth()->user();

        foreach ($administradores as $admin) {
            if ($admin->email) {
                $this->notificacionService->enviarAUsuario(
                    $admin,
                    '🗑️ Solicitud cancelada',
                    "La solicitud #{$solicitud->id} ha sido cancelada.\n\n" .
                    "📌 Solicitud: #{$solicitud->id}\n" .
                    "👤 Cancelada por: " . ($cancelador?->trabajador?->nombre ?? $cancelador?->usuario ?? 'Usuario') . "\n" .
                    "📅 Fecha de cancelación: " . now()->format('d/m/Y H:i') . "\n" .
                    "🏢 Entidad: " . ($solicitud->tipo_solicitante === 'interno' 
                        ? ($solicitud->departamento?->nombre ?? 'No especificado')
                        : ($solicitud->institucion?->nombre ?? 'No especificado')) . "\n" .
                    "🔴 Prioridad: " . ucfirst($solicitud->prioridad) . "\n\n" .
                    "La solicitud ha sido cancelada por el usuario.",
                    'solicitud',
                    route('admin.solicitudes.index')
                );
            }
        }

        $usuarioCreador = $solicitud->usuario;
        if ($usuarioCreador && $usuarioCreador->email && $usuarioCreador->id !== auth()->id()) {
            $this->notificacionService->enviarAUsuario(
                $usuarioCreador,
                '🗑️ Solicitud cancelada',
                "Tu solicitud #{$solicitud->id} ha sido cancelada.\n\n" .
                "📌 Solicitud: #{$solicitud->id}\n" .
                "👤 Cancelada por: " . ($cancelador?->trabajador?->nombre ?? $cancelador?->usuario ?? 'Usuario') . "\n" .
                "📅 Fecha de cancelación: " . now()->format('d/m/Y H:i') . "\n\n" .
                "Si esto fue un error, puedes crear una nueva solicitud.",
                'solicitud',
                route('admin.solicitudes.index')
            );
        }
    }
}