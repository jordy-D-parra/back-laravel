<?php

// app/Http/Controllers/Admin/FichaSoporteController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FichaSoporte;
use App\Models\FichaSoporteDetalle;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Usuario;
use App\Models\Estatus;
use App\Models\CorreoRecibido;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Categoria;
use App\Models\Institucion;
use App\Models\Responsable;
use App\Services\CorreoImapService;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FichaSoporteController extends Controller
{
    // ============================================================
    // INDEX - Lista de fichas de soporte
    // ============================================================
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-fichas-soporte')) {
            abort(403, 'No tienes permiso para ver fichas de soporte');
        }

        $query = FichaSoporte::with(['activo.modelo.marca', 'tecnico.trabajador', 'detalles']);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('activo', fn($q2) => $q2->where('serial', 'like', "%{$buscar}%"))
                    ->orWhere('tecnico_nombre', 'like', "%{$buscar}%")
                    ->orWhere('usuario_reporta_nombre', 'like', "%{$buscar}%")
                    ->orWhere('diagnostico', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $fichas = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($fichas);
        }

        $estatusReparacion = Estatus::where('descripcion', 'En reparación')->first();

        $activosDisponibles = Activo::with('modelo.marca')
            ->whereDoesntHave('fichasSoporte', function ($q) {
                $q->where('estado', 'en_proceso');
            })
            ->when($estatusReparacion, function ($q) use ($estatusReparacion) {
                $q->where('id_estatus', '!=', $estatusReparacion->id);
            })
            ->orderBy('serial')
            ->get();

        $tecnicos = Usuario::whereHas('rol', function ($q) {
            $q->whereIn('nombre', ['admin', 'ingeniero', 'tecnico']);
        })->with('trabajador')->orderBy('usuario')->get();

        $totalFichas = FichaSoporte::count();
        $enProceso = FichaSoporte::where('estado', 'en_proceso')->count();
        $finalizados = FichaSoporte::where('estado', 'finalizado')->count();
        $equiposReparacion = Activo::whereHas('estatus', function ($q) {
            $q->where('descripcion', 'En reparación');
        })->count();

        // Solo correos de tipo SOPORTE
        $correosNoLeidos = CorreoRecibido::deTipoSoporte()->where('leido', false)->count();
        $correosNoProcesados = CorreoRecibido::deTipoSoporte()->where('procesado', false)->count();

        return view('admin.soporte.index', compact(
            'fichas', 'activosDisponibles', 'tecnicos', 'totalFichas',
            'enProceso', 'finalizados', 'equiposReparacion',
            'correosNoLeidos', 'correosNoProcesados'
        ));
    }

    // ============================================================
    // SHOW - Detalle de ficha
    // ============================================================
    public function show($id)
    {
        if (!auth()->user()->hasPermission('ver-fichas-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $ficha = FichaSoporte::with([
                'activo.modelo.marca',
                'activo.institucion',
                'activo.responsable',
                'tecnico.trabajador',
                'detalles.componente',
                'correo'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $ficha
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ficha no encontrada'
            ], 404);
        }
    }

    // ============================================================
    // UPDATE - Editar ficha
    // ============================================================
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('editar-ficha-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $ficha = FichaSoporte::findOrFail($id);

            if ($ficha->estado === 'finalizado') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede editar una ficha finalizada'
                ], 422);
            }

            $validated = $request->validate([
                'diagnostico' => 'nullable|string',
                'observaciones' => 'nullable|string',
            ]);

            $ficha->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Ficha actualizada correctamente'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // ACEPTAR FICHA (en proceso → aceptada)
    // ============================================================
    public function aceptar(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('editar-ficha-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $ficha = FichaSoporte::with(['activo.institucion', 'activo.responsable', 'activo.modelo.marca'])->findOrFail($id);

            if ($ficha->estado === 'finalizado') {
                return response()->json(['success' => false, 'message' => 'No se puede aceptar una ficha finalizada'], 422);
            }

            if ($ficha->estado === 'aceptada') {
                return response()->json(['success' => false, 'message' => 'La ficha ya está aceptada'], 422);
            }

            $validated = $request->validate([
                'observaciones_aceptacion' => 'nullable|string',
                'fecha_requerida_entrega' => 'nullable|date',
            ]);

            $ficha->update([
                'estado' => 'aceptada',
                'fecha_aceptacion' => now(),
                'observaciones' => $validated['observaciones_aceptacion'] ?? $ficha->observaciones,
                'fecha_requerida_entrega' => $validated['fecha_requerida_entrega'] ?? $ficha->fecha_requerida_entrega,
            ]);

            try {
                $this->notificarFichaAceptada($ficha);
            } catch (\Throwable $e) {
                Log::error('Error al notificar ficha aceptada: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Ficha aceptada exitosamente',
                'data' => $ficha
            ]);

        } catch (\Throwable $e) {
            Log::error('Error al aceptar ficha: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // RECHAZAR FICHA (en proceso → rechazada)
    // ============================================================
    public function rechazar(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('editar-ficha-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $ficha = FichaSoporte::with(['activo.institucion', 'activo.responsable', 'activo.modelo.marca'])->findOrFail($id);

            if ($ficha->estado === 'finalizado') {
                return response()->json(['success' => false, 'message' => 'No se puede rechazar una ficha finalizada'], 422);
            }

            if ($ficha->estado === 'rechazada') {
                return response()->json(['success' => false, 'message' => 'La ficha ya está rechazada'], 422);
            }

            $validated = $request->validate([
                'motivo_rechazo' => 'required|string|min:10',
            ]);

            DB::beginTransaction();

            $ficha->update([
                'estado' => 'rechazada',
                'fecha_rechazo' => now(),
                'motivo_rechazo' => $validated['motivo_rechazo'],
            ]);

            $activo = Activo::find($ficha->activo_id);
            if ($activo) {
                $estatusDisponible = Estatus::where('descripcion', 'Disponible')->first();
                if ($estatusDisponible) {
                    $activo->update(['id_estatus' => $estatusDisponible->id]);
                }
            }

            DB::commit();

            try {
                $this->notificarFichaRechazada($ficha);
            } catch (\Throwable $e) {
                Log::error('Error al notificar ficha rechazada: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Ficha rechazada exitosamente',
                'data' => $ficha
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al rechazar ficha: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // CLOSE - Finalizar ficha
    // ============================================================
    public function close(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('cerrar-ficha-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $ficha = FichaSoporte::findOrFail($id);

            if ($ficha->estado === 'finalizado') {
                return response()->json([
                    'success' => false,
                    'message' => 'La ficha ya está finalizada'
                ], 422);
            }

            $validated = $request->validate([
                'trabajo_realizado' => 'nullable|string',
                'observaciones_finales' => 'nullable|string',
                'detalles' => 'nullable|array',
            ]);

            DB::beginTransaction();

            if (isset($validated['detalles']) && is_array($validated['detalles'])) {
                foreach ($validated['detalles'] as $detalleId => $det) {
                    if (isset($det['estado_salida'])) {
                        FichaSoporteDetalle::where('id', $detalleId)->update([
                            'estado_salida' => $det['estado_salida'],
                            'observaciones' => $det['observaciones'] ?? null,
                        ]);
                    }
                }
            }

            $ficha->update([
                'trabajo_realizado' => $validated['trabajo_realizado'] ?? null,
                'observaciones' => $validated['observaciones_finales'] ?? $ficha->observaciones,
                'fecha_salida' => now(),
                'estado' => 'finalizado',
            ]);

            $activo = Activo::find($ficha->activo_id);
            if ($activo) {
                $estatusDisponible = Estatus::where('descripcion', 'Disponible')->first();
                if ($estatusDisponible) {
                    $activo->update(['id_estatus' => $estatusDisponible->id]);
                }
            }

            DB::commit();

            try {
                $this->notificarFichaFinalizada($ficha);
            } catch (\Throwable $e) {
                Log::error('Error al notificar ficha finalizada: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Ficha finalizada exitosamente'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // DESTROY - Eliminar ficha
    // ============================================================
    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('eliminar-ficha-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $ficha = FichaSoporte::findOrFail($id);
            $estabaEnProceso = in_array($ficha->estado, ['en_proceso', 'aceptada']);

            if ($estabaEnProceso) {
                try {
                    $this->notificarFichaEliminada($ficha);
                } catch (\Throwable $e) {
                    Log::error('Error al notificar ficha eliminada: ' . $e->getMessage());
                }
            }

            if ($estabaEnProceso) {
                $activo = Activo::find($ficha->activo_id);
                if ($activo) {
                    $estatusDisponible = Estatus::where('descripcion', 'Disponible')->first();
                    if ($estatusDisponible) {
                        $activo->update(['id_estatus' => $estatusDisponible->id]);
                    }
                }
            }

            $ficha->detalles()->delete();
            $ficha->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ficha eliminada correctamente'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // GET COMPONENTES DETALLE
    // ============================================================
    public function getComponentesDetalle($id)
    {
        try {
            $detalles = FichaSoporteDetalle::where('ficha_soporte_id', $id)
                ->with('componente')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $detalles
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // ============ CORREOS DE SOPORTE TÉCNICO ====================
    // ============================================================
    public function correosIndex(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-fichas-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $query = CorreoRecibido::deTipoSoporte()
            ->with(['fichaSoporte', 'usuario'])
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

        return response()->json([
            'success' => true,
            'data' => $correos->items(),
            'total' => $correos->total(),
            'no_leidos' => CorreoRecibido::deTipoSoporte()->where('leido', false)->count(),
            'no_procesados' => CorreoRecibido::deTipoSoporte()->where('procesado', false)->count(),
        ]);
    }

    public function correoShow($id)
    {
        if (!auth()->user()->hasPermission('ver-fichas-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $correo = CorreoRecibido::deTipoSoporte()
                ->with(['fichaSoporte', 'usuario'])
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
        if (!auth()->user()->hasPermission('ver-fichas-soporte')) {
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
        if (!auth()->user()->hasPermission('ver-fichas-soporte')) {
            return response()->json(['success' => false, 'no_leidos' => 0, 'no_procesados' => 0], 403);
        }

        return response()->json([
            'success' => true,
            'no_leidos' => CorreoRecibido::deTipoSoporte()->where('leido', false)->count(),
            'no_procesados' => CorreoRecibido::deTipoSoporte()->where('procesado', false)->count(),
        ]);
    }

    // ============================================================
    // CONVERTIR correo en Ficha de Soporte (Wizard)
    // ============================================================
    public function correoConvertir(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('crear-ficha-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $correo = CorreoRecibido::deTipoSoporte()->findOrFail($id);

            if ($correo->procesado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este correo ya fue convertido en ficha de soporte',
                ], 422);
            }

            if ($correo->ficha_soporte_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este correo ya tiene una ficha asociada',
                    'ficha_id' => $correo->ficha_soporte_id,
                ], 422);
            }

            $hoy = now()->startOfDay()->format('Y-m-d');

            $validated = $request->validate([
                'tipo_equipo' => 'required|in:existente,nuevo',
                'activo_id' => 'required_if:tipo_equipo,existente|nullable|exists:activos,id',
                'nuevo_equipo' => 'required_if:tipo_equipo,nuevo|nullable|array',
                'nuevo_equipo.serial' => 'required_if:tipo_equipo,nuevo|nullable|string|max:100|unique:activos,serial',
                'nuevo_equipo.marca' => 'required_if:tipo_equipo,nuevo|nullable|string|max:100',
                'nuevo_equipo.modelo_nombre' => 'required_if:tipo_equipo,nuevo|nullable|string|max:100',
                'nuevo_equipo.categoria_id' => 'required_if:tipo_equipo,nuevo|nullable|exists:categorias,id',
                'nuevo_equipo.institucion_id' => 'required_if:tipo_equipo,nuevo|nullable|exists:instituciones,id',
                'nuevo_equipo.responsable_id' => 'required_if:tipo_equipo,nuevo|nullable|exists:responsables,id',
                'nuevo_equipo.ubicacion' => 'nullable|string|max:100',
                'nuevo_equipo.fecha_adquisicion' => 'nullable|date',
                'tecnico_id' => 'nullable|exists:usuarios,id',
                'tecnico_nombre' => 'nullable|string|max:150',
                'usuario_reporta_nombre' => 'required|string|max:150',
                'diagnostico' => 'required|string|min:10',
                'observaciones' => 'nullable|string',
                'fecha_requerida_entrega' => 'required|date|after_or_equal:' . $hoy,
            ]);

            DB::beginTransaction();

            $activoId = null;

            if ($validated['tipo_equipo'] === 'nuevo' && !empty($validated['nuevo_equipo'])) {
                $nuevo = $validated['nuevo_equipo'];
                $marca = Marca::firstOrCreate(['nombre' => $nuevo['marca']], ['activo' => true]);
                $modelo = Modelo::firstOrCreate(
                    ['marca_id' => $marca->id, 'nombre' => $nuevo['modelo_nombre']],
                    ['categoria_id' => $nuevo['categoria_id'], 'activo' => true]
                );

                $estatusReparacion = Estatus::where('descripcion', 'En reparación')->first();

                $activo = Activo::create([
                    'serial' => $nuevo['serial'],
                    'modelo_id' => $modelo->id,
                    'id_estatus' => $estatusReparacion?->id,
                    'institucion_id' => $nuevo['institucion_id'],
                    'responsable_id' => $nuevo['responsable_id'],
                    'ubicacion' => $nuevo['ubicacion'] ?? 'Taller de reparación',
                    'fecha_adquisicion' => $nuevo['fecha_adquisicion'] ?? null,
                ]);
                $activoId = $activo->id;
            } else {
                $activoId = $validated['activo_id'];
                $activo = Activo::find($activoId);
                $estatusReparacion = Estatus::where('descripcion', 'En reparación')->first();
                if ($activo && $estatusReparacion) {
                    $activo->update(['id_estatus' => $estatusReparacion->id]);
                }
            }

            $ficha = FichaSoporte::create([
                'activo_id' => $activoId,
                'correo_id' => $correo->id,
                'tecnico_id' => $validated['tecnico_id'] ?? null,
                'tecnico_nombre' => $validated['tecnico_nombre'] ?? 'No asignado',
                'usuario_reporta_id' => auth()->id(),
                'usuario_reporta_nombre' => $validated['usuario_reporta_nombre'],
                'fecha_ingreso' => now(),
                'fecha_requerida_entrega' => $validated['fecha_requerida_entrega'],
                'diagnostico' => $validated['diagnostico'],
                'observaciones' => $validated['observaciones'] ?? null,
                'estado' => 'en_proceso',
                'origen' => 'correo',
            ]);

            $correo->update([
                'procesado' => true,
                'leido' => true,
                'ficha_soporte_id' => $ficha->id,
                'usuario_id' => auth()->id(),
            ]);

            DB::commit();

            try {
                $this->notificarFichaSoporteCreada($ficha, $correo);
            } catch (\Throwable $e) {
                Log::error('Error al notificar ficha de soporte: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Ficha de soporte creada exitosamente desde el correo',
                'ficha_id' => $ficha->id,
                'data' => $ficha->load(['activo', 'tecnico', 'correo']),
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
    // HELPER: Obtener el responsable con email (del activo o de la institución)
    // ============================================================
    protected function obtenerResponsableConEmail(?Activo $activo): ?Responsable
    {
        if (!$activo) {
            Log::warning('obtenerResponsableConEmail: activo es null');
            return null;
        }

        // 1. Intentar con el responsable directo del activo
        $responsable = $activo->responsable;
        if ($responsable && $responsable->email) {
            Log::info('obtenerResponsableConEmail: usando responsable directo del activo', [
                'activo_id' => $activo->id,
                'responsable_id' => $responsable->id,
                'email' => $responsable->email,
            ]);
            return $responsable;
        }

        // 2. Si no tiene email, buscar el responsable de la institución
        if ($activo->institucion_id) {
            $responsableInstitucion = Responsable::where('institucion_id', $activo->institucion_id)
                ->whereNull('departamento_id')
                ->where('activo', true)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->first();

            if ($responsableInstitucion) {
                Log::info('obtenerResponsableConEmail: usando responsable de la institución', [
                    'activo_id' => $activo->id,
                    'institucion_id' => $activo->institucion_id,
                    'responsable_id' => $responsableInstitucion->id,
                    'email' => $responsableInstitucion->email,
                ]);
                return $responsableInstitucion;
            }
        }

        // 3. Como último recurso, cualquier responsable activo de la institución (con departamento)
        if ($activo->institucion_id) {
            $responsableCualquiera = Responsable::where('institucion_id', $activo->institucion_id)
                ->where('activo', true)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->first();

            if ($responsableCualquiera) {
                Log::info('obtenerResponsableConEmail: usando responsable alternativo de la institución', [
                    'activo_id' => $activo->id,
                    'responsable_id' => $responsableCualquiera->id,
                    'email' => $responsableCualquiera->email,
                ]);
                return $responsableCualquiera;
            }
        }

        Log::warning('obtenerResponsableConEmail: NO se encontró responsable con email', [
            'activo_id' => $activo->id,
            'institucion_id' => $activo->institucion_id,
            'responsable_directo_id' => $activo->responsable_id,
            'responsable_directo_email' => $activo->responsable?->email,
        ]);

        return null;
    }

    // ============================================================
    // NOTIFICAR FICHA DE SOPORTE CREADA
    // ============================================================
    protected function notificarFichaSoporteCreada(FichaSoporte $ficha, ?CorreoRecibido $correo = null): void
    {
        $notificacionService = app(NotificacionService::class);
        $ficha->loadMissing(['activo.modelo.marca', 'activo.institucion', 'activo.responsable']);

        $activo = $ficha->activo;
        $serial = $activo?->serial ?? 'N/A';
        $modelo = $activo?->modelo?->nombre ?? 'N/A';
        $marca = $activo?->modelo?->marca?->nombre ?? 'N/A';

        Log::info('🔔 [notificarFichaSoporteCreada] Iniciando notificaciones', [
            'ficha_id' => $ficha->id,
            'activo_id' => $activo?->id,
            'tiene_tecnico' => !empty($ficha->tecnico_id),
            'tiene_activo' => (bool) $activo,
        ]);

        // 1. NOTIFICAR AL TÉCNICO ASIGNADO (si existe)
        if ($ficha->tecnico_id) {
            $tecnico = Usuario::with('trabajador')->find($ficha->tecnico_id);
            if ($tecnico) {
                $origen = $correo ? "Correo de {$correo->from_email}" : 'Ficha manual';
                $mensajeTecnico =
                    "Se te ha asignado una nueva ficha de soporte técnico #{$ficha->id}.\n\n" .
                    "📧 Origen: {$origen}\n" .
                    "🔧 Equipo: {$marca} {$modelo} (Serial: {$serial})\n" .
                    "📝 Diagnóstico: " . substr($ficha->diagnostico ?? 'Sin diagnóstico', 0, 200) . "\n" .
                    "📅 Fecha requerida de entrega: " . ($ficha->fecha_requerida_entrega ? $ficha->fecha_requerida_entrega->format('d/m/Y') : 'No especificada') . "\n\n" .
                    "Por favor, revisa la ficha y procede con la reparación.";

                try {
                    $notificacionService->enviarAUsuario(
                        $tecnico,
                        '🔧 Nueva Ficha de Soporte Técnico',
                        $mensajeTecnico,
                        'soporte',
                        route('admin.soporte.index')
                    );
                } catch (\Throwable $e) {
                    Log::error('Error al notificar al técnico: ' . $e->getMessage());
                }
            }
        }

        // 2. NOTIFICAR AL RESPONSABLE (con fallback a responsable de institución)
        $responsable = $this->obtenerResponsableConEmail($activo);
        $institucion = $activo?->institucion;

        if ($responsable && $responsable->email) {
            $destino = $institucion?->nombre ?? 'No especificada';
            $tieneTecnico = !empty($ficha->tecnico_id) && $ficha->tecnico_nombre !== 'No asignado';

            if ($tieneTecnico) {
                $estadoTecnico = "✅ TÉCNICO ASIGNADO\n" .
                    " • Nombre: {$ficha->tecnico_nombre}\n\n";
            } else {
                $estadoTecnico = "⚠️ NO HAY TÉCNICO ASIGNADO TODAVÍA\n" .
                    " Motivo: En este momento todos nuestros técnicos están ocupados con otras reparaciones.\n" .
                    " Acción: Su equipo será atendido en cuanto un técnico esté disponible. Por favor, lleve el equipo a la Gobernación igualmente para iniciar el proceso.\n\n";
            }

            $mensajeResponsable =
                "🔄 SOLICITUD DE REPARACIÓN EN PROCESO\n\n" .
                "Estimado/a {$responsable->nombre},\n\n" .
                "Le informamos que su solicitud de reparación ha sido recibida y está EN PROCESO de revisión.\n\n" .
                "📌 Datos de la ficha de soporte:\n" .
                " • Número de ficha: #{$ficha->id}\n" .
                " • Equipo: {$marca} {$modelo}\n" .
                " • Serial: {$serial}\n" .
                " • Institución: {$destino}\n" .
                " • Fecha de ingreso al sistema: " . ($ficha->fecha_ingreso ? $ficha->fecha_ingreso->format('d/m/Y H:i') : now()->format('d/m/Y H:i')) . "\n" .
                " • Fecha requerida de entrega: " . ($ficha->fecha_requerida_entrega ? $ficha->fecha_requerida_entrega->format('d/m/Y') : 'No especificada') . "\n\n" .
                "📝 Diagnóstico reportado:\n" .
                " " . ($ficha->diagnostico ?? 'Sin diagnóstico específico') . "\n\n" .
                $estadoTecnico .
                "📍 LUGAR DONDE SE REALIZARÁ LA REPARACIÓN:\n" .
                " Gobernación del Estado Yaracuy\n" .
                " Departamento de Informática\n" .
                " San Felipe, Edo. Yaracuy\n\n" .
                "📌 INSTRUCCIONES:\n" .
                " 1. Lleve el equipo (o los equipos) a la dirección indicada.\n" .
                " 2. Presente este correo o su cédula al momento de la entrega.\n" .
                " 3. Nuestro personal recibirá el equipo y le dará seguimiento a la reparación.\n\n" .
                "⏳ IMPORTANTE:\n" .
                " Una vez que el equipo sea recibido y evaluado, le notificaremos si la solicitud es ACEPTADA o RECHAZADA.\n" .
                " Si es aceptada, procederemos con la reparación.\n" .
                " Si es rechazada, le indicaremos el motivo y podrá retirar el equipo.\n\n" .
                "Le mantendremos informado por este mismo medio.\n\n" .
                "Gracias por confiar en nuestro servicio.";

            try {
                $notificacionService->enviarAResponsable(
                    $responsable->email,
                    $responsable->nombre,
                    '🔄 Solicitud de Reparación en Proceso - Llevar equipo a la Gobernación',
                    $mensajeResponsable,
                    'soporte'
                );
                Log::info("📧 Correo de EN PROCESO enviado al responsable", [
                    'ficha_id' => $ficha->id,
                    'responsable' => $responsable->nombre,
                    'email' => $responsable->email,
                    'tiene_tecnico' => $tieneTecnico,
                ]);
            } catch (\Throwable $e) {
                Log::error('Error al enviar correo al responsable: ' . $e->getMessage(), [
                    'ficha_id' => $ficha->id,
                    'email' => $responsable->email,
                ]);
            }
        } else {
            Log::warning("⚠️ No se pudo notificar al responsable: sin email disponible", [
                'ficha_id' => $ficha->id,
                'activo_id' => $activo?->id,
                'institucion_id' => $activo?->institucion_id,
            ]);
        }

        // 3. NOTIFICAR A LOS ADMINISTRADORES
        $admins = Usuario::whereHas('rol', function ($q) {
            $q->where('nombre', 'admin');
        })->where('status', 'activo')->with('trabajador')->get();

        $mensajeAdmin =
            "Se ha creado una nueva ficha de soporte técnico.\n\n" .
            "📌 Ficha #{$ficha->id}\n" .
            "🔧 Equipo: {$marca} {$modelo} (Serial: {$serial})\n" .
            "👤 Reportado por: {$ficha->usuario_reporta_nombre}\n" .
            "👨‍🔧 Técnico asignado: " . ($ficha->tecnico_nombre ?? 'No asignado') . "\n" .
            "📅 Fecha requerida de entrega: " . ($ficha->fecha_requerida_entrega ? $ficha->fecha_requerida_entrega->format('d/m/Y') : 'No especificada') . "\n\n" .
            "Revise la ficha en el módulo de Soporte Técnico.";

        foreach ($admins as $admin) {
            if ($admin->email) {
                try {
                    $notificacionService->enviarAUsuario(
                        $admin,
                        '🔧 Nueva Ficha de Soporte Técnico',
                        $mensajeAdmin,
                        'soporte',
                        route('admin.soporte.index')
                    );
                } catch (\Throwable $e) {
                    Log::error('Error al notificar admin: ' . $e->getMessage());
                }
            }
        }
    }

    // ============================================================
    // NOTIFICAR FICHA ACEPTADA
    // ============================================================
    protected function notificarFichaAceptada(FichaSoporte $ficha): void
    {
        $notificacionService = app(NotificacionService::class);
        $ficha->loadMissing([
            'activo.modelo.marca',
            'activo.institucion',
            'activo.responsable',
            'tecnico.trabajador'
        ]);

        $activo = $ficha->activo;
        $institucion = $activo?->institucion;
        $serial = $activo?->serial ?? 'N/A';
        $modelo = $activo?->modelo?->nombre ?? 'N/A';
        $marca = $activo?->modelo?->marca?->nombre ?? 'N/A';

        Log::info('🔔 [notificarFichaAceptada] Iniciando notificaciones', [
            'ficha_id' => $ficha->id,
            'activo_id' => $activo?->id,
        ]);

        // Notificar al responsable (con fallback)
        $responsable = $this->obtenerResponsableConEmail($activo);

        if ($responsable && $responsable->email) {
            $tieneTecnico = !empty($ficha->tecnico_id) && $ficha->tecnico_nombre !== 'No asignado';
            $infoTecnico = $tieneTecnico
                ? " • Técnico asignado: {$ficha->tecnico_nombre}\n"
                : " • Técnico asignado: Pendiente por asignar\n";

            $mensaje =
                "✅ SOLICITUD DE REPARACIÓN ACEPTADA\n\n" .
                "Estimado/a {$responsable->nombre},\n\n" .
                "Nos complace informarle que su solicitud de reparación ha sido ACEPTADA.\n\n" .
                "📌 Datos de la ficha:\n" .
                " • Número de ficha: #{$ficha->id}\n" .
                " • Equipo: {$marca} {$modelo} (Serial: {$serial})\n" .
                " • Institución: " . ($institucion?->nombre ?? 'No especificada') . "\n" .
                " • Fecha de aceptación: " . ($ficha->fecha_aceptacion ? $ficha->fecha_aceptacion->format('d/m/Y H:i') : now()->format('d/m/Y H:i')) . "\n" .
                " • Fecha requerida de entrega: " . ($ficha->fecha_requerida_entrega ? $ficha->fecha_requerida_entrega->format('d/m/Y') : 'No especificada') . "\n" .
                $infoTecnico . "\n" .
                "📝 Diagnóstico:\n" .
                " " . ($ficha->diagnostico ?? 'Sin diagnóstico específico') . "\n\n" .
                "🔧 PRÓXIMOS PASOS:\n" .
                " 1. El equipo será reparado por nuestro equipo técnico.\n" .
                " 2. Le notificaremos cuando la reparación haya finalizado.\n" .
                " 3. Una vez finalizada, podrá retirar el equipo en nuestras instalaciones.\n\n" .
                "📍 LUGAR DE REPARACIÓN:\n" .
                " Gobernación del Estado Yaracuy\n" .
                " Departamento de Informática\n" .
                " San Felipe, Edo. Yaracuy\n\n" .
                "Gracias por confiar en nuestro servicio.";

            try {
                $notificacionService->enviarAResponsable(
                    $responsable->email,
                    $responsable->nombre,
                    '✅ Solicitud de Reparación ACEPTADA',
                    $mensaje,
                    'soporte'
                );
                Log::info('📧 Correo de ACEPTACIÓN enviado al responsable', [
                    'ficha_id' => $ficha->id,
                    'responsable' => $responsable->nombre,
                    'email' => $responsable->email,
                ]);
            } catch (\Throwable $e) {
                Log::error('Error al notificar aceptación al responsable: ' . $e->getMessage(), [
                    'ficha_id' => $ficha->id,
                    'email' => $responsable->email,
                ]);
            }
        } else {
            Log::warning('⚠️ No se pudo notificar ACEPTACIÓN: sin responsable con email', [
                'ficha_id' => $ficha->id,
                'activo_id' => $activo?->id,
            ]);
        }

        // Notificar al técnico
        if ($ficha->tecnico_id) {
            $tecnico = Usuario::with('trabajador')->find($ficha->tecnico_id);
            if ($tecnico) {
                $mensajeTecnico =
                    "La ficha de soporte #{$ficha->id} ha sido ACEPTADA.\n\n" .
                    "🔧 Equipo: {$marca} {$modelo} (Serial: {$serial})\n" .
                    "🏢 Institución: " . ($institucion?->nombre ?? 'No especificada') . "\n" .
                    "📅 Fecha requerida de entrega: " . ($ficha->fecha_requerida_entrega ? $ficha->fecha_requerida_entrega->format('d/m/Y') : 'No especificada') . "\n\n" .
                    "Procede con la reparación del equipo.";

                try {
                    $notificacionService->enviarAUsuario(
                        $tecnico,
                        '✅ Ficha de Soporte ACEPTADA',
                        $mensajeTecnico,
                        'soporte',
                        route('admin.soporte.index')
                    );
                } catch (\Throwable $e) {
                    Log::error('Error al notificar aceptación al técnico: ' . $e->getMessage());
                }
            }
        }

        // Notificar a los administradores
        $admins = Usuario::whereHas('rol', function ($q) {
            $q->where('nombre', 'admin');
        })->where('status', 'activo')->with('trabajador')->get();

        $mensajeAdmin =
            "La ficha de soporte #{$ficha->id} ha sido ACEPTADA.\n\n" .
            "🔧 Equipo: {$marca} {$modelo} (Serial: {$serial})\n" .
            "👤 Reportado por: {$ficha->usuario_reporta_nombre}\n" .
            "👨‍🔧 Técnico: " . ($ficha->tecnico_nombre ?? 'No asignado') . "\n" .
            "📅 Fecha de aceptación: " . ($ficha->fecha_aceptacion ? $ficha->fecha_aceptacion->format('d/m/Y H:i') : now()->format('d/m/Y H:i'));

        foreach ($admins as $admin) {
            if ($admin->email) {
                try {
                    $notificacionService->enviarAUsuario(
                        $admin,
                        '✅ Ficha de Soporte ACEPTADA',
                        $mensajeAdmin,
                        'soporte',
                        route('admin.soporte.index')
                    );
                } catch (\Throwable $e) {
                    Log::error('Error al notificar aceptación a admin: ' . $e->getMessage());
                }
            }
        }
    }

    // ============================================================
    // NOTIFICAR FICHA RECHAZADA
    // ============================================================
    protected function notificarFichaRechazada(FichaSoporte $ficha): void
    {
        $notificacionService = app(NotificacionService::class);
        $ficha->loadMissing([
            'activo.modelo.marca',
            'activo.institucion',
            'activo.responsable'
        ]);

        $activo = $ficha->activo;
        $institucion = $activo?->institucion;
        $serial = $activo?->serial ?? 'N/A';
        $modelo = $activo?->modelo?->nombre ?? 'N/A';
        $marca = $activo?->modelo?->marca?->nombre ?? 'N/A';

        Log::info('🔔 [notificarFichaRechazada] Iniciando notificaciones', [
            'ficha_id' => $ficha->id,
            'activo_id' => $activo?->id,
        ]);

        // Notificar al responsable (con fallback)
        $responsable = $this->obtenerResponsableConEmail($activo);

        if ($responsable && $responsable->email) {
            $mensaje =
                "❌ SOLICITUD DE REPARACIÓN RECHAZADA\n\n" .
                "Estimado/a {$responsable->nombre},\n\n" .
                "Lamentamos informarle que su solicitud de reparación ha sido RECHAZADA.\n\n" .
                "📌 Datos de la ficha:\n" .
                " • Número de ficha: #{$ficha->id}\n" .
                " • Equipo: {$marca} {$modelo} (Serial: {$serial})\n" .
                " • Institución: " . ($institucion?->nombre ?? 'No especificada') . "\n" .
                " • Fecha de rechazo: " . ($ficha->fecha_rechazo ? $ficha->fecha_rechazo->format('d/m/Y H:i') : now()->format('d/m/Y H:i')) . "\n\n" .
                "📝 MOTIVO DEL RECHAZO:\n" .
                " " . ($ficha->motivo_rechazo ?? 'No especificado') . "\n\n" .
                "📍 INSTRUCCIONES:\n" .
                " 1. Puede pasar a retirar su equipo en:\n" .
                " Gobernación del Estado Yaracuy\n" .
                " Departamento de Informática\n" .
                " San Felipe, Edo. Yaracuy\n" .
                " 2. Presente este correo o su cédula al momento del retiro.\n\n" .
                "Si tiene alguna duda o desea más información, por favor comuníquese con el Departamento de Informática.\n\n" .
                "Gracias por su comprensión.";

            try {
                $notificacionService->enviarAResponsable(
                    $responsable->email,
                    $responsable->nombre,
                    '❌ Solicitud de Reparación RECHAZADA - Retirar Equipo',
                    $mensaje,
                    'soporte'
                );
                Log::info('📧 Correo de RECHAZO enviado al responsable', [
                    'ficha_id' => $ficha->id,
                    'responsable' => $responsable->nombre,
                    'email' => $responsable->email,
                ]);
            } catch (\Throwable $e) {
                Log::error('Error al notificar rechazo al responsable: ' . $e->getMessage(), [
                    'ficha_id' => $ficha->id,
                    'email' => $responsable->email,
                ]);
            }
        } else {
            Log::warning('⚠️ No se pudo notificar RECHAZO: sin responsable con email', [
                'ficha_id' => $ficha->id,
                'activo_id' => $activo?->id,
            ]);
        }

        // Notificar a los administradores
        $admins = Usuario::whereHas('rol', function ($q) {
            $q->where('nombre', 'admin');
        })->where('status', 'activo')->with('trabajador')->get();

        $mensajeAdmin =
            "La ficha de soporte #{$ficha->id} ha sido RECHAZADA.\n\n" .
            "🔧 Equipo: {$marca} {$modelo} (Serial: {$serial})\n" .
            "👤 Reportado por: {$ficha->usuario_reporta_nombre}\n" .
            "📝 Motivo: " . ($ficha->motivo_rechazo ?? 'No especificado') . "\n" .
            "📅 Fecha de rechazo: " . ($ficha->fecha_rechazo ? $ficha->fecha_rechazo->format('d/m/Y H:i') : now()->format('d/m/Y H:i'));

        foreach ($admins as $admin) {
            if ($admin->email) {
                try {
                    $notificacionService->enviarAUsuario(
                        $admin,
                        '❌ Ficha de Soporte RECHAZADA',
                        $mensajeAdmin,
                        'soporte',
                        route('admin.soporte.index')
                    );
                } catch (\Throwable $e) {
                    Log::error('Error al notificar rechazo a admin: ' . $e->getMessage());
                }
            }
        }
    }

    // ============================================================
    // NOTIFICAR CUANDO LA REPARACIÓN FINALIZA
    // ============================================================
    protected function notificarFichaFinalizada(FichaSoporte $ficha): void
    {
        $notificacionService = app(NotificacionService::class);
        $ficha->loadMissing([
            'activo.modelo.marca',
            'activo.institucion',
            'activo.responsable',
            'tecnico.trabajador'
        ]);

        $activo = $ficha->activo;
        $institucion = $activo?->institucion;
        $serial = $activo?->serial ?? 'N/A';
        $modelo = $activo?->modelo?->nombre ?? 'N/A';
        $marca = $activo?->modelo?->marca?->nombre ?? 'N/A';

        Log::info('🔔 [notificarFichaFinalizada] Iniciando notificaciones', [
            'ficha_id' => $ficha->id,
            'activo_id' => $activo?->id,
        ]);

        // Notificar al responsable de la institución (con fallback)
        $responsable = $this->obtenerResponsableConEmail($activo);

        if ($responsable && $responsable->email) {
            $mensajeResponsable =
                "✅ REPARACIÓN FINALIZADA\n\n" .
                "Estimado/a {$responsable->nombre},\n\n" .
                "Le informamos que la reparación de su equipo ha finalizado exitosamente.\n\n" .
                "📌 Datos:\n" .
                " • Ficha: #{$ficha->id}\n" .
                " • Equipo: {$marca} {$modelo} (Serial: {$serial})\n" .
                " • Institución: " . ($institucion?->nombre ?? 'No especificada') . "\n" .
                " • Fecha de finalización: " . ($ficha->fecha_salida ? $ficha->fecha_salida->format('d/m/Y H:i') : now()->format('d/m/Y H:i')) . "\n\n" .
                "📝 Trabajo realizado:\n" .
                " " . ($ficha->trabajo_realizado ?? 'No especificado') . "\n\n" .
                "📍 PUEDE RETIRAR EL EQUIPO EN:\n" .
                " Gobernación del Estado Yaracuy\n" .
                " Departamento de Informática\n" .
                " San Felipe, Edo. Yaracuy\n\n" .
                "📌 INSTRUCCIONES PARA EL RETIRO:\n" .
                " 1. Presente este correo o su cédula al momento del retiro.\n" .
                " 2. El horario de atención es de Lunes a Viernes de 8:00 AM a 4:00 PM.\n" .
                " 3. Si no puede retirarlo personalmente, puede enviar a un representante con una autorización escrita.\n\n" .
                "Gracias por confiar en nuestro servicio.";

            try {
                $notificacionService->enviarAResponsable(
                    $responsable->email,
                    $responsable->nombre,
                    '✅ Reparación Finalizada - RETIRAR EQUIPO',
                    $mensajeResponsable,
                    'soporte'
                );
                Log::info('📧 Correo de finalización enviado al responsable', [
                    'ficha_id' => $ficha->id,
                    'responsable' => $responsable->nombre,
                    'email' => $responsable->email,
                ]);
            } catch (\Throwable $e) {
                Log::error('Error al notificar finalización al responsable: ' . $e->getMessage(), [
                    'ficha_id' => $ficha->id,
                    'email' => $responsable->email,
                ]);
            }
        } else {
            Log::warning('⚠️ No se pudo notificar finalización al responsable (sin email)', [
                'ficha_id' => $ficha->id,
                'activo_id' => $activo?->id,
            ]);
        }

        // Notificar internamente al técnico y admins
        $mensajeInterno =
            "La ficha de soporte #{$ficha->id} ha sido finalizada.\n\n" .
            "🔧 Equipo: {$marca} {$modelo} (Serial: {$serial})\n" .
            "👨‍🔧 Técnico: " . ($ficha->tecnico_nombre ?? 'No asignado') . "\n" .
            "📝 Trabajo realizado: " . substr($ficha->trabajo_realizado ?? 'N/A', 0, 200) . "\n" .
            "📅 Fecha de finalización: " . ($ficha->fecha_salida ? $ficha->fecha_salida->format('d/m/Y H:i') : now()->format('d/m/Y H:i'));

        if ($ficha->tecnico_id) {
            $tecnico = Usuario::with('trabajador')->find($ficha->tecnico_id);
            if ($tecnico) {
                try {
                    $notificacionService->enviarAUsuario(
                        $tecnico,
                        '✅ Ficha de Soporte Finalizada',
                        $mensajeInterno,
                        'soporte',
                        route('admin.soporte.index')
                    );
                } catch (\Throwable $e) {
                    Log::error('Error al notificar finalización al técnico: ' . $e->getMessage());
                }
            }
        }

        $admins = Usuario::whereHas('rol', function ($q) {
            $q->where('nombre', 'admin');
        })->where('status', 'activo')->with('trabajador')->get();

        foreach ($admins as $admin) {
            if ($admin->email) {
                try {
                    $notificacionService->enviarAUsuario(
                        $admin,
                        '✅ Ficha de Soporte Finalizada',
                        $mensajeInterno,
                        'soporte',
                        route('admin.soporte.index')
                    );
                } catch (\Throwable $e) {
                    Log::error('Error al notificar finalización a admin: ' . $e->getMessage());
                }
            }
        }
    }

    // ============================================================
    // NOTIFICAR CUANDO LA FICHA ES ELIMINADA
    // ============================================================
    protected function notificarFichaEliminada(FichaSoporte $ficha): void
    {
        $notificacionService = app(NotificacionService::class);
        $ficha->loadMissing([
            'activo.modelo.marca',
            'activo.institucion',
            'activo.responsable'
        ]);

        $activo = $ficha->activo;
        $institucion = $activo?->institucion;
        $serial = $activo?->serial ?? 'N/A';
        $modelo = $activo?->modelo?->nombre ?? 'N/A';
        $marca = $activo?->modelo?->marca?->nombre ?? 'N/A';

        Log::info('🔔 [notificarFichaEliminada] Iniciando notificaciones', [
            'ficha_id' => $ficha->id,
            'activo_id' => $activo?->id,
        ]);

        // Notificar al responsable (con fallback)
        $responsable = $this->obtenerResponsableConEmail($activo);

        if ($responsable && $responsable->email) {
            $mensaje =
                "⚠️ SOLICITUD DE REPARACIÓN CANCELADA\n\n" .
                "Estimado/a {$responsable->nombre},\n\n" .
                "Le informamos que la solicitud de reparación para su equipo ha sido CANCELADA.\n\n" .
                "📌 Datos de la ficha:\n" .
                " • Número de ficha: #{$ficha->id}\n" .
                " • Equipo: {$marca} {$modelo} (Serial: {$serial})\n" .
                " • Institución: " . ($institucion?->nombre ?? 'No especificada') . "\n\n" .
                "📍 Si ya llevó el equipo a la Gobernación, puede pasar a retirarlo en:\n" .
                " Gobernación del Estado Yaracuy\n" .
                " Departamento de Informática\n" .
                " San Felipe, Edo. Yaracuy\n\n" .
                "Si tiene alguna duda, por favor comuníquese con el Departamento de Informática.\n\n" .
                "Gracias por su comprensión.";

            try {
                $notificacionService->enviarAResponsable(
                    $responsable->email,
                    $responsable->nombre,
                    '⚠️ Solicitud de Reparación CANCELADA',
                    $mensaje,
                    'soporte'
                );
                Log::info('📧 Correo de CANCELACIÓN enviado al responsable', [
                    'ficha_id' => $ficha->id,
                    'responsable' => $responsable->nombre,
                    'email' => $responsable->email,
                ]);
            } catch (\Throwable $e) {
                Log::error('Error al notificar cancelación al responsable: ' . $e->getMessage(), [
                    'ficha_id' => $ficha->id,
                    'email' => $responsable->email,
                ]);
            }
        } else {
            Log::warning('⚠️ No se pudo notificar CANCELACIÓN: sin responsable con email', [
                'ficha_id' => $ficha->id,
                'activo_id' => $activo?->id,
            ]);
        }
    }
}