<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\DetalleSolicitud;
use App\Models\Activo;
use App\Models\Periferico;
use App\Models\Institucion;
use App\Models\Departamento;
use App\Models\Responsable;
use App\Models\NotificacionSistema;
use App\Models\User;
use App\Events\SolicitudCreada;
use App\Events\SolicitudAprobada;
use App\Events\SolicitudRechazada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SolicitudController extends Controller
{
    public function index()
    {
        $solicitudes = Solicitud::with(['detalles.activo', 'detalles.periferico', 'institucion', 'departamento', 'solicitante', 'responsable'])
            ->where('id_solicitante', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $activos = Activo::all();
        $perifericos = Periferico::all();

        $instituciones = Institucion::where('activo', true)->orderBy('nombre')->get();
        $departamentos = Departamento::where('activo', true)->orderBy('nombre')->get();
        $responsables = Responsable::all();

        return view('solicitudes.index', compact('solicitudes', 'activos', 'perifericos', 'instituciones', 'departamentos', 'responsables'));
    }

    public function getItemsJson($id)
    {
        try {
            $solicitud = Solicitud::with(['detalles.activo', 'detalles.periferico'])->find($id);

            if (!$solicitud) {
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            $items = [];
            foreach ($solicitud->detalles as $detalle) {
                $descripcion = '';
                if ($detalle->tipo_item == 'activo' && $detalle->activo) {
                    $descripcion = $detalle->activo->serial . ' - ' . ($detalle->activo->marca_modelo ?? 'Activo');
                } elseif ($detalle->periferico) {
                    $descripcion = $detalle->periferico->nombre;
                } elseif ($detalle->observaciones) {
                    $descripcion = $detalle->observaciones;
                } else {
                    $descripcion = 'Item no disponible';
                }

                $items[] = [
                    'tipo_item' => $detalle->tipo_item,
                    'descripcion' => $descripcion,
                    'cantidad_solicitada' => $detalle->cantidad_solicitada
                ];
            }

            return response()->json($items);

        } catch (\Exception $e) {
            Log::error('Error en getItemsJson: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener detalles completos de una solicitud en formato JSON para el modal
     */
    public function getDetalles($id)
    {
        try {
            $solicitud = Solicitud::with([
                'detalles.activo',
                'detalles.periferico',
                'institucion',
                'departamento',
                'responsable',
                'solicitante'
            ])->find($id);

            if (!$solicitud) {
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            // Verificar permisos
            $user = auth()->user();
            if (!$user->isSuperAdmin() && !$user->hasRole('admin') && $user->id !== $solicitud->id_solicitante) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            // Procesar los detalles correctamente
            $detalles = [];
            foreach ($solicitud->detalles as $detalle) {
                $descripcion = '';

                if ($detalle->tipo_item === 'activo' && $detalle->activo) {
                    $descripcion = $detalle->activo->serial . ' - ' . ($detalle->activo->marca_modelo ?? 'Activo sin marca');
                } elseif ($detalle->tipo_item === 'periferico' && $detalle->periferico) {
                    $descripcion = $detalle->periferico->nombre;
                } elseif ($detalle->descripcion_personalizada) {
                    $descripcion = $detalle->descripcion_personalizada;
                } elseif ($detalle->observaciones) {
                    $descripcion = $detalle->observaciones;
                } else {
                    $descripcion = 'Item sin descripción específica';
                }

                $detalles[] = [
                    'tipo_item' => $detalle->tipo_item,
                    'item_descripcion' => $descripcion,
                    'cantidad_solicitada' => $detalle->cantidad_solicitada
                ];
            }

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
                'responsable' => $solicitud->responsable ? [
                    'id' => $solicitud->responsable->id,
                    'nombre' => $solicitud->responsable->nombre
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
            Log::info('Datos recibidos en store:', $request->all());

            // Validación base
            $rules = [
                'tipo_solicitante' => 'required|in:interno,externo',
                'fecha_requerida' => 'required|date|after_or_equal:today',
                'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
                'justificacion' => 'required|string|min:20|max:1000',
                'prioridad' => 'required|in:baja,normal,alta,urgente',
                'observaciones' => 'nullable|string|max:500',
                'items' => 'required|array|min:1',
                'items.*.tipo_item' => 'required|in:activo,periferico',
                'items.*.cantidad' => 'required|integer|min:1',
                'oficio_adjunto' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
            ];

            // Validación condicional: puede venir item_id (select) o item_descripcion (texto libre)
            $rules['items.*.item_id'] = 'nullable|integer';
            $rules['items.*.item_descripcion'] = 'nullable|string|max:255';

            $request->validate($rules);

            DB::beginTransaction();

            // Inicializar todas las variables necesarias
            $institucionId = null;
            $departamentoId = null;
            $responsableId = null;

            if ($request->tipo_solicitante === 'interno') {
                // Caso interno: departamento
                if ($request->filled('departamento_id') && $request->departamento_id !== 'otro') {
                    $departamentoId = $request->departamento_id;
                } elseif ($request->filled('nuevo_departamento')) {
                    $departamento = Departamento::create([
                        'nombre' => $request->nuevo_departamento,
                        'informacion' => $request->departamento_informacion ?? null,
                        'representante' => $request->departamento_representante ?? null,
                        'ubicacion' => $request->departamento_ubicacion ?? null,
                        'activo' => true
                    ]);
                    $departamentoId = $departamento->id;
                    $this->notificarNuevaEntidad('departamento', $departamento->nombre);
                }
            } else {
                // Caso externo: institución
                if ($request->filled('institucion_id') && $request->institucion_id !== 'otro') {
                    $institucionId = $request->institucion_id;
                } elseif ($request->filled('nueva_institucion')) {
                    $institucion = Institucion::create([
                        'nombre' => $request->nueva_institucion,
                        'informacion' => $request->informacion ?? null,
                        'representante' => $request->representante ?? null,
                        'ubicacion' => $request->ubicacion ?? null,
                        'activo' => true
                    ]);
                    $institucionId = $institucion->id;
                    $this->notificarNuevaEntidad('institución', $institucion->nombre);
                }
            }

            // Procesar responsable (común para ambos casos)
            if ($request->filled('responsable_id') && $request->responsable_id !== 'otro') {
                $responsableId = $request->responsable_id;
            } elseif ($request->filled('nuevo_responsable')) {
                $responsable = Responsable::create([
                    'nombre' => $request->nuevo_responsable,
                    'departamento' => $request->responsable_cargo ?? null,
                    'tipo' => $request->tipo_solicitante,
                    'telefono' => $request->responsable_telefono ?? null,
                    'email' => $request->responsable_email ?? null,
                    'documento' => $request->responsable_documento ?? null,
                    'institucion_id' => $institucionId,
                ]);
                $responsableId = $responsable->id;
            }

            // Subir archivo
            $oficioPath = null;
            if ($request->hasFile('oficio_adjunto')) {
                $oficioPath = $request->file('oficio_adjunto')->store('solicitudes/oficios', 'public');
            }

            // Crear solicitud
            $solicitud = Solicitud::create([
                'id_solicitante' => auth()->id(),
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

            Log::info('Solicitud creada con ID: ' . $solicitud->id);

            // Guardar items (soporta tanto item_id como item_descripcion)
            foreach ($request->items as $item) {
                $activoId = null;
                $perifericoId = null;
                $descripcionPersonalizada = null;

                // Caso 1: Viene item_id (select de inventario)
                if (isset($item['item_id']) && !empty($item['item_id'])) {
                    if ($item['tipo_item'] === 'activo') {
                        $activo = Activo::find($item['item_id']);
                        if (!$activo || $activo->cantidad < $item['cantidad']) {
                            throw new \Exception("No hay suficiente cantidad disponible del activo seleccionado");
                        }
                        $activoId = $item['item_id'];
                    } else {
                        $periferico = Periferico::find($item['item_id']);
                        if (!$periferico || $periferico->cantidad_disponible < $item['cantidad']) {
                            throw new \Exception("No hay suficiente cantidad disponible del periférico seleccionado");
                        }
                        $perifericoId = $item['item_id'];
                    }
                }
                // Caso 2: Viene item_descripcion (texto libre desde bandeja de correos)
                elseif (isset($item['item_descripcion']) && !empty($item['item_descripcion'])) {
                    $descripcionPersonalizada = $item['item_descripcion'];
                }
                // Caso 3: Si no viene ninguno, error
                else {
                    throw new \Exception("Debe especificar el item (seleccionando de inventario o escribiendo la descripción)");
                }

                DetalleSolicitud::create([
                    'id_solicitud' => $solicitud->id,
                    'tipo_item' => $item['tipo_item'],
                    'cantidad_solicitada' => $item['cantidad'],
                    'id_activo' => $activoId,
                    'periferico_id' => $perifericoId,
                    'descripcion_personalizada' => $descripcionPersonalizada,
                    'observaciones' => $item['observaciones'] ?? null
                ]);
            }

            DB::commit();

            try {
                event(new SolicitudCreada($solicitud));
            } catch (\Exception $e) {
                Log::warning('Error al disparar evento SolicitudCreada: ' . $e->getMessage());
            }

            // Respuesta JSON para peticiones AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud creada exitosamente',
                    'solicitud_id' => $solicitud->id,
                    'items_guardados' => count($request->items)
                ]);
            }

            return redirect()->route('solicitudes.index')
                ->with('success', 'Solicitud creada exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear solicitud: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    private function notificarNuevaEntidad($tipo, $nombre)
    {
        try {
            $admins = User::whereHas('rol', function($query) {
                $query->whereIn('nombre', ['super_admin', 'admin']);
            })->get();

            foreach ($admins as $admin) {
                NotificacionSistema::create([
                    'usuario_id' => $admin->id,
                    'tipo' => 'nueva_entidad',
                    'titulo' => "Nueva $tipo registrada",
                    'mensaje' => "Se ha registrado una nueva $tipo: '$nombre' durante una solicitud de préstamo.",
                    'datos_extra' => ['tipo' => $tipo, 'nombre' => $nombre],
                    'fecha_envio' => now(),
                    'leida' => false
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Error al notificar nueva entidad: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $activos = Activo::where('cantidad', '>', 0)->get();
        $perifericos = Periferico::where('cantidad_disponible', '>', 0)->get();
        $instituciones = Institucion::where('activo', true)->get();
        $departamentos = Departamento::where('activo', true)->get();
        $responsables = Responsable::all();

        return view('solicitudes.create', compact('activos', 'perifericos', 'instituciones', 'departamentos', 'responsables'));
    }

    public function show(Solicitud $solicitud)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->hasRole('admin') && $user->id !== $solicitud->id_solicitante) {
            abort(403);
        }

        $solicitud->load(['solicitante', 'aprobador', 'detalles.activo', 'detalles.periferico', 'institucion', 'departamento', 'responsable']);

        return view('solicitudes.show', compact('solicitud'));
    }

    public function approve(Solicitud $solicitud)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->hasRole('admin')) {
            abort(403);
        }

        if ($solicitud->estado_solicitud !== 'pendiente') {
            return back()->with('error', 'Solo se pueden aprobar solicitudes pendientes');
        }

        try {
            DB::beginTransaction();

            foreach ($solicitud->detalles as $detalle) {
                if ($detalle->tipo_item === 'activo' && $detalle->id_activo) {
                    $activo = Activo::find($detalle->id_activo);
                    if (!$activo || $activo->cantidad < $detalle->cantidad_solicitada) {
                        throw new \Exception("No hay suficiente cantidad del activo");
                    }
                } elseif ($detalle->tipo_item === 'periferico' && $detalle->periferico_id) {
                    $periferico = Periferico::find($detalle->periferico_id);
                    if (!$periferico || $periferico->cantidad_disponible < $detalle->cantidad_solicitada) {
                        throw new \Exception("No hay suficiente cantidad del periférico");
                    }
                }
                // Si el item es de texto libre (sin ID), no se puede verificar disponibilidad
            }

            $solicitud->update([
                'estado_solicitud' => 'aprobada',
                'aprobado_por' => $user->id,
                'fecha_aprobacion' => now()
            ]);

            DB::commit();

            try {
                event(new SolicitudAprobada($solicitud));
            } catch (\Exception $e) {
                Log::warning('Error al disparar evento SolicitudAprobada: ' . $e->getMessage());
            }

            NotificacionSistema::create([
                'usuario_id' => $solicitud->id_solicitante,
                'tipo' => 'solicitud_aprobada',
                'titulo' => 'Solicitud Aprobada',
                'mensaje' => "Tu solicitud #{$solicitud->id} ha sido aprobada",
                'datos_extra' => ['solicitud_id' => $solicitud->id],
                'fecha_envio' => now()
            ]);

            return redirect()->route('solicitudes.show', $solicitud)
                ->with('success', 'Solicitud aprobada');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al aprobar solicitud: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, Solicitud $solicitud)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->hasRole('admin')) {
            abort(403);
        }

        $request->validate(['motivo' => 'required|string|min:10']);

        $solicitud->update([
            'estado_solicitud' => 'rechazada',
            'aprobado_por' => $user->id,
            'fecha_aprobacion' => now(),
            'observaciones' => $request->motivo
        ]);

        try {
            event(new SolicitudRechazada($solicitud, $request->motivo));
        } catch (\Exception $e) {
            Log::warning('Error al disparar evento SolicitudRechazada: ' . $e->getMessage());
        }

        NotificacionSistema::create([
            'usuario_id' => $solicitud->id_solicitante,
            'tipo' => 'solicitud_rechazada',
            'titulo' => 'Solicitud Rechazada',
            'mensaje' => "Tu solicitud #{$solicitud->id} ha sido rechazada. Motivo: {$request->motivo}",
            'datos_extra' => ['solicitud_id' => $solicitud->id],
            'fecha_envio' => now()
        ]);

        return redirect()->route('solicitudes.show', $solicitud)
            ->with('success', 'Solicitud rechazada');
    }

    public function cancel(Solicitud $solicitud)
    {
        if (auth()->id() !== $solicitud->id_solicitante) {
            abort(403);
        }

        if (!in_array($solicitud->estado_solicitud, ['pendiente', 'aprobada'])) {
            return back()->with('error', 'No se puede cancelar esta solicitud');
        }

        $solicitud->update(['estado_solicitud' => 'cancelada']);

        return redirect()->route('solicitudes.index')
            ->with('success', 'Solicitud cancelada');
    }

    public function descargarOficio(Solicitud $solicitud)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->hasRole('admin') && $user->id !== $solicitud->id_solicitante) {
            abort(403);
        }

        if (!$solicitud->oficio_adjunto) {
            return back()->with('error', 'No hay archivo adjunto');
        }

        $path = storage_path("app/public/{$solicitud->oficio_adjunto}");

        if (!file_exists($path)) {
            return back()->with('error', 'El archivo no existe');
        }

        return response()->download($path);
    }

    public function update(Request $request, $id)
    {
        try {
            $solicitud = Solicitud::findOrFail($id);

            // Verificar que el usuario sea el propietario
            if ($solicitud->id_solicitante !== auth()->id()) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }

            // Verificar que la solicitud esté pendiente
            if ($solicitud->estado_solicitud !== 'pendiente') {
                return response()->json(['success' => false, 'message' => 'Solo se pueden editar solicitudes pendientes'], 422);
            }

            $request->validate([
                'tipo_solicitante' => 'required|in:interno,externo',
                'fecha_requerida' => 'required|date|after_or_equal:today',
                'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
                'justificacion' => 'required|string|min:20|max:1000',
                'prioridad' => 'required|in:baja,normal,alta,urgente',
                'observaciones' => 'nullable|string|max:500',
            ]);

            $solicitud->update([
                'tipo_solicitante' => $request->tipo_solicitante,
                'prioridad' => $request->prioridad,
                'fecha_requerida' => $request->fecha_requerida,
                'fecha_fin_estimada' => $request->fecha_fin_estimada,
                'justificacion' => $request->justificacion,
                'observaciones' => $request->observaciones,
                'departamento_id' => $request->departamento_id ?? null,
                'institucion_id' => $request->institucion_id ?? null,
                'responsable_id' => $request->responsable_id ?? null,
            ]);

            return response()->json(['success' => true, 'message' => 'Solicitud actualizada exitosamente']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
