<?php

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

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('activo', fn($q2) => $q2->where('serial', 'like', "%{$buscar}%"))
                  ->orWhere('tecnico_nombre', 'like', "%{$buscar}%")
                  ->orWhere('usuario_reporta_nombre', 'like', "%{$buscar}%")
                  ->orWhere('diagnostico', 'like', "%{$buscar}%");
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $fichas = $query->orderBy('created_at', 'desc')->paginate(15);

        // Respuesta AJAX para la tabla
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($fichas);
        }

        // Activos disponibles (NO en reparación y sin ficha activa)
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

        // Técnicos disponibles (usuarios con rol admin, ingeniero o tecnico)
        $tecnicos = Usuario::whereHas('rol', function ($q) {
            $q->whereIn('nombre', ['admin', 'ingeniero', 'tecnico']);
        })
            ->with('trabajador')
            ->orderBy('usuario')
            ->get();

        // Estadísticas para el dashboard
        $totalFichas = FichaSoporte::count();
        $enProceso = FichaSoporte::where('estado', 'en_proceso')->count();
        $finalizados = FichaSoporte::where('estado', 'finalizado')->count();
        $equiposReparacion = Activo::whereHas('estatus', function ($q) {
            $q->where('descripcion', 'En reparación');
        })->count();

        // Contadores de correos de soporte
        $correosNoLeidos = CorreoRecibido::soporte()->where('leido', false)->count();
        $correosNoProcesados = CorreoRecibido::soporte()->where('procesado', false)->count();

        return view('admin.soporte.index', compact(
            'fichas',
            'activosDisponibles',
            'tecnicos',
            'totalFichas',
            'enProceso',
            'finalizados',
            'equiposReparacion',
            'correosNoLeidos',
            'correosNoProcesados'
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

            // Cambiar estado del activo a "En reparación"
            $activo = Activo::find($validated['activo_id']);
            $estatusReparacion = Estatus::where('descripcion', 'En reparación')->first();

            if ($activo && $estatusReparacion) {
                $activo->update(['id_estatus' => $estatusReparacion->id]);
            }

            // Crear la ficha
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

            // Crear detalles con componentes del activo
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
        } catch (\Exception $e) {
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

            // 1. Crear la marca si no existe
            $marca = Marca::firstOrCreate(
                ['nombre' => $validated['marca']],
                ['activo' => true]
            );

            // 2. Crear el modelo si no existe
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

            // 3. Obtener estatus "En reparación"
            $estatusReparacion = Estatus::where('descripcion', 'En reparación')->first();

            if (!$estatusReparacion) {
                throw new \Exception('No se encontró el estatus "En reparación"');
            }

            // 4. Crear el activo en inventario con estado "En reparación"
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

            // 5. Crear la ficha de soporte vinculada al activo
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
        } catch (\Exception $e) {
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
                'tecnico.trabajador',
                'detalles.componente',
                'correo'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $ficha
            ]);
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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

            // Actualizar estado de componentes
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

            // Actualizar ficha
            $ficha->update([
                'trabajo_realizado' => $validated['trabajo_realizado'] ?? null,
                'observaciones' => $validated['observaciones_finales'] ?? $ficha->observaciones,
                'fecha_salida' => now(),
                'estado' => 'finalizado',
            ]);

            // Cambiar estado del activo a "Disponible"
            $activo = Activo::find($ficha->activo_id);
            if ($activo) {
                $estatusDisponible = Estatus::where('descripcion', 'Disponible')->first();
                if ($estatusDisponible) {
                    $activo->update(['id_estatus' => $estatusDisponible->id]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ficha finalizada exitosamente'
            ]);
        } catch (\Exception $e) {
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

            // Si la ficha estaba en proceso, restaurar el estado del activo
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // ============ CORREOS DE SOPORTE TÉCNICO ====================
    // ============================================================

    /**
     * Lista de correos tipo soporte
     */
    public function correosIndex(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-fichas-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $query = CorreoRecibido::soporte()
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
            'no_leidos' => CorreoRecibido::soporte()->where('leido', false)->count(),
            'no_procesados' => CorreoRecibido::soporte()->where('procesado', false)->count(),
        ]);
    }

    /**
     * Ver un correo de soporte
     */
    public function correoShow($id)
    {
        if (!auth()->user()->hasPermission('ver-fichas-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $correo = CorreoRecibido::soporte()
                ->with(['fichaSoporte', 'usuario'])
                ->findOrFail($id);

            if (!$correo->leido) {
                $correo->update(['leido' => true]);
            }

            return response()->json(['success' => true, 'data' => $correo]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Correo no encontrado'], 404);
        }
    }

    /**
     * Revisar correos manualmente
     */
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
        } catch (\Exception $e) {
            Log::error('Error al revisar correos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al revisar correos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Contador de correos no leídos/procesados
     */
    public function correosContador()
    {
        if (!auth()->user()->hasPermission('ver-fichas-soporte')) {
            return response()->json(['success' => false, 'no_leidos' => 0, 'no_procesados' => 0], 403);
        }

        return response()->json([
            'success' => true,
            'no_leidos' => CorreoRecibido::soporte()->where('leido', false)->count(),
            'no_procesados' => CorreoRecibido::soporte()->where('procesado', false)->count(),
        ]);
    }

    /**
     * CONVERTIR correo en Ficha de Soporte (Wizard)
     */
    public function correoConvertir(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('crear-ficha-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $correo = CorreoRecibido::soporte()->findOrFail($id);

            if ($correo->procesado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este correo ya fue convertido en ficha de soporte',
                ], 422);
            }

            $validated = $request->validate([
                'activo_id' => 'nullable|exists:activos,id',
                'nuevo_equipo' => 'nullable|array',
                'nuevo_equipo.serial' => 'required_with:nuevo_equipo|string|max:100|unique:activos,serial',
                'nuevo_equipo.marca' => 'required_with:nuevo_equipo|string|max:100',
                'nuevo_equipo.modelo_nombre' => 'required_with:nuevo_equipo|string|max:100',
                'nuevo_equipo.categoria_id' => 'required_with:nuevo_equipo|exists:categorias,id',
                'nuevo_equipo.institucion_id' => 'required_with:nuevo_equipo|exists:instituciones,id',
                'nuevo_equipo.responsable_id' => 'required_with:nuevo_equipo|exists:responsables,id',
                'tecnico_id' => 'nullable|exists:usuarios,id',
                'tecnico_nombre' => 'nullable|string|max:150',
                'usuario_reporta_nombre' => 'required|string|max:150',
                'diagnostico' => 'required|string|min:10',
                'observaciones' => 'nullable|string',
                'fecha_requerida_entrega' => 'required|date|after_or_equal:today',
            ]);

            DB::beginTransaction();

            $activoId = $validated['activo_id'] ?? null;

            // Si viene equipo nuevo, crearlo
            if (!empty($validated['nuevo_equipo'])) {
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
                // Cambiar estado del activo existente
                $activo = Activo::find($activoId);
                $estatusReparacion = Estatus::where('descripcion', 'En reparación')->first();
                if ($activo && $estatusReparacion) {
                    $activo->update(['id_estatus' => $estatusReparacion->id]);
                }
            }

            // Crear ficha de soporte
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

            // Actualizar correo
            $correo->update([
                'procesado' => true,
                'leido' => true,
                'ficha_soporte_id' => $ficha->id,
                'usuario_id' => auth()->id(),
            ]);

            DB::commit();

            // ========== NOTIFICACIONES ==========
            try {
                $this->notificarFichaSoporteCreada($ficha, $correo);
            } catch (\Exception $e) {
                Log::error('Error al notificar ficha de soporte: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Ficha de soporte creada exitosamente desde el correo',
                'ficha_id' => $ficha->id,
                'data' => $ficha->load(['activo', 'tecnico', 'correo']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al convertir correo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Notifica al técnico y al admin (2 notificaciones)
     */
    protected function notificarFichaSoporteCreada(FichaSoporte $ficha, CorreoRecibido $correo): void
    {
        $notificacionService = app(NotificacionService::class);

        // ========== 1. NOTIFICAR AL TÉCNICO ==========
        if ($ficha->tecnico_id) {
            $tecnico = Usuario::with('trabajador')->find($ficha->tecnico_id);

            if ($tecnico) {
                $mensaje = "Se te ha asignado una nueva ficha de soporte técnico #{$ficha->id}.\n\n" .
                    "📧 Origen: Correo de {$correo->from_email}\n" .
                    "🔧 Equipo: " . ($ficha->activo?->serial ?? 'N/A') . "\n" .
                    "📝 Diagnóstico: " . substr($ficha->diagnostico, 0, 200) . "\n" .
                    "📅 Fecha requerida de entrega: " . ($ficha->fecha_requerida_entrega ? $ficha->fecha_requerida_entrega->format('d/m/Y') : 'No especificada') . "\n\n" .
                    "Por favor, revisa la ficha y procede con la reparación.";

                $notificacionService->enviarAUsuario(
                    $tecnico,
                    '🔧 Nueva Ficha de Soporte Técnico',
                    $mensaje,
                    'soporte',
                    route('admin.soporte.index')
                );
            }
        }

        // ========== 2. NOTIFICAR A ADMINISTRADORES ==========
        $admins = Usuario::whereHas('rol', function ($q) {
            $q->where('nombre', 'admin');
        })->where('status', 'activo')->with('trabajador')->get();

        $mensajeAdmin = "Se ha creado una nueva ficha de soporte técnico desde un correo.\n\n" .
            "📌 Ficha #{$ficha->id}\n" .
            "📧 Correo origen: {$correo->from_email}\n" .
            "👤 Reportado por: {$ficha->usuario_reporta_nombre}\n" .
            "🔧 Equipo: " . ($ficha->activo?->serial ?? 'N/A') . "\n" .
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
}