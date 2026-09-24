<?php

// app/Http/Controllers/Admin/SolicitudController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\EnviarNotificacionSolicitudCreada;
use App\Models\Solicitud;
use App\Models\DetalleSolicitud;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Institucion;
use App\Models\Departamento;
use App\Models\Responsable;
use App\Models\Usuario;
use App\Models\CorreoRecibido;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Notificacion;
use App\Services\NotificacionService;
use App\Services\CorreoImapService;
use App\Mail\ActaRetiroMail;
use App\Mail\SolicitudRechazadaMail;
use App\Mail\NotificacionMail;
use Barryvdh\DomPDF\Facade\Pdf;
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
    // INDEX
    // ============================================================
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-solicitudes')) {
            abort(403, 'No tienes permiso para ver solicitudes');
        }

        $user = auth()->user();
        $query = Solicitud::with([
            'detalles', 'institucion', 'departamento', 'responsable',
            'usuario', 'usuario.trabajador', 'estado', 'municipio', 'parroquia'
        ]);

        if (!$user->hasPermission('aprobar-solicitudes')) {
            $query->where('usuario_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('institucion', fn($sq) => $sq->where('nombre', 'ILIKE', "%{$search}%"))
                  ->orWhereHas('departamento', fn($sq) => $sq->where('nombre', 'ILIKE', "%{$search}%"))
                  ->orWhere('justificacion', 'ILIKE', "%{$search}%")
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
                ->paginate($perPage)->appends($request->query());
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
            'solicitudes', 'activos', 'componentes',
            'instituciones', 'departamentos', 'responsables'
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

        return response()->json([
            'success' => true,
            'total' => $solicitudes->count(),
            'data' => $solicitudes
        ]);
    }

    // ============================================================
    // MARCAR COMO LEÍDA
    // ============================================================
    public function marcarLeida($id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $solicitud = Solicitud::findOrFail($id);
            $solicitud->update(['leida_por_admin' => true]);

            return response()->json(['success' => true, 'message' => 'Marcada como leída']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // PARA PRÉSTAMO
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
    // GET DETALLES
    // ============================================================
    public function getDetalles($id)
    {
        $user = auth()->user();

        if (!$user->hasPermission('ver-solicitudes') && !$user->hasPermission('ver-prestamos')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $solicitud = Solicitud::with([
                'detalles', 'institucion', 'departamento', 'responsable',
                'usuario', 'usuario.trabajador', 'estado', 'municipio', 'parroquia'
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
                'items.*.observaciones' => 'nullable|string|max:500',
                'correo_origen' => 'nullable|string|max:255',
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

            $solicitud = DB::transaction(function () use ($request, $institucionId, $departamentoId) {
                $nuevaSolicitud = Solicitud::create([
                    'usuario_id' => auth()->id(),
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

            // ✅ Despachar Job
            EnviarNotificacionSolicitudCreada::dispatch($solicitud->id, 'creada')
                ->onQueue('notifications');

            Log::info('✅ Job EnviarNotificacionSolicitudCreada despachado', [
                'solicitud_id' => $solicitud->id,
            ]);

            // Notificar al correo origen si aplica
            if ($request->filled('correo_origen')) {
                try {
                    $solicitud->load(['usuario.trabajador', 'responsable', 'departamento', 'institucion', 'detalles']);
                    $this->notificarResponsableCorreoSolicitud($solicitud, $request->correo_origen);
                } catch (\Throwable $e) {
                    Log::error('Error al notificar correo origen: ' . $e->getMessage());
                }
            }

            $solicitudCreada = Solicitud::with([
                'responsable', 'departamento', 'institucion', 'detalles',
                'estado', 'municipio', 'parroquia', 'usuario.trabajador'
            ])->find($solicitud->id);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud creada exitosamente. Se enviarán las notificaciones.',
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
            Log::error('Error en store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // UPDATE
    // ============================================================
    public function update(Request $request, $id)
    {
        try {
            $solicitud = Solicitud::findOrFail($id);

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
                'responsable_id' => 'required|exists:responsables,id',
                'estado_id' => 'nullable|exists:estados,id',
                'municipio_id' => 'nullable|exists:municipios,id',
                'parroquia_id' => 'nullable|exists:parroquias,id',
                'lugar_evento' => 'nullable|string|max:200',
            ]);

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
                'responsable', 'departamento', 'institucion', 'detalles',
                'estado', 'municipio', 'parroquia'
            ])->find($solicitud->id);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud actualizada exitosamente',
                'data' => $solicitudActualizada
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en update: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // APPROVE - APROBAR CON ACTA
    // ✅ CORREGIDO: Pasa los 3 argumentos a NotificacionMail
    // ============================================================
    public function approve(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $solicitud = Solicitud::with([
                'detalles', 'responsable', 'usuario.trabajador', 'institucion', 'departamento'
            ])->findOrFail($id);

            if ($solicitud->estado_solicitud !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aprobar solicitudes pendientes'
                ], 422);
            }

            $validated = $request->validate([
                'observaciones' => 'nullable|string|max:1000',
                'fecha_requerida' => 'nullable|date',
                'fecha_fin_estimada' => 'nullable|date',
            ]);

            DB::transaction(function () use ($solicitud, $validated) {
                $solicitud->update([
                    'estado_solicitud' => 'aprobada',
                    'aprobado_por' => auth()->id(),
                    'fecha_aprobacion' => now(),
                    'observaciones' => $validated['observaciones'] ?? $solicitud->observaciones,
                    'leida_por_admin' => true,
                    'fecha_requerida' => $validated['fecha_requerida'] ?? $solicitud->fecha_requerida,
                    'fecha_fin_estimada' => $validated['fecha_fin_estimada'] ?? $solicitud->fecha_fin_estimada,
                ]);
            });

            $solicitud->refresh();

            // ✅ DESPACHAR JOB con evento 'aprobada'
            EnviarNotificacionSolicitudCreada::dispatch($solicitud->id, 'aprobada')
                ->onQueue('notifications');

            Log::info('✅ Job EnviarNotificacionSolicitudCreada despachado (aprobada)', [
                'solicitud_id' => $solicitud->id,
            ]);

            // Generar y enviar Acta de Retiro
            try {
                $this->generarYEnviarActaRetiro($solicitud);
            } catch (\Throwable $e) {
                Log::error('Error al generar/enviar acta de retiro: ' . $e->getMessage(), [
                    'solicitud_id' => $solicitud->id,
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Notificar al solicitante interno (campanita + correo)
            try {
                if ($solicitud->usuario && $solicitud->usuario->email) {
                    Notificacion::create([
                        'usuario_id' => $solicitud->usuario->id,
                        'tipo' => 'solicitud',
                        'titulo' => '✅ Solicitud Aprobada',
                        'mensaje' => "Tu solicitud #{$solicitud->id} ha sido aprobada.",
                        'url' => route('admin.solicitudes.index'),
                        'fecha_envio' => now(),
                        'leida' => false,
                    ]);

                    // ✅ CORREGIDO: Se pasan los 3 argumentos (titulo, mensaje, nombreDestinatario)
                    Mail::to($solicitud->usuario->email)->send(
                        new NotificacionMail(
                            '✅ Solicitud Aprobada',
                            "Estimado/a " . ($solicitud->usuario->trabajador?->nombre ?? $solicitud->usuario->usuario) . ",\n\n" .
                            "Tu solicitud #{$solicitud->id} ha sido APROBADA.\n\n" .
                            "Pronto recibirás el Acta de Retiro.\n\nGracias.",
                            $solicitud->usuario->trabajador?->nombre ?? $solicitud->usuario->usuario
                        )
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Error al notificar aprobación al solicitante: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Solicitud aprobada exitosamente',
                'data' => $solicitud->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al aprobar solicitud: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // REJECT - RECHAZAR
    // ✅ CORREGIDO: Pasa los 3 argumentos a NotificacionMail
    // ============================================================
    public function reject(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $solicitud = Solicitud::with([
                'responsable', 'usuario.trabajador', 'institucion', 'departamento'
            ])->findOrFail($id);

            if ($solicitud->estado_solicitud !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden rechazar solicitudes pendientes'
                ], 422);
            }

            $validated = $request->validate([
                'motivo' => 'required|string|min:10|max:1000',
            ]);

            DB::transaction(function () use ($solicitud, $validated) {
                $solicitud->update([
                    'estado_solicitud' => 'rechazada',
                    'aprobado_por' => auth()->id(),
                    'fecha_aprobacion' => now(),
                    'observaciones' => $validated['motivo'],
                    'leida_por_admin' => true,
                ]);
            });

            $solicitud->refresh();

            // ✅ DESPACHAR JOB con evento 'rechazada'
            EnviarNotificacionSolicitudCreada::dispatch($solicitud->id, 'rechazada')
                ->onQueue('notifications');

            Log::info('✅ Job EnviarNotificacionSolicitudCreada despachado (rechazada)', [
                'solicitud_id' => $solicitud->id,
            ]);

            // Notificar al solicitante interno (campanita + correo)
            try {
                if ($solicitud->usuario && $solicitud->usuario->email) {
                    Notificacion::create([
                        'usuario_id' => $solicitud->usuario->id,
                        'tipo' => 'solicitud',
                        'titulo' => '❌ Solicitud Rechazada',
                        'mensaje' => "Tu solicitud #{$solicitud->id} ha sido rechazada. Motivo: {$validated['motivo']}",
                        'url' => route('admin.solicitudes.index'),
                        'fecha_envio' => now(),
                        'leida' => false,
                    ]);

                    // ✅ CORREGIDO: Se pasan los 3 argumentos
                    Mail::to($solicitud->usuario->email)->send(
                        new NotificacionMail(
                            '❌ Solicitud Rechazada',
                            "Estimado/a " . ($solicitud->usuario->trabajador?->nombre ?? $solicitud->usuario->usuario) . ",\n\n" .
                            "Tu solicitud #{$solicitud->id} ha sido RECHAZADA.\n\n" .
                            "Motivo: {$validated['motivo']}\n\nGracias.",
                            $solicitud->usuario->trabajador?->nombre ?? $solicitud->usuario->usuario
                        )
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Error al notificar rechazo al solicitante: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Solicitud rechazada exitosamente',
                'data' => $solicitud->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al rechazar solicitud: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // CANCEL
    // ============================================================
    public function cancel($id)
    {
        try {
            $solicitud = Solicitud::with(['responsable', 'usuario.trabajador'])->findOrFail($id);

            if (auth()->id() !== $solicitud->usuario_id
                && !auth()->user()->hasPermission('aprobar-solicitudes')) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }

            if (!in_array($solicitud->estado_solicitud, ['pendiente', 'aprobada'])) {
                return response()->json(['success' => false, 'message' => 'No se puede cancelar esta solicitud'], 422);
            }

            $estadoAnterior = $solicitud->estado_solicitud;
            $solicitud->update(['estado_solicitud' => 'cancelada']);
            $solicitud->refresh();

            // ✅ DESPACHAR JOB con evento 'cancelada'
            if ($estadoAnterior === 'aprobada') {
                EnviarNotificacionSolicitudCreada::dispatch($solicitud->id, 'cancelada')
                    ->onQueue('notifications');

                Log::info('✅ Job EnviarNotificacionSolicitudCreada despachado (cancelada)', [
                    'solicitud_id' => $solicitud->id,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Solicitud cancelada exitosamente']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // DESTROY
    // ============================================================
    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('aprobar-solicitudes')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar solicitudes'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $solicitud = Solicitud::findOrFail($id);
            $solicitud->detalles()->delete();
            $solicitud->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Solicitud eliminada exitosamente']);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Solicitud no encontrada'], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar solicitud: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // CORREOS
    // ============================================================
    public function correosIndex(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $query = CorreoRecibido::deTipoSolicitud()
            ->with(['solicitud', 'usuario'])
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
            if ($request->filtro === 'no_leidos') $query->where('leido', false);
            elseif ($request->filtro === 'no_procesados') $query->where('procesado', false);
            elseif ($request->filtro === 'procesados') $query->where('procesado', true);
        }

        $correos = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $correos->items(),
            'total' => $correos->total(),
            'no_leidos' => CorreoRecibido::deTipoSolicitud()->where('leido', false)->count(),
            'no_procesados' => CorreoRecibido::deTipoSolicitud()->where('procesado', false)->count(),
        ]);
    }

    public function correoShow($id)
    {
        if (!auth()->user()->hasPermission('ver-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $correo = CorreoRecibido::deTipoSolicitud()
                ->with(['solicitud', 'usuario'])
                ->findOrFail($id);

            if (!$correo->leido) {
                $correo->update(['leido' => true]);
            }

            return response()->json(['success' => true, 'data' => $correo]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Correo no encontrado'], 404);
        }
    }

    public function correosRevisar(CorreoImapService $imapService)
    {
        if (!auth()->user()->hasPermission('ver-solicitudes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $count = $imapService->leerCorreosNuevos();
            return response()->json([
                'success' => true,
                'message' => "✅ {$count} correo(s) nuevo(s) procesado(s)",
                'count' => $count,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al revisar correos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al revisar correos: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function correosContador()
    {
        if (!auth()->user()->hasPermission('ver-solicitudes')) {
            return response()->json(['success' => false, 'no_leidos' => 0, 'no_procesados' => 0], 403);
        }

        return response()->json([
            'success' => true,
            'no_leidos' => CorreoRecibido::deTipoSolicitud()->where('leido', false)->count(),
            'no_procesados' => CorreoRecibido::deTipoSolicitud()->where('procesado', false)->count(),
        ]);
    }

    // ============================================================
    // CONVERTIR CORREO EN SOLICITUD (Wizard)
    // ============================================================
    public function correoConvertir(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('crear-solicitud')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $correo = CorreoRecibido::deTipoSolicitud()->findOrFail($id);

            if ($correo->procesado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este correo ya fue convertido en una solicitud',
                ], 422);
            }

            $validated = $request->validate([
                'tipo_solicitante' => 'required|in:interno,externo',
                'departamento_id' => 'nullable|exists:departamentos,id',
                'institucion_id' => 'nullable|exists:instituciones,id',
                'responsable_id' => 'required|exists:responsables,id',
                'fecha_requerida' => 'required|date|after_or_equal:today',
                'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
                'prioridad' => 'required|in:baja,normal,alta,urgente',
                'justificacion' => 'required|string|min:20|max:1000',
                'observaciones' => 'nullable|string|max:500',
                'items' => 'required|array|min:1',
                'items.*.tipo_item' => 'required|in:activo,componente',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.item_descripcion' => 'required|string|max:255',
                'items.*.observaciones' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();

            $institucionId = $request->tipo_solicitante === 'externo'
                ? ($request->institucion_id ?: null) : null;
            $departamentoId = $request->tipo_solicitante === 'interno'
                ? ($request->departamento_id ?: null) : null;

            $solicitud = Solicitud::create([
                'usuario_id' => auth()->id(),
                'tipo_solicitante' => $validated['tipo_solicitante'],
                'institucion_id' => $institucionId,
                'departamento_id' => $departamentoId,
                'responsable_id' => $validated['responsable_id'],
                'oficio_adjunto' => null,
                'fecha_solicitud' => now(),
                'fecha_requerida' => $validated['fecha_requerida'],
                'fecha_fin_estimada' => $validated['fecha_fin_estimada'],
                'justificacion' => $validated['justificacion'],
                'prioridad' => $validated['prioridad'],
                'estado_solicitud' => 'pendiente',
                'observaciones' => $validated['observaciones'] ?? null,
                'leida_por_admin' => false,
            ]);

            foreach ($validated['items'] as $item) {
                DetalleSolicitud::create([
                    'solicitud_id' => $solicitud->id,
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
                'solicitud_id' => $solicitud->id,
                'usuario_id' => auth()->id(),
            ]);

            DB::commit();

            // ✅ Despachar Job con evento 'creada'
            EnviarNotificacionSolicitudCreada::dispatch($solicitud->id, 'creada')
                ->onQueue('notifications');

            Log::info('✅ Job EnviarNotificacionSolicitudCreada despachado desde correo', [
                'solicitud_id' => $solicitud->id,
                'correo_id' => $correo->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud creada exitosamente desde el correo',
                'solicitud_id' => $solicitud->id,
                'data' => $solicitud->load(['usuario.trabajador', 'responsable', 'departamento', 'institucion', 'detalles']),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al convertir correo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // NOTIFICAR AL RESPONSABLE - CAMBIO DE ESTADO
    // ✅ CORREGIDO: Pasa los 3 argumentos a NotificacionMail
    // ============================================================
    protected function notificarResponsableSolicitudEstado(Solicitud $solicitud, string $estado, ?string $motivo = null): void
    {
        try {
            $responsable = $solicitud->responsable;

            if (!$responsable || !$responsable->email) {
                Log::warning("No se pudo notificar al responsable: sin email", [
                    'solicitud_id' => $solicitud->id,
                    'estado' => $estado,
                ]);
                return;
            }

            $solicitud->loadMissing(['institucion', 'departamento', 'usuario.trabajador', 'detalles']);

            // 🔴 RECHAZADA → Mailable HTML
            if ($estado === 'rechazada') {
                try {
                    Mail::to($responsable->email)->send(
                        new SolicitudRechazadaMail($solicitud, $motivo ?? 'No especificado')
                    );
                    Log::info("📧 ✅ Correo de rechazo (HTML) enviado al responsable", [
                        'solicitud_id' => $solicitud->id,
                        'email' => $responsable->email,
                    ]);
                } catch (\Throwable $e) {
                    Log::error("❌ Error al enviar correo de rechazo: " . $e->getMessage());
                }
                return;
            }

            $entidad = $solicitud->tipo_solicitante === 'interno'
                ? ($solicitud->departamento?->nombre ?? 'Departamento')
                : ($solicitud->institucion?->nombre ?? 'Institución');

            $solicitante = $solicitud->usuario?->trabajador
                ? trim($solicitud->usuario->trabajador->nombre . ' ' . $solicitud->usuario->trabajador->apellido)
                : ($solicitud->usuario?->usuario ?? 'Usuario');

            $itemsTexto = '';
            foreach ($solicitud->detalles as $det) {
                $itemsTexto .= " • " . ($det->descripcion_personalizada ?? 'Item') . " (Cant: {$det->cantidad_solicitada})\n";
            }

            $fechaRequerida = $solicitud->fecha_requerida
                ? \Carbon\Carbon::parse($solicitud->fecha_requerida)->format('d/m/Y') : 'No especificada';
            $fechaFin = $solicitud->fecha_fin_estimada
                ? \Carbon\Carbon::parse($solicitud->fecha_fin_estimada)->format('d/m/Y') : 'No especificada';

            if ($estado === 'aprobada') {
                $titulo = '✅ Solicitud de Préstamo APROBADA';
                $mensaje =
                    "✅ SOLICITUD DE PRÉSTAMO APROBADA\n\n" .
                    "Estimado/a {$responsable->nombre},\n\n" .
                    "Le informamos que su solicitud de préstamo ha sido APROBADA.\n\n" .
                    "📌 Detalles:\n" .
                    " • Código: SOL-" . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . "\n" .
                    " • Entidad: {$entidad}\n" .
                    " • Solicitante: {$solicitante}\n" .
                    " • Fecha requerida: {$fechaRequerida}\n" .
                    " • Fecha fin estimada: {$fechaFin}\n" .
                    " • Prioridad: " . strtoupper($solicitud->prioridad ?? 'normal') . "\n\n" .
                    "📦 Items:\n" . ($itemsTexto ?: " • Sin items\n") . "\n" .
                    ($motivo ? "📝 Observaciones:\n {$motivo}\n\n" : "") .
                    "📍 Próximos pasos:\n 1. Informática se contactará.\n 2. Firme el acta.\n 3. Coordine la entrega.\n\n" .
                    "Gracias.";

            } elseif ($estado === 'cancelada') {
                $titulo = '🚫 Solicitud de Préstamo CANCELADA';
                $mensaje =
                    "🚫 SOLICITUD DE PRÉSTAMO CANCELADA\n\n" .
                    "Estimado/a {$responsable->nombre},\n\n" .
                    "La solicitud ha sido CANCELADA.\n\n" .
                    "📌 Detalles:\n" .
                    " • Código: SOL-" . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . "\n" .
                    " • Entidad: {$entidad}\n" .
                    " • Motivo: " . ($motivo ?? 'Cancelada por el solicitante') . "\n\n" .
                    "Si no solicitó esta cancelación, contacte al Departamento de Informática.";
            } else {
                return;
            }

            Notificacion::create([
                'usuario_id' => null,
                'tipo' => 'solicitud',
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'url' => null,
                'fecha_envio' => now(),
                'leida' => false,
            ]);

            // ✅ CORREGIDO: Se pasan los 3 argumentos
            Mail::to($responsable->email)->send(
                new NotificacionMail(
                    $titulo,
                    $mensaje,
                    $responsable->nombre
                )
            );

            Log::info("📧 ✅ Notificación '{$estado}' enviada al responsable", [
                'solicitud_id' => $solicitud->id,
                'email' => $responsable->email,
            ]);

        } catch (\Throwable $e) {
            Log::error('❌ Error al notificar al responsable: ' . $e->getMessage(), [
                'solicitud_id' => $solicitud->id,
                'estado' => $estado,
                'error_clase' => get_class($e),
            ]);
        }
    }

    // ============================================================
    // NOTIFICAR AL CORREO ORIGEN
    // ✅ CORREGIDO: Pasa los 3 argumentos a NotificacionMail
    // ============================================================
    protected function notificarResponsableCorreoSolicitud(Solicitud $solicitud, string $correoOrigen): void
    {
        try {
            $solicitud->loadMissing(['institucion', 'departamento', 'usuario.trabajador', 'detalles']);

            $entidad = $solicitud->tipo_solicitante === 'interno'
                ? ($solicitud->departamento?->nombre ?? 'Departamento')
                : ($solicitud->institucion?->nombre ?? 'Institución');

            $fechaRequerida = $solicitud->fecha_requerida
                ? \Carbon\Carbon::parse($solicitud->fecha_requerida)->format('d/m/Y') : 'No especificada';

            $itemsTexto = '';
            foreach ($solicitud->detalles as $det) {
                $itemsTexto .= " • " . ($det->descripcion_personalizada ?? 'Item') . " (Cant: {$det->cantidad_solicitada})\n";
            }

            $titulo = '📋 Solicitud de Préstamo Recibida';
            $mensaje =
                "✅ SOLICITUD DE PRÉSTAMO RECIBIDA\n\n" .
                "Estimado/a usuario,\n\n" .
                "Hemos recibido su solicitud enviada por correo electrónico.\n\n" .
                "📌 Detalles:\n" .
                " • Código: SOL-" . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . "\n" .
                " • Entidad: {$entidad}\n" .
                " • Fecha requerida: {$fechaRequerida}\n" .
                " • Prioridad: " . strtoupper($solicitud->prioridad ?? 'normal') . "\n\n" .
                "📦 Items:\n" . ($itemsTexto ?: " • Sin items\n") . "\n" .
                "⏳ Estado: PENDIENTE DE APROBACIÓN\n\n" .
                "Le notificaremos cuando sea APROBADA o RECHAZADA.\n\nGracias.";

            Notificacion::create([
                'usuario_id' => null,
                'tipo' => 'solicitud',
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'url' => null,
                'fecha_envio' => now(),
                'leida' => false,
            ]);

            // ✅ CORREGIDO: Se pasan los 3 argumentos
            Mail::to($correoOrigen)->send(
                new NotificacionMail(
                    $titulo,
                    $mensaje,
                    'Solicitante'
                )
            );

            Log::info("📧 ✅ Confirmación enviada al correo origen", [
                'solicitud_id' => $solicitud->id,
                'correo_origen' => $correoOrigen,
            ]);

        } catch (\Throwable $e) {
            Log::error('❌ Error al notificar al correo origen: ' . $e->getMessage(), [
                'solicitud_id' => $solicitud->id,
                'correo_origen' => $correoOrigen,
            ]);
        }
    }

    // ============================================================
    // GENERAR Y ENVIAR ACTA DE RETIRO
    // ============================================================
    protected function generarYEnviarActaRetiro(Solicitud $solicitud): void
    {
        $solicitud->load([
            'responsable', 'departamento', 'institucion', 'detalles', 'usuario.trabajador'
        ]);

        $dataActa = $this->prepararDatosActaRetiro($solicitud);

        $pdf = Pdf::loadView('admin.actas.retiro-pdf', [
            'data' => $dataActa,
            'solicitud' => $solicitud
        ]);
        $pdf->setPaper('letter', 'portrait');

        $this->enviarActaRetiroPorCorreo($solicitud, $pdf);
    }

    protected function prepararDatosActaRetiro(Solicitud $solicitud): array
    {
        $items = [];
        foreach ($solicitud->detalles as $detalle) {
            $items[] = [
                'tipo_item' => $detalle->tipo_item ?? 'Item',
                'descripcion' => $detalle->descripcion_personalizada
                    ?? $detalle->descripcion_item
                    ?? 'Item sin descripción',
                'cantidad' => $detalle->cantidad_solicitada ?? 1,
            ];
        }

        $encargado = $this->obtenerEncargadoInformatica();

        $entidad = 'No especificada';
        if ($solicitud->tipo_solicitante === 'interno' && $solicitud->departamento) {
            $entidad = $solicitud->departamento->nombre;
        } elseif ($solicitud->tipo_solicitante === 'externo' && $solicitud->institucion) {
            $entidad = $solicitud->institucion->nombre;
        }

        $responsable = $solicitud->responsable;

        return [
            'numero_acta' => 'ACTA-RETIRO-' . date('Ym') . '-' . str_pad($solicitud->id, 4, '0', STR_PAD_LEFT),
            'codigo_solicitud' => 'SOL-' . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT),
            'fecha' => now()->format('d/m/Y'),
            'institucion' => $entidad,
            'responsable_nombre' => $responsable->nombre ?? 'No especificado',
            'responsable_cargo' => $responsable->cargo ?? 'Responsable',
            'responsable_documento' => $responsable->documento ?? 'N/A',
            'responsable_telefono' => $responsable->telefono ?? 'N/A',
            'encargado_nombre' => $encargado['nombre'],
            'encargado_cargo' => $encargado['cargo'],
            'items' => $items,
        ];
    }

    protected function obtenerEncargadoInformatica(): array
    {
        $deptoInformatica = Departamento::where('nombre', 'ILIKE', '%informatica%')
            ->orWhere('nombre', 'ILIKE', '%informática%')
            ->orWhere('nombre', 'ILIKE', '%sistemas%')
            ->orWhere('nombre', 'ILIKE', '%tecnologia%')
            ->first();

        if ($deptoInformatica) {
            $responsable = Responsable::where('departamento_id', $deptoInformatica->id)
                ->where('activo', true)
                ->first();

            if ($responsable) {
                return [
                    'nombre' => $responsable->nombre,
                    'cargo' => $responsable->cargo ?? 'Jefe de Informática',
                ];
            }
        }

        return [
            'nombre' => 'Departamento de Informática',
            'cargo' => 'Dirección de Informática',
        ];
    }

    protected function enviarActaRetiroPorCorreo(Solicitud $solicitud, $pdf): void
    {
        $responsable = $solicitud->responsable;

        if (!$responsable || !$responsable->email) {
            Log::warning("No se pudo enviar acta de retiro: sin email", [
                'solicitud_id' => $solicitud->id,
            ]);
            return;
        }

        $entidad = $solicitud->institucion?->nombre
            ?? $solicitud->departamento?->nombre
            ?? 'su entidad';

        $mensaje = "Le informamos que su solicitud de préstamo #{$solicitud->id} ha sido APROBADA.\n\n";
        $mensaje .= "Adjunto encontrará el Acta de Retiro correspondiente, la cual debe:\n";
        $mensaje .= "1. Imprimir\n2. Firmar\n3. Presentar en el Departamento de Informática al momento de retirar los equipos\n\n";
        $mensaje .= "Entidad solicitante: {$entidad}\n";
        $mensaje .= "Fecha de aprobación: " . now()->format('d/m/Y H:i') . "\n\n";
        $mensaje .= "Preséntese con su cédula y el acta firmada.\n\nGracias.";

        try {
            Mail::to($responsable->email)->send(new ActaRetiroMail($solicitud, $pdf, $mensaje));
            Log::info("✅ Acta de retiro enviada", [
                'solicitud_id' => $solicitud->id,
                'email' => $responsable->email,
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Error al enviar acta de retiro: " . $e->getMessage(), [
                'solicitud_id' => $solicitud->id,
                'email' => $responsable->email,
            ]);
            throw $e;
        }
    }
}