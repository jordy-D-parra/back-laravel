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
use App\Models\CorreoRecibido;
use App\Services\NotificacionService;
use App\Services\CorreoImapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SolicitudController extends Controller
{
    protected NotificacionService $notificacionService;
    protected CorreoImapService $imapService;

    public function __construct(
        NotificacionService $notificacionService,
        CorreoImapService $imapService
    ) {
        $this->notificacionService = $notificacionService;
        $this->imapService = $imapService;
    }

    // ============================================================
    // LISTADO DE SOLICITUDES (ADMIN)
    // ============================================================
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-solicitudes')) {
            abort(403, 'No tienes permiso para ver solicitudes');
        }

        $user = auth()->user();

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
        ]);

        // Si NO es admin, solo ve sus propias solicitudes
        if (!$user->hasPermission('aprobar-solicitudes')) {
            $query->where('usuario_id', $user->id);
        }

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
        $responsables = Responsable::orderBy('nombre')->get();

        return view('admin.solicitudes.index', compact(
            'solicitudes',
            'activos',
            'componentes',
            'instituciones',
            'departamentos',
            'responsables'
        ));
    }

    // ============================================================
    // NO LEÍDAS POR ADMIN
    // ============================================================
    public function noLeidasAdmin(Request $request)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $query = Solicitud::with(['detalles', 'institucion', 'departamento', 'responsable', 'usuario.trabajador'])
            ->where('leida_por_admin', false)
            ->orderBy('created_at', 'desc');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('id', 'LIKE', "%{$buscar}%")
                  ->orWhere('justificacion', 'ILIKE', "%{$buscar}%")
                  ->orWhereHas('departamento', fn($sq) => $sq->where('nombre', 'ILIKE', "%{$buscar}%"))
                  ->orWhereHas('institucion', fn($sq) => $sq->where('nombre', 'ILIKE', "%{$buscar}%"));
            });
        }

        $solicitudes = $query->get();
        $total = $solicitudes->count();

        return response()->json([
            'success' => true,
            'total' => $total,
            'data' => $solicitudes
        ]);
    }

    // ============================================================
    // MARCAR SOLICITUD COMO LEÍDA
    // ============================================================
    public function marcarLeida($id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $solicitud = Solicitud::findOrFail($id);
            $solicitud->update(['leida_por_admin' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud marcada como leída'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // SOLICITUDES PARA PRÉSTAMO
    // ============================================================
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

    // ============================================================
    // OBTENER DETALLES
    // ============================================================
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

            if (!$user->hasPermission('aprobar-solicitudes')
                && !$user->hasPermission('ver-prestamos')
                && $user->id !== $solicitud->usuario_id) {
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
                'codigo' => 'SOL-' . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT),
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
                'usuario' => $solicitud->usuario ? [
                    'id' => $solicitud->usuario->id,
                    'usuario' => $solicitud->usuario->usuario,
                    'trabajador' => $solicitud->usuario->trabajador ? [
                        'nombre' => $solicitud->usuario->trabajador->nombre,
                        'apellido' => $solicitud->usuario->trabajador->apellido,
                        'cedula' => $solicitud->usuario->trabajador->cedula,
                        'email' => $solicitud->usuario->trabajador->email,
                    ] : null
                ] : null,
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

    // ============================================================
    // STORE - CREAR SOLICITUD
    // ============================================================
    public function store(Request $request)
    {
        try {
            if (!auth()->user()->hasPermission('crear-solicitud')) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }

            $userId = (int) auth()->user()->id;

            // ✅ Validación FUERA de la transacción
            $validated = $request->validate([
                'tipo_solicitante' => 'required|in:interno,externo',
                'fecha_requerida' => 'required|date|after_or_equal:today',
                'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
                'justificacion' => 'required|string|min:20|max:1000',
                'prioridad' => 'required|in:baja,normal,alta,urgente',
                'observaciones' => 'nullable|string|max:500',
                'responsable_id' => 'required|exists:responsables,id',
                'estado_id' => 'nullable|exists:estados,id',
                'municipio_id' => 'nullable|exists:municipios,id',
                'parroquia_id' => 'nullable|exists:parroquias,id',
                'lugar_evento' => 'nullable|string|max:200',
                'items' => 'required|array|min:1',
                'items.*.tipo_item' => 'required|in:activo,componente',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.item_descripcion' => 'required|string|max:255',
            ]);

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

            $responsable = Responsable::find($request->responsable_id);
            if (!$responsable) {
                throw new \Exception('Responsable no encontrado');
            }

            // ✅ DB::transaction() con closure maneja commit/rollback automáticamente
            $solicitud = DB::transaction(function () use ($request, $userId, $institucionId, $departamentoId) {
                $nuevaSolicitud = Solicitud::create([
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
                    'estado_id' => $request->estado_id,
                    'municipio_id' => $request->municipio_id,
                    'parroquia_id' => $request->parroquia_id,
                    'lugar_evento' => $request->lugar_evento,
                    'leida_por_admin' => false,
                ]);

                foreach ($request->items as $item) {
                    DetalleSolicitud::create([
                        'solicitud_id' => $nuevaSolicitud->id,
                        'tipo_item' => $item['tipo_item'],
                        'cantidad_solicitada' => (int) $item['cantidad'],
                        'descripcion_personalizada' => $item['item_descripcion'],
                        'activo_id' => null,
                        'componente_id' => null,
                        'observaciones' => $item['observaciones'] ?? null
                    ]);
                }

                return $nuevaSolicitud;
            });

            try {
                $this->enviarNotificacionesSolicitud($solicitud);
            } catch (\Exception $e) {
                Log::error('Error al enviar notificaciones: ' . $e->getMessage());
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
            // ⚠️ NO DB::rollBack() aquí: la validación ocurre ANTES de cualquier transacción
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // NOTIFICACIONES DE SOLICITUD
    // ============================================================
    protected function enviarNotificacionesSolicitud(Solicitud $solicitud): void
    {
        $usuarioCreador = $solicitud->usuario;
        $responsable = $solicitud->responsable;

        $entidadNombre = $solicitud->tipo_solicitante === 'interno'
            ? ($solicitud->departamento?->nombre ?? 'No especificado')
            : ($solicitud->institucion?->nombre ?? 'No especificado');

        $itemsLista = '';
        foreach ($solicitud->detalles as $detalle) {
            $itemsLista .= " • " . $detalle->tipo_item . ": " . $detalle->descripcion_personalizada . " (Cant: " . $detalle->cantidad_solicitada . ")\n";
        }

        $solicitanteNombre = $solicitud->usuario?->trabajador?->nombre
            ?? $solicitud->usuario?->usuario
            ?? 'Usuario del sistema';

        // 1. NOTIFICAR AL CREADOR
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

        // 2. NOTIFICAR AL RESPONSABLE
        if ($responsable && $responsable->email) {
            $mensajeResponsable =
                "Se ha creado una nueva solicitud de préstamo en la que eres responsable.\n\n" .
                "📌 Solicitud #{$solicitud->id}\n" .
                "👤 Solicitante: {$solicitanteNombre}\n" .
                "🏢 Entidad: {$entidadNombre}\n" .
                "🔴 Prioridad: " . ucfirst($solicitud->prioridad) . "\n" .
                "📅 Fecha requerida: " . ($solicitud->fecha_requerida ? $solicitud->fecha_requerida->format('d/m/Y') : 'No especificada') . "\n" .
                "📅 Fecha fin estimada: " . ($solicitud->fecha_fin_estimada ? $solicitud->fecha_fin_estimada->format('d/m/Y') : 'No especificada') . "\n" .
                "📍 Lugar del evento: " . ($solicitud->lugar_evento ?? 'No especificado') . "\n\n" .
                "📦 Items solicitados:\n" . $itemsLista . "\n" .
                "📝 Justificación:\n" . substr($solicitud->justificacion, 0, 200) . (strlen($solicitud->justificacion) > 200 ? '...' : '') . "\n\n" .
                "Como responsable de la entidad, debes estar informado sobre esta solicitud.\n" .
                "El administrador del sistema revisará y aprobará la solicitud próximamente.";

            $this->notificacionService->enviarAResponsable(
                $responsable->email,
                $responsable->nombre,
                '📋 Nueva solicitud de préstamo - Responsable',
                $mensajeResponsable,
                'solicitud',
                route('admin.solicitudes.index')
            );
        }

        // 3. NOTIFICAR A ADMINISTRADORES
        $administradores = Usuario::whereHas('rol', function ($query) {
            $query->where('nombre', 'admin');
        })->where('status', 'activo')->get();

        $responsableNombre = $responsable?->nombre ?? 'No especificado';
        $responsableEmail = $responsable?->email ?? 'No registrado';

        $mensajeAdmin =
            "Se ha creado una nueva solicitud de préstamo.\n\n" .
            "📌 Solicitud #{$solicitud->id}\n" .
            "👤 Solicitante: {$solicitanteNombre}\n" .
            "🏢 Entidad: {$entidadNombre}\n" .
            "👤 Responsable: {$responsableNombre}\n" .
            "📧 Email responsable: {$responsableEmail}\n" .
            "🔴 Prioridad: " . ucfirst($solicitud->prioridad) . "\n" .
            "📅 Fecha requerida: " . ($solicitud->fecha_requerida ? $solicitud->fecha_requerida->format('d/m/Y') : 'No especificada') . "\n" .
            "📅 Fecha fin estimada: " . ($solicitud->fecha_fin_estimada ? $solicitud->fecha_fin_estimada->format('d/m/Y') : 'No especificada') . "\n" .
            "📍 Lugar del evento: " . ($solicitud->lugar_evento ?? 'No especificado') . "\n\n" .
            "📦 Items solicitados:\n" . $itemsLista . "\n" .
            "📝 Justificación:\n" . substr($solicitud->justificacion, 0, 200) . (strlen($solicitud->justificacion) > 200 ? '...' : '') . "\n\n" .
            "Por favor, revise y apruebe o rechace la solicitud.";

        foreach ($administradores as $admin) {
            if ($admin->email) {
                $this->notificacionService->enviarAUsuario(
                    $admin,
                    '📋 Nueva solicitud de préstamo',
                    $mensajeAdmin,
                    'solicitud',
                    route('admin.solicitudes.index')
                );
            }
        }
    }

    // ============================================================
    // UPDATE - ACTUALIZAR SOLICITUD
    // ============================================================
    public function update(Request $request, $id)
    {
        try {
            $solicitud = Solicitud::findOrFail($id);

            if ($solicitud->estado_solicitud !== 'pendiente') {
                return response()->json(['success' => false, 'message' => 'Solo se pueden editar solicitudes pendientes'], 422);
            }

            // ✅ Validación FUERA de la transacción
            $validated = $request->validate([
                'tipo_solicitante' => 'required|in:interno,externo',
                'fecha_requerida' => 'required|date',
                'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
                'justificacion' => 'required|string|min:20|max:1000',
                'prioridad' => 'required|in:baja,normal,alta,urgente',
                'observaciones' => 'nullable|string|max:500',
                'departamento_id' => 'nullable|exists:departamentos,id',
                'institucion_id' => 'nullable|exists:instituciones,id',
                'responsable_id' => 'required|exists:responsables,id',
                'estado_id' => 'nullable|exists:estados,id',
                'municipio_id' => 'nullable|exists:municipios,id',
                'parroquia_id' => 'nullable|exists:parroquias,id',
                'lugar_evento' => 'nullable|string|max:200',
            ]);

            // ✅ DB::transaction() con closure
            DB::transaction(function () use ($solicitud, $validated, $request) {
                $solicitud->update([
                    'tipo_solicitante' => $validated['tipo_solicitante'],
                    'fecha_requerida' => $validated['fecha_requerida'],
                    'fecha_fin_estimada' => $validated['fecha_fin_estimada'],
                    'justificacion' => $validated['justificacion'],
                    'prioridad' => $validated['prioridad'],
                    'observaciones' => $validated['observaciones'] ?? null,
                    'departamento_id' => $validated['departamento_id'] ?? null,
                    'institucion_id' => $validated['institucion_id'] ?? null,
                    'responsable_id' => $validated['responsable_id'],
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
            });

            $solicitudActualizada = Solicitud::with([
                'responsable', 'departamento', 'institucion', 'detalles', 'estado', 'municipio', 'parroquia'
            ])->find($solicitud->id);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud actualizada exitosamente',
                    'data' => $solicitudActualizada
                ]);
            }

            return redirect()->route('admin.solicitudes.index')
                ->with('success', 'Solicitud actualizada exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // ⚠️ NO DB::rollBack() aquí
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en update: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // ============================================================
    // DESTROY - ELIMINAR SOLICITUD
    // ============================================================
    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar solicitudes. Solo administradores.'], 403);
            }
            abort(403);
        }

        try {
            $solicitud = Solicitud::findOrFail($id);

            // ✅ DB::transaction() con closure
            DB::transaction(function () use ($solicitud) {
                $solicitud->detalles()->delete();
                $solicitud->delete();
            });

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Solicitud eliminada exitosamente']);
            }
            return redirect()->route('admin.solicitudes.index')->with('success', 'Solicitud eliminada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en destroy: ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // ============================================================
    // CANCEL - CANCELAR SOLICITUD
    // ============================================================
    public function cancel($id)
    {
        if (!auth()->user()->hasPermission('cancelar-solicitud')) {
            if (request()->ajax() || request()->wantsJson()) {
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

            // ✅ DB::transaction() con closure
            DB::transaction(function () use ($solicitud) {
                $solicitud->update(['estado_solicitud' => 'cancelada']);
            });

            try {
                $this->enviarNotificacionCancelacion($solicitud);
            } catch (\Exception $e) {
                Log::error('Error al enviar notificación de cancelación: ' . $e->getMessage());
            }

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Solicitud cancelada exitosamente']);
            }
            return redirect()->route('admin.solicitudes.index')->with('success', 'Solicitud cancelada');
        } catch (\Exception $e) {
            Log::error('Error en cancel: ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // ============================================================
// APPROVE - APROBAR SOLICITUD (CON VERIFICACIÓN DE STOCK)
// ============================================================
public function approve(Request $request, $id)
{
    if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }
        abort(403);
    }

    try {
        $solicitud = Solicitud::with('detalles')->findOrFail($id);

        if ($solicitud->estado_solicitud !== 'pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden aprobar solicitudes pendientes. Estado actual: ' . $solicitud->estado_solicitud
            ], 422);
        }

        // ✅ Validación FUERA de la transacción
        $validated = $request->validate([
            'fecha_requerida' => 'required|date',
            'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // ========== VERIFICACIÓN DE STOCK ==========
        $faltantes = [];
        foreach ($solicitud->detalles as $detalle) {
            $descripcion = $detalle->descripcion_personalizada ?? 'Item';
            $cantidadSolicitada = $detalle->cantidad_solicitada;

            if ($detalle->tipo_item === 'activo' && $detalle->activo_id) {
                $activo = Activo::find($detalle->activo_id);
                if (!$activo || $activo->cantidad < $cantidadSolicitada) {
                    $faltantes[] = [
                        'tipo' => 'activo',
                        'nombre' => $descripcion,
                        'solicitado' => $cantidadSolicitada,
                        'disponible' => $activo->cantidad ?? 0,
                        'serial' => $activo->serial ?? 'N/A'
                    ];
                }
            } elseif ($detalle->tipo_item === 'componente' && $detalle->componente_id) {
                $componente = Componente::find($detalle->componente_id);
                if (!$componente || $componente->estado !== 'en_bodega') {
                    $faltantes[] = [
                        'tipo' => 'componente',
                        'nombre' => $descripcion,
                        'solicitado' => $cantidadSolicitada,
                        'disponible' => 0,
                        'serial' => $componente->serial ?? 'N/A'
                    ];
                }
            } else {
                Log::info("Item de texto libre sin verificación de stock: {$descripcion}");
            }
        }

        // ========== HAY FALTANTES → RECHAZAR AUTOMÁTICAMENTE ==========
        if (count($faltantes) > 0) {
            $motivoRechazo = $this->construirMensajeFaltaStock($faltantes);

            DB::transaction(function () use ($solicitud, $motivoRechazo) {
                $solicitud->update([
                    'estado_solicitud' => 'rechazada',
                    'aprobado_por' => auth()->id(),
                    'fecha_aprobacion' => now(),
                    'observaciones' => $motivoRechazo,
                    'leida_por_admin' => true,
                ]);
            });

            try {
                $this->enviarNotificacionRechazo($solicitud, $motivoRechazo);
            } catch (\Exception $e) {
                Log::error('Error al enviar notificación de rechazo: ' . $e->getMessage());
            }

            return response()->json([
                'success' => false,
                'message' => 'No hay stock suficiente para los siguientes items: ' . $this->formatearFaltantes($faltantes),
                'faltantes' => $faltantes,
                'auto_rechazada' => true
            ], 422);
        }

        // ========== HAY STOCK → APROBAR ==========
        $fechaRequerida = new \DateTime($validated['fecha_requerida']);
        $hoy = new \DateTime('today');
        $fechaPasada = $fechaRequerida < $hoy;

        DB::transaction(function () use ($solicitud, $validated) {
            $solicitud->update([
                'estado_solicitud' => 'aprobada',
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => now(),
                'fecha_requerida' => $validated['fecha_requerida'],
                'fecha_fin_estimada' => $validated['fecha_fin_estimada'],
                'observaciones' => $validated['observaciones'] ?? $solicitud->observaciones,
                'leida_por_admin' => true,
            ]);
        });

        $solicitud->refresh();

        try {
            $this->enviarNotificacionAprobacion($solicitud, $fechaPasada);
        } catch (\Exception $e) {
            Log::error('Error al enviar notificación de aprobación: ' . $e->getMessage());
        }

        if (request()->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $fechaPasada
                    ? 'Solicitud aprobada exitosamente. ⚠️ La fecha requerida ya pasó.'
                    : 'Solicitud aprobada exitosamente',
                'fecha_pasada' => $fechaPasada
            ]);
        }

        return redirect()->route('admin.solicitudes.index')->with('success', 'Solicitud aprobada exitosamente');

    } catch (\Illuminate\Validation\ValidationException $e) {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }
        return back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        Log::error('Error en approve: ' . $e->getMessage());
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Error al aprobar la solicitud: ' . $e->getMessage()], 500);
        }
        return back()->with('error', $e->getMessage());
    }
}

    // ============================================================
    // HELPERS DE STOCK
    // ============================================================
    private function construirMensajeFaltaStock(array $faltantes): string
    {
        $mensaje = "Lo sentimos, no contamos con los equipos necesarios para su solicitud en este momento.\n\n";
        $mensaje .= "Detalle de items no disponibles:\n\n";

        foreach ($faltantes as $item) {
            $tipo = $item['tipo'] === 'activo' ? 'Equipo' : 'Componente';
            $mensaje .= "• {$tipo}: {$item['nombre']}\n";
            $mensaje .= "  Solicitado: {$item['solicitado']} | Disponible: {$item['disponible']}\n";
            if ($item['serial'] !== 'N/A') {
                $mensaje .= "  Serial: {$item['serial']}\n";
            }
            $mensaje .= "\n";
        }

        $mensaje .= "Por favor, intente más tarde o contacte al administrador para más información.\n\n";
        $mensaje .= "Disculpe las molestias ocasionadas.";

        return $mensaje;
    }

    private function formatearFaltantes(array $faltantes): string
    {
        $lineas = [];
        foreach ($faltantes as $item) {
            $tipo = $item['tipo'] === 'activo' ? 'Equipo' : 'Componente';
            $lineas[] = "• {$tipo}: {$item['nombre']} (Solicitado: {$item['solicitado']}, Disponible: {$item['disponible']})";
        }
        return implode("\n", $lineas);
    }

    // ============================================================
    // REJECT - RECHAZAR SOLICITUD
    // ============================================================
    public function reject(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            if (request()->ajax() || request()->wantsJson()) {
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

            // ✅ DB::transaction() con closure
            DB::transaction(function () use ($solicitud, $motivo) {
                $solicitud->update([
                    'estado_solicitud' => 'rechazada',
                    'aprobado_por' => auth()->id(),
                    'fecha_aprobacion' => now(),
                    'observaciones' => $motivo,
                    'leida_por_admin' => true,
                ]);
            });

            try {
                $this->enviarNotificacionRechazo($solicitud, $motivo);
            } catch (\Exception $e) {
                Log::error('Error al enviar notificación de rechazo: ' . $e->getMessage());
            }

            if (request()->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Solicitud rechazada exitosamente']);
            }

            return redirect()->route('admin.solicitudes.index')->with('success', 'Solicitud rechazada');

        } catch (\Exception $e) {
            Log::error('Error en reject: ' . $e->getMessage());
            if (request()->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // ============================================================
    // NOTIFICACIÓN DE APROBACIÓN
    // ============================================================
    protected function enviarNotificacionAprobacion(Solicitud $solicitud, bool $fechaPasada = false): void
    {
        $usuarioCreador = $solicitud->usuario;
        $aprobador = auth()->user();

        $itemsLista = '';
        foreach ($solicitud->detalles as $detalle) {
            $itemsLista .= " • " . ucfirst($detalle->tipo_item) . ": " . ($detalle->descripcion_personalizada ?? 'Item') . " (Cant: " . $detalle->cantidad_solicitada . ")\n";
        }

        if ($usuarioCreador && $usuarioCreador->email) {
            $mensaje = "✅ Tu solicitud #{$solicitud->id} ha sido APROBADA.\n\n" .
                "📌 Solicitud: #{$solicitud->id}\n" .
                "✅ Aprobada por: " . ($aprobador?->trabajador?->nombre ?? $aprobador?->usuario ?? 'Administrador') . "\n" .
                "📅 Fecha de aprobación: " . now()->format('d/m/Y H:i') . "\n" .
                "📅 Fecha requerida: " . date('d/m/Y', strtotime($solicitud->fecha_requerida)) . "\n" .
                "📅 Fecha fin estimada: " . date('d/m/Y', strtotime($solicitud->fecha_fin_estimada)) . "\n" .
                "🔴 Prioridad: " . ucfirst($solicitud->prioridad) . "\n\n" .
                "📦 Items aprobados:\n" . $itemsLista . "\n";

            if ($fechaPasada) {
                $mensaje .= "⚠️ IMPORTANTE: La fecha requerida ya pasó. Por favor, coordina con el departamento de informática para agilizar el proceso.\n\n";
            }

            $mensaje .= "Tu solicitud está lista para ser convertida en préstamo. Dirígete al módulo de préstamos para continuar con el proceso.";

            $this->notificacionService->enviarAUsuario(
                $usuarioCreador,
                '✅ Solicitud aprobada' . ($fechaPasada ? ' - ⚠️ Fecha vencida' : ''),
                $mensaje,
                'solicitud',
                route('admin.prestamos.index')
            );
        }

        $responsable = $solicitud->responsable;
        if ($responsable && $responsable->email) {
            $mensajeResp =
                "La solicitud #{$solicitud->id} ha sido APROBADA.\n\n" .
                "📌 Solicitud: #{$solicitud->id}\n" .
                "✅ Aprobada por: " . ($aprobador?->trabajador?->nombre ?? $aprobador?->usuario ?? 'Administrador') . "\n" .
                "📅 Fecha de aprobación: " . now()->format('d/m/Y H:i') . "\n" .
                "🔴 Prioridad: " . ucfirst($solicitud->prioridad) . "\n" .
                "📅 Fecha requerida: " . date('d/m/Y', strtotime($solicitud->fecha_requerida)) . "\n\n" .
                "Como responsable de esta solicitud, debes coordinar la entrega o gestión del préstamo.\n\n" .
                "La solicitud está lista para ser gestionada como préstamo en el sistema.";

            $this->notificacionService->enviarAResponsable(
                $responsable->email,
                $responsable->nombre,
                '✅ Solicitud aprobada - Acción requerida',
                $mensajeResp,
                'solicitud',
                route('admin.prestamos.index')
            );
        }
    }

    // ============================================================
    // NOTIFICACIÓN DE RECHAZO
    // ============================================================
    protected function enviarNotificacionRechazo(Solicitud $solicitud, string $motivo): void
    {
        $usuarioCreador = $solicitud->usuario;
        $rechazador = auth()->user();

        if ($usuarioCreador && $usuarioCreador->email) {
            $mensaje = "❌ Tu solicitud #{$solicitud->id} ha sido RECHAZADA.\n\n" .
                "📌 Solicitud: #{$solicitud->id}\n" .
                "❌ Rechazada por: " . ($rechazador?->trabajador?->nombre ?? $rechazador?->usuario ?? 'Administrador') . "\n" .
                "📅 Fecha de rechazo: " . now()->format('d/m/Y H:i') . "\n\n" .
                "📝 Motivo del rechazo:\n{$motivo}\n\n" .
                "Si tienes preguntas o necesitas aclaraciones, contacta al administrador del sistema.\n\n" .
                "Puedes crear una nueva solicitud con la información corregida si lo deseas.";

            $this->notificacionService->enviarAUsuario(
                $usuarioCreador,
                '❌ Solicitud rechazada',
                $mensaje,
                'solicitud',
                route('admin.solicitudes.index')
            );
        }

        $responsable = $solicitud->responsable;
        if ($responsable && $responsable->email) {
            $mensajeResp = "La solicitud #{$solicitud->id} ha sido RECHAZADA.\n\n" .
                "Rechazada por: " . ($rechazador?->trabajador?->nombre ?? $rechazador?->usuario ?? 'Administrador') . "\n" .
                "Fecha de rechazo: " . now()->format('d/m/Y H:i') . "\n\n" .
                "Motivo: {$motivo}\n\n" .
                "Como responsable, debes estar informado de esta decisión.";

            $this->notificacionService->enviarAResponsable(
                $responsable->email,
                $responsable->nombre,
                '❌ Solicitud rechazada',
                $mensajeResp,
                'solicitud',
                route('admin.solicitudes.index')
            );
        }
    }

    // ============================================================
    // NOTIFICACIÓN DE CANCELACIÓN
    // ============================================================
    protected function enviarNotificacionCancelacion(Solicitud $solicitud): void
    {
        $cancelador = auth()->user();
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

        $administradores = Usuario::whereHas('rol', function ($query) {
            $query->where('nombre', 'admin');
        })->where('status', 'activo')->get();

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
    }

    // ============================================================
    // ============================================================
    // MÉTODOS DE CORREOS (INTEGRADOS EN LA MISMA SECCIÓN)
    // ============================================================
    // ============================================================

    /**
     * Listar correos recibidos (bandeja)
     */
    public function correosIndex(Request $request)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $query = CorreoRecibido::with(['solicitud', 'usuario'])
            ->orderBy('received_at', 'desc');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('from_email', 'ILIKE', "%{$buscar}%")
                  ->orWhere('from_name', 'ILIKE', "%{$buscar}%")
                  ->orWhere('subject', 'ILIKE', "%{$buscar}%")
                  ->orWhere('body_text', 'ILIKE', "%{$buscar}%");
            });
        }

        if ($request->filled('filtro')) {
            if ($request->filtro === 'no_leidos') {
                $query->where('leido', false);
            } elseif ($request->filtro === 'no_procesados') {
                $query->where('procesado', false);
            } elseif ($request->filtro === 'procesados') {
                $query->where('procesado', true);
            }
        }

        $correos = $query->paginate(20);

        $noLeidos = CorreoRecibido::where('leido', false)->count();
        $noProcesados = CorreoRecibido::where('procesado', false)->count();

        return response()->json([
            'success' => true,
            'data' => $correos->items(),
            'total' => $correos->total(),
            'no_leidos' => $noLeidos,
            'no_procesados' => $noProcesados,
        ]);
    }

    /**
     * Mostrar detalle de un correo
     */
    public function correoShow($id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $correo = CorreoRecibido::with(['solicitud', 'usuario'])->findOrFail($id);

            if (!$correo->leido) {
                $correo->update(['leido' => true]);
            }

            return response()->json(['success' => true, 'data' => $correo]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Correo no encontrado'], 404);
        }
    }

    /**
     * Revisar correos manualmente (botón "Revisar ahora")
     */
    public function correosRevisar()
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $count = $this->imapService->leerCorreosNuevos();

            return response()->json([
                'success' => true,
                'message' => "✅ {$count} correo(s) nuevo(s) procesado(s)",
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al revisar correos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al revisar correos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Contador de correos para badge
     */
    public function correosContador()
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            return response()->json(['success' => false, 'no_leidos' => 0, 'no_procesados' => 0], 403);
        }

        $noLeidos = CorreoRecibido::where('leido', false)->count();
        $noProcesados = CorreoRecibido::where('procesado', false)->count();

        return response()->json([
            'success' => true,
            'no_leidos' => $noLeidos,
            'no_procesados' => $noProcesados,
        ]);
    }

    /**
     * Eliminar correo
     */
    public function correoDestroy($id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $correo = CorreoRecibido::findOrFail($id);
            $correo->delete();
            return response()->json(['success' => true, 'message' => 'Correo eliminado']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Convertir correo en solicitud (Wizard de 3 pasos)
     */
    public function correoConvertir(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $correo = CorreoRecibido::findOrFail($id);

            if ($correo->procesado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este correo ya fue convertido en solicitud',
                ], 422);
            }

            // ✅ Validación FUERA de la transacción
            $validated = $request->validate([
                'tipo_solicitante' => 'required|in:interno,externo',
                'institucion_id' => 'nullable|exists:instituciones,id',
                'departamento_id' => 'nullable|exists:departamentos,id',
                'responsable_id' => 'required|exists:responsables,id',
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

            // ✅ DB::transaction() con closure
            $solicitud = DB::transaction(function () use ($validated, $correo) {
                $nuevaSolicitud = Solicitud::create([
                    'usuario_id' => auth()->id(),
                    'tipo_solicitante' => $validated['tipo_solicitante'],
                    'institucion_id' => $validated['institucion_id'] ?? null,
                    'departamento_id' => $validated['departamento_id'] ?? null,
                    'responsable_id' => $validated['responsable_id'],
                    'oficio_adjunto' => null,
                    'fecha_solicitud' => now(),
                    'fecha_requerida' => $validated['fecha_requerida'],
                    'fecha_fin_estimada' => $validated['fecha_fin_estimada'],
                    'justificacion' => $validated['justificacion'],
                    'prioridad' => $validated['prioridad'],
                    'estado_solicitud' => 'pendiente',
                    'observaciones' => $validated['observaciones'] ?? "Creada desde correo de: {$correo->from_email}",
                    'leida_por_admin' => false,
                ]);

                foreach ($validated['items'] as $item) {
                    DetalleSolicitud::create([
                        'solicitud_id' => $nuevaSolicitud->id,
                        'tipo_item' => $item['tipo_item'],
                        'cantidad_solicitada' => (int) $item['cantidad'],
                        'descripcion_personalizada' => $item['item_descripcion'],
                        'activo_id' => null,
                        'componente_id' => null,
                        'observaciones' => $item['observaciones'] ?? null,
                    ]);
                }

                $correo->update([
                    'procesado' => true,
                    'leido' => true,
                    'solicitud_id' => $nuevaSolicitud->id,
                    'usuario_id' => auth()->id(),
                ]);

                return $nuevaSolicitud;
            });

            // Notificar al responsable
            try {
                if ($solicitud->responsable && $solicitud->responsable->email) {
                    $this->notificacionService->enviarAResponsable(
                        $solicitud->responsable->email,
                        $solicitud->responsable->nombre,
                        '📋 Nueva solicitud creada desde correo',
                        "Se ha creado una solicitud desde el correo de {$correo->from_email}.\n\nSolicitud #{$solicitud->id}",
                        'solicitud',
                        route('admin.solicitudes.index')
                    );
                }

                // Responder al remitente del correo
                if ($correo->from_email) {
                    Mail::raw(
                        "Estimado/a {$correo->from_name},\n\n" .
                        "Su solicitud ha sido recibida y está siendo procesada.\n\n" .
                        "📌 Número de solicitud: #{$solicitud->id}\n" .
                        "📅 Fecha requerida: " . date('d/m/Y', strtotime($validated['fecha_requerida'])) . "\n" .
                        "🔴 Prioridad: " . ucfirst($validated['prioridad']) . "\n\n" .
                        "Le notificaremos cuando sea aprobada o rechazada.\n\n" .
                        "Atentamente,\nDepartamento de Informática",
                        function ($message) use ($correo) {
                            $message->to($correo->from_email)
                                    ->subject('✅ Solicitud recibida - En proceso');
                        }
                    );
                }
            } catch (\Exception $e) {
                Log::error('Error al enviar notificaciones: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Solicitud creada exitosamente desde el correo',
                'solicitud_id' => $solicitud->id,
                'data' => $solicitud->load(['responsable', 'departamento', 'institucion', 'detalles']),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // ⚠️ NO DB::rollBack() aquí
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al convertir correo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}