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
use Illuminate\Support\Facades\Mail;

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
                'tecnico_nombre' => 'required|string|max:150',
                'usuario_reporta_nombre' => 'required|string|max:150',
                'diagnostico' => 'nullable|string',
                'observaciones' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $activo = Activo::find($validated['activo_id']);
            $estatusReparacion = Estatus::where('descripcion', 'En reparación')->first();
            if ($activo && $estatusReparacion) {
                $activo->update(['id_estatus' => $estatusReparacion->id]);
            }

            $ficha = FichaSoporte::create([
                'activo_id' => $validated['activo_id'],
                'tecnico_id' => $validated['tecnico_id'] ?? null,
                'tecnico_nombre' => $validated['tecnico_nombre'],
                'usuario_reporta_id' => auth()->id(),
                'usuario_reporta_nombre' => $validated['usuario_reporta_nombre'],
                'fecha_ingreso' => now(),
                'diagnostico' => $validated['diagnostico'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'estado' => 'en_proceso',
                'origen' => 'manual',
            ]);

            $componentes = Componente::where('activo_id', $validated['activo_id'])->get();
            foreach ($componentes as $comp) {
                FichaSoporteDetalle::create([
                    'ficha_soporte_id' => $ficha->id,
                    'componente_id' => $comp->id,
                    'componente_nombre' => $comp->tipo . ' - ' . ($comp->marca ?? 'N/A'),
                    'estado_ingreso' => $comp->estado === 'instalado' ? 'funcionando' : 'dañado',
                    'observaciones' => null,
                ]);
            }

            DB::commit();

            try {
                $this->notificarFichaSoporteCreada($ficha, null);
            } catch (\Throwable $e) {
                Log::error('Error al notificar ficha manual: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Ficha de soporte creada exitosamente',
                'ficha_id' => $ficha->id,
                'data' => $ficha->load(['activo', 'detalles'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la ficha: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // STORE EQUIPO EXTERNO
    // ============================================================
    public function storeEquipoExterno(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-ficha-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $validated = $request->validate([
                'serial' => 'required|string|max:100|unique:activos,serial',
                'modelo_nombre' => 'required|string|max:100',
                'marca' => 'required|string|max:100',
                'categoria_id' => 'required|exists:categorias,id',
                'institucion_id' => 'required|exists:instituciones,id',
                'responsable_id' => 'required|exists:responsables,id',
                'ubicacion' => 'nullable|string|max:100',
                'fecha_adquisicion' => 'nullable|date',
                'observaciones' => 'nullable|string',
                'tecnico_id' => 'nullable|exists:usuarios,id',
                'tecnico_nombre' => 'nullable|string|max:150',
                'usuario_reporta_nombre' => 'required|string|max:150',
                'diagnostico' => 'nullable|string',
                'observaciones_ficha' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $marca = Marca::firstOrCreate(
                ['nombre' => $validated['marca']],
                ['activo' => true]
            );

            $modelo = Modelo::firstOrCreate(
                [
                    'marca_id' => $marca->id,
                    'nombre' => $validated['modelo_nombre']
                ],
                [
                    'categoria_id' => $validated['categoria_id'],
                    'activo' => true
                ]
            );

            $estatusReparacion = Estatus::where('descripcion', 'En reparación')->first();
            if (!$estatusReparacion) {
                throw new \Exception('No se encontró el estatus "En reparación"');
            }

            $activo = Activo::create([
                'serial' => $validated['serial'],
                'modelo_id' => $modelo->id,
                'id_estatus' => $estatusReparacion->id,
                'institucion_id' => $validated['institucion_id'],
                'responsable_id' => $validated['responsable_id'],
                'ubicacion' => $validated['ubicacion'] ?? 'Taller de reparación',
                'fecha_adquisicion' => $validated['fecha_adquisicion'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            $tecnicoNombre = $validated['tecnico_nombre'] ?? null;
            if ($validated['tecnico_id']) {
                $tecnico = Usuario::with('trabajador')->find($validated['tecnico_id']);
                if ($tecnico && $tecnico->trabajador) {
                    $tecnicoNombre = $tecnico->trabajador->nombre . ' ' . $tecnico->trabajador->apellido;
                }
            }

            $ficha = FichaSoporte::create([
                'activo_id' => $activo->id,
                'tecnico_id' => $validated['tecnico_id'] ?? null,
                'tecnico_nombre' => $tecnicoNombre ?? 'No asignado',
                'usuario_reporta_id' => auth()->id(),
                'usuario_reporta_nombre' => $validated['usuario_reporta_nombre'],
                'fecha_ingreso' => now(),
                'diagnostico' => $validated['diagnostico'] ?? null,
                'observaciones' => $validated['observaciones_ficha'] ?? null,
                'estado' => 'en_proceso',
                'origen' => 'manual',
            ]);

            DB::commit();

            try {
                $this->notificarFichaSoporteCreada($ficha, null);
            } catch (\Throwable $e) {
                Log::error('Error al notificar ficha externa: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Equipo externo registrado en inventario y ficha de soporte creada exitosamente',
                'data' => [
                    'activo' => $activo,
                    'ficha' => $ficha
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar: ' . $e->getMessage()
            ], 500);
        }
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

            if ($ficha->estado === 'en_proceso') {
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
    // CONVERTIR correo en Ficha de Soporte (Wizard) - MEJORADO
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

            // ✅ CORRECCIÓN: fecha normalizada y tolerante con la zona horaria
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
                // ===== EQUIPO NUEVO: crear activo =====
                $nuevo = $validated['nuevo_equipo'];

                $marca = Marca::firstOrCreate(
                    ['nombre' => $nuevo['marca']],
                    ['activo' => true]
                );

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
                // ===== EQUIPO EXISTENTE =====
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

            // Notificar al técnico, admin Y RESPONSABLE INSTITUCIONAL
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
    // ✅ NOTIFICAR FICHA DE SOPORTE CREADA
    // ============================================================
    protected function notificarFichaSoporteCreada(FichaSoporte $ficha, ?CorreoRecibido $correo = null): void
    {
        $notificacionService = app(NotificacionService::class);

        $ficha->loadMissing(['activo.modelo.marca', 'activo.institucion', 'activo.responsable']);

        $activo = $ficha->activo;
        $serial = $activo?->serial ?? 'N/A';
        $modelo = $activo?->modelo?->nombre ?? 'N/A';
        $marca = $activo?->modelo?->marca?->nombre ?? 'N/A';

        // ============================================================
        // 1. NOTIFICAR AL TÉCNICO ASIGNADO (si existe)
        // ============================================================
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

                $notificacionService->enviarAUsuario(
                    $tecnico,
                    '🔧 Nueva Ficha de Soporte Técnico',
                    $mensajeTecnico,
                    'soporte',
                    route('admin.soporte.index')
                );
            }
        }

        // ============================================================
        // 2. NOTIFICAR AL RESPONSABLE DE LA INSTITUCIÓN
        // ============================================================
        $responsable = $activo?->responsable;
        $institucion = $activo?->institucion;

        if ($responsable && $responsable->email) {
            $destino = $institucion?->nombre ?? 'No especificada';

            // ✅ CORREGIDO: Determinar si hay o no técnico asignado
            $tieneTecnico = !empty($ficha->tecnico_id) && $ficha->tecnico_nombre !== 'No asignado';

            if ($tieneTecnico) {
                $estadoTecnico =
                    "✅ TÉCNICO ASIGNADO\n" .
                    " • Nombre: {$ficha->tecnico_nombre}\n\n";
            } else {
                $estadoTecnico =
                    "⚠️ NO HAY TÉCNICO ASIGNADO TODAVÍA\n" .
                    " Motivo: En este momento todos nuestros técnicos están ocupados con otras reparaciones.\n" .
                    " Acción: Su equipo será atendido en cuanto un técnico esté disponible. Por favor, lleve el equipo a la Gobernación igualmente para iniciar el proceso.\n\n";
            }

            $mensajeResponsable =
                "✅ SOLICITUD DE REPARACIÓN ACEPTADA\n\n" .
                "Estimado/a {$responsable->nombre},\n\n" .
                "Le informamos que su solicitud de reparación ha sido ACEPTADA y registrada en nuestro sistema.\n\n" .
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
                "Le notificaremos por este mismo medio cuando la reparación haya finalizado.\n\n" .
                "Gracias por confiar en nuestro servicio.";

            try {
                $notificacionService->enviarAResponsable(
                    $responsable->email,
                    $responsable->nombre,
                    '✅ Solicitud de Reparación Aceptada - Llevar equipo a la Gobernación',
                    $mensajeResponsable,
                    'soporte'
                );

                Log::info("📧 Correo enviado al responsable institucional", [
                    'ficha_id' => $ficha->id,
                    'responsable' => $responsable->nombre,
                    'email' => $responsable->email,
                    'tiene_tecnico'=> $tieneTecnico,
                ]);
            } catch (\Throwable $e) {
                Log::error('Error al enviar correo al responsable institucional: ' . $e->getMessage());
            }
        } else {
            Log::warning("⚠️ No se pudo notificar al responsable institucional", [
                'ficha_id' => $ficha->id,
                'activo_id' => $activo?->id,
                'tiene_responsable' => (bool) $responsable,
                'tiene_email' => (bool) ($responsable?->email),
            ]);
        }

        // ============================================================
        // 3. NOTIFICAR A LOS ADMINISTRADORES
        // ============================================================
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
                $notificacionService->enviarAUsuario(
                    $admin,
                    '🔧 Nueva Ficha de Soporte Técnico',
                    $mensajeAdmin,
                    'soporte',
                    route('admin.soporte.index')
                );
            }
        }
    }

    // ============================================================
    // ✅ NOTIFICAR CUANDO LA REPARACIÓN FINALIZA
    // ============================================================
    protected function notificarFichaFinalizada(FichaSoporte $ficha): void
    {
        $notificacionService = app(NotificacionService::class);

        $ficha->loadMissing(['activo.modelo.marca', 'activo.institucion', 'activo.responsable', 'tecnico.trabajador']);

        $activo = $ficha->activo;
        $responsable = $activo?->responsable;
        $institucion = $activo?->institucion;
        $serial = $activo?->serial ?? 'N/A';
        $modelo = $activo?->modelo?->nombre ?? 'N/A';
        $marca = $activo?->modelo?->marca?->nombre ?? 'N/A';

        // --- Notificar al responsable de la institución ---
        if ($responsable && $responsable->email) {
            $mensajeResponsable =
                "✅ REPARACIÓN FINALIZADA\n\n" .
                "Estimado/a {$responsable->nombre},\n\n" .
                "Le informamos que la reparación de su equipo ha finalizado.\n\n" .
                "📌 Datos:\n" .
                " • Ficha: #{$ficha->id}\n" .
                " • Equipo: {$marca} {$modelo} (Serial: {$serial})\n" .
                " • Institución: " . ($institucion?->nombre ?? 'No especificada') . "\n" .
                " • Fecha de finalización: " . ($ficha->fecha_salida ? $ficha->fecha_salida->format('d/m/Y H:i') : now()->format('d/m/Y H:i')) . "\n\n" .
                "📝 Trabajo realizado:\n" .
                " " . ($ficha->trabajo_realizado ?? 'No especificado') . "\n\n" .
                "📍 Puede retirar el equipo en:\n" .
                " Gobernación del Estado Yaracuy\n" .
                " Departamento de Informática\n" .
                " San Felipe, Edo. Yaracuy\n\n" .
                "Presente este correo o su cédula al momento del retiro.\n\n" .
                "Gracias por confiar en nuestro servicio.";

            try {
                $notificacionService->enviarAResponsable(
                    $responsable->email,
                    $responsable->nombre,
                    '✅ Reparación Finalizada - Retirar Equipo',
                    $mensajeResponsable,
                    'soporte'
                );
            } catch (\Throwable $e) {
                Log::error('Error al notificar finalización al responsable: ' . $e->getMessage());
            }
        }

        // --- Notificar internamente al técnico y admins ---
        $mensajeInterno =
            "La ficha de soporte #{$ficha->id} ha sido finalizada.\n\n" .
            "🔧 Equipo: {$marca} {$modelo} (Serial: {$serial})\n" .
            "👨‍🔧 Técnico: " . ($ficha->tecnico_nombre ?? 'No asignado') . "\n" .
            "📝 Trabajo realizado: " . substr($ficha->trabajo_realizado ?? 'N/A', 0, 200);

        if ($ficha->tecnico_id) {
            $tecnico = Usuario::with('trabajador')->find($ficha->tecnico_id);
            if ($tecnico) {
                $notificacionService->enviarAUsuario(
                    $tecnico,
                    '✅ Ficha de Soporte Finalizada',
                    $mensajeInterno,
                    'soporte',
                    route('admin.soporte.index')
                );
            }
        }

        $admins = Usuario::whereHas('rol', function ($q) {
            $q->where('nombre', 'admin');
        })->where('status', 'activo')->with('trabajador')->get();

        foreach ($admins as $admin) {
            if ($admin->email) {
                $notificacionService->enviarAUsuario(
                    $admin,
                    '✅ Ficha de Soporte Finalizada',
                    $mensajeInterno,
                    'soporte',
                    route('admin.soporte.index')
                );
            }
        }
    }
}