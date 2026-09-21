<?php

// app/Http/Controllers/Admin/FichaSoporteController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\EnviarNotificacionFichaSoporte;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FichaSoporteController extends Controller
{
    // ============================================================
    // INDEX
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
            ->whereDoesntHave('fichasSoporte', fn($q) => $q->where('estado', 'en_proceso'))
            ->when($estatusReparacion, fn($q) => $q->where('id_estatus', '!=', $estatusReparacion->id))
            ->orderBy('serial')->get();

        $tecnicos = Usuario::whereHas('rol', fn($q) => $q->whereIn('nombre', ['admin', 'ingeniero', 'tecnico']))
            ->with('trabajador')->orderBy('usuario')->get();

        $totalFichas = FichaSoporte::count();
        $enProceso = FichaSoporte::where('estado', 'en_proceso')->count();
        $finalizados = FichaSoporte::where('estado', 'finalizado')->count();
        $equiposReparacion = Activo::whereHas('estatus', fn($q) => $q->where('descripcion', 'En reparación'))->count();

        $correosNoLeidos = CorreoRecibido::deTipoSoporte()->where('leido', false)->count();
        $correosNoProcesados = CorreoRecibido::deTipoSoporte()->where('procesado', false)->count();

        return view('admin.soporte.index', compact(
            'fichas', 'activosDisponibles', 'tecnicos', 'totalFichas',
            'enProceso', 'finalizados', 'equiposReparacion',
            'correosNoLeidos', 'correosNoProcesados'
        ));
    }

    // ============================================================
    // STORE - Crear ficha manual
    // ============================================================
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-ficha-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $validated = $request->validate([
                'activo_id' => 'required|exists:activos,id',
                'tecnico_id' => 'nullable|exists:usuarios,id',
                'tecnico_nombre' => 'nullable|string|max:150',
                'usuario_reporta_nombre' => 'required|string|max:150',
                'diagnostico' => 'nullable|string|min:10',
                'observaciones' => 'nullable|string',
                'fecha_requerida_entrega' => 'nullable|date|after_or_equal:today',
            ]);

            DB::beginTransaction();

            $ficha = FichaSoporte::create([
                'activo_id' => $validated['activo_id'],
                'tecnico_id' => $validated['tecnico_id'] ?? null,
                'tecnico_nombre' => $validated['tecnico_nombre'] ?? null,
                'usuario_reporta_id' => auth()->id(),
                'usuario_reporta_nombre' => $validated['usuario_reporta_nombre'],
                'fecha_ingreso' => now(),
                'fecha_requerida_entrega' => $validated['fecha_requerida_entrega'] ?? null,
                'diagnostico' => $validated['diagnostico'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'estado' => 'en_proceso',
                'origen' => 'manual',
            ]);

            // Cambiar estatus del activo
            $estatusReparacion = Estatus::where('descripcion', 'En reparación')->first();
            if ($estatusReparacion) {
                Activo::where('id', $validated['activo_id'])
                    ->update(['id_estatus' => $estatusReparacion->id]);
            }

            DB::commit();

            // ✅ Despachar Job
            EnviarNotificacionFichaSoporte::dispatch($ficha->id, 'creada')
                ->onQueue('notifications');

            Log::info('✅ Job EnviarNotificacionFichaSoporte despachado (creada)', [
                'ficha_id' => $ficha->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ficha de soporte creada exitosamente. Se enviarán las notificaciones.',
                'data' => $ficha->load(['activo.modelo.marca', 'tecnico.trabajador']),
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
            Log::error('Error al crear ficha: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // SHOW
    // ============================================================
    public function show($id)
    {
        if (!auth()->user()->hasPermission('ver-fichas-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $ficha = FichaSoporte::with([
                'activo.modelo.marca', 'activo.institucion', 'activo.responsable',
                'tecnico.trabajador', 'detalles.componente', 'correo'
            ])->findOrFail($id);

            return response()->json(['success' => true, 'data' => $ficha]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Ficha no encontrada'], 404);
        }
    }

    // ============================================================
    // UPDATE
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

            return response()->json(['success' => true, 'message' => 'Ficha actualizada correctamente']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // ACEPTAR
    // ✅ CORREGIDO: Despacha Job con evento 'aceptada' para notificar al responsable
    // ============================================================
    public function aceptar(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('editar-ficha-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $ficha = FichaSoporte::with([
                'activo.institucion', 'activo.responsable', 'activo.modelo.marca'
            ])->findOrFail($id);

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

            // ✅ Despachar Job con evento 'aceptada' para notificar al responsable
            EnviarNotificacionFichaSoporte::dispatch($ficha->id, 'aceptada')
                ->onQueue('notifications');

            Log::info('✅ Job EnviarNotificacionFichaSoporte despachado (aceptada)', [
                'ficha_id' => $ficha->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ficha aceptada exitosamente. Se enviarán las notificaciones.',
                'data' => $ficha
            ]);

        } catch (\Throwable $e) {
            Log::error('Error al aceptar ficha: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // RECHAZAR
    // ✅ CORREGIDO: Despacha Job con evento 'rechazada' para notificar al responsable
    // ============================================================
    public function rechazar(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('editar-ficha-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $ficha = FichaSoporte::with([
                'activo.institucion', 'activo.responsable', 'activo.modelo.marca'
            ])->findOrFail($id);

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

            // Liberar activo
            $activo = Activo::find($ficha->activo_id);
            if ($activo) {
                $estatusDisponible = Estatus::where('descripcion', 'Disponible')->first();
                if ($estatusDisponible) {
                    $activo->update(['id_estatus' => $estatusDisponible->id]);
                }
            }

            DB::commit();

            // ✅ Despachar Job con evento 'rechazada' para notificar al responsable
            EnviarNotificacionFichaSoporte::dispatch($ficha->id, 'rechazada')
                ->onQueue('notifications');

            Log::info('✅ Job EnviarNotificacionFichaSoporte despachado (rechazada)', [
                'ficha_id' => $ficha->id,
            ]);

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
    // ✅ CORREGIDO: Despacha Job con evento 'finalizada' para notificar al responsable
    // ============================================================
    public function close(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('cerrar-ficha-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $ficha = FichaSoporte::findOrFail($id);

            if ($ficha->estado === 'finalizado') {
                return response()->json(['success' => false, 'message' => 'La ficha ya está finalizada'], 422);
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

            // Liberar activo
            $activo = Activo::find($ficha->activo_id);
            if ($activo) {
                $estatusDisponible = Estatus::where('descripcion', 'Disponible')->first();
                if ($estatusDisponible) {
                    $activo->update(['id_estatus' => $estatusDisponible->id]);
                }
            }

            DB::commit();

            // ✅ Despachar Job con evento 'finalizada' para notificar al responsable
            EnviarNotificacionFichaSoporte::dispatch($ficha->id, 'finalizada')
                ->onQueue('notifications');

            Log::info('✅ Job EnviarNotificacionFichaSoporte despachado (finalizada)', [
                'ficha_id' => $ficha->id,
            ]);

            return response()->json(['success' => true, 'message' => 'Ficha finalizada exitosamente']);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // DESTROY
    // ✅ CORREGIDO: Despacha Job con evento 'eliminada' para notificar al responsable
    // ============================================================
    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('eliminar-ficha-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $ficha = FichaSoporte::findOrFail($id);
            $estabaEnProceso = in_array($ficha->estado, ['en_proceso', 'aceptada']);

            // ✅ Despachar Job ANTES de eliminar (para que pueda leer la ficha)
            if ($estabaEnProceso) {
                EnviarNotificacionFichaSoporte::dispatch($ficha->id, 'eliminada')
                    ->onQueue('notifications');

                Log::info('✅ Job EnviarNotificacionFichaSoporte despachado (eliminada)', [
                    'ficha_id' => $ficha->id,
                ]);
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

            return response()->json(['success' => true, 'message' => 'Ficha eliminada correctamente']);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // GET COMPONENTES DETALLE
    // ============================================================
    public function getComponentesDetalle($id)
    {
        try {
            $ficha = FichaSoporte::with('detalles.componente')->findOrFail($id);

            if ($ficha->detalles->isEmpty()) {
                // Crear detalles por defecto si no existen
                $componentes = Componente::where('activo_id', $ficha->activo_id)->get();

                if ($componentes->isNotEmpty()) {
                    foreach ($componentes as $comp) {
                        FichaSoporteDetalle::create([
                            'ficha_soporte_id' => $ficha->id,
                            'componente_id' => $comp->id,
                            'componente_nombre' => $comp->tipo . ' - ' . ($comp->marca ?? 'N/A'),
                            'estado_ingreso' => $comp->estado === 'instalado' ? 'funcionando' : 'dañado',
                        ]);
                    }
                }

                $ficha->load('detalles.componente');
            }

            return response()->json(['success' => true, 'data' => $ficha->detalles]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // CORREOS
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
            if ($request->filtro === 'no_leidos') $query->where('leido', false);
            elseif ($request->filtro === 'no_procesados') $query->where('procesado', false);
            elseif ($request->filtro === 'procesados') $query->where('procesado', true);
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
                ->with(['fichaSoporte', 'usuario'])->findOrFail($id);

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
    // CONVERTIR CORREO EN FICHA (Wizard)
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

            // ✅ Despachar Job con evento 'creada'
            EnviarNotificacionFichaSoporte::dispatch($ficha->id, 'creada', $correo->id)
                ->onQueue('notifications');

            Log::info('✅ Job EnviarNotificacionFichaSoporte despachado (creada desde correo)', [
                'ficha_id' => $ficha->id,
                'correo_id' => $correo->id,
            ]);

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
}