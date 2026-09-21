<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Prestamo;
use App\Models\DetallePrestamo;
use App\Models\DetalleSolicitud;
use App\Models\Activo;
use App\Models\Periferico;
use App\Models\NotificacionSistema;
use App\Models\ExtensionPrestamo;
use App\Models\Devolucion;
use App\Models\DetalleDevolucion;
use App\Mail\ActaRetiroMail;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class AprobacionController extends Controller
{
    protected NotificacionService $notificacionService;

    public function __construct(NotificacionService $notificacionService)
    {
        $this->notificacionService = $notificacionService;
    }

    /**
     * Mostrar panel de aprobaciones
     */
    public function index()
    {
        // Solicitudes pendientes de aprobación
        $solicitudesPendientes = Solicitud::with(['solicitante', 'detalles.activo', 'detalles.periferico', 'institucion'])
            ->where('estado_solicitud', 'pendiente')
            ->orderByRaw("CASE prioridad 
                WHEN 'urgente' THEN 1 
                WHEN 'alta' THEN 2 
                WHEN 'normal' THEN 3 
                WHEN 'baja' THEN 4 
                ELSE 5 END")
            ->orderBy('created_at', 'asc')
            ->get();

        // Solicitudes aprobadas (sin préstamo asociado aún)
        $solicitudesAprobadas = Solicitud::with(['solicitante', 'detalles.activo', 'detalles.periferico'])
            ->where('estado_solicitud', 'aprobada')
            ->whereDoesntHave('prestamo')
            ->orderBy('fecha_aprobacion', 'desc')
            ->get();

        // Préstamos activos
        $prestamosActivos = Prestamo::with(['solicitud.solicitante', 'detalles.activo', 'detalles.periferico', 'tecnico', 'responsable'])
            ->where('estado_prestamo', 'activo')
            ->orderBy('fecha_retorno_estimada', 'asc')
            ->get();

        // Historial de préstamos completados
        $historial = Prestamo::with(['solicitud.solicitante', 'detalles'])
            ->where('estado_prestamo', 'completado')
            ->orderBy('fecha_retorno_real', 'desc')
            ->paginate(20);

        // Contadores para el dashboard
        $totalPendientes = $solicitudesPendientes->count();
        $totalActivos = $prestamosActivos->count();
        $prestamosVencidos = $prestamosActivos->filter(function($prestamo) {
            return $prestamo->fecha_retorno_estimada && 
                   $prestamo->fecha_retorno_estimada < now()->toDateString();
        })->count();

        return view('solicitudes.aprobaciones', compact(
            'solicitudesPendientes',
            'solicitudesAprobadas',
            'prestamosActivos',
            'historial',
            'totalPendientes',
            'totalActivos',
            'prestamosVencidos'
        ));
    }

    /**
     * Aprobar una solicitud
     * ✅ MODIFICADO: Ahora genera y envía el Acta de Retiro en PDF
     */
    public function approve(Request $request, Solicitud $solicitud)
    {
        $request->validate([
            'items_asignados' => 'required|array',
            'items_asignados.*.detalle_id' => 'required|integer',
            'items_asignados.*.cantidad_asignada' => 'required|integer|min:0',
            'observaciones' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Verificar que no exceda lo solicitado
            foreach ($request->items_asignados as $item) {
                $detalle = DetalleSolicitud::find($item['detalle_id']);
                if ($detalle && $item['cantidad_asignada'] > $detalle->cantidad_solicitada) {
                    throw new \Exception("No se puede asignar más de lo solicitado para un item");
                }
            }

            // Actualizar la solicitud
            $solicitud->update([
                'estado_solicitud' => 'aprobada',
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => now(),
                'observaciones_aprobacion' => $request->observaciones
            ]);

            // Guardar la asignación en sesión
            session()->put("asignacion_{$solicitud->id}", $request->items_asignados);

            DB::commit();

            // Crear notificación en el sistema para el solicitante
            NotificacionSistema::create([
                'usuario_id' => $solicitud->id_solicitante,
                'tipo' => 'solicitud_aprobada',
                'titulo' => '✅ Solicitud Aprobada',
                'mensaje' => "Tu solicitud #{$solicitud->id} ha sido aprobada.",
                'datos_extra' => ['solicitud_id' => $solicitud->id],
                'fecha_envio' => now(),
                'leida' => false
            ]);

            // ✅ NUEVO: Generación y envío del Acta de Retiro en PDF
            $this->generarYEnviarActaRetiro($solicitud, $request->observaciones);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud aprobada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al aprobar solicitud: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NUEVO: Genera el PDF del Acta de Retiro y lo envía por correo
     */
    private function generarYEnviarActaRetiro(Solicitud $solicitud, ?string $observaciones = null): void
    {
        try {
            // Refrescar la solicitud con las relaciones necesarias
            $solicitud->load([
                'responsable',
                'departamento',
                'institucion',
                'usuario.trabajador',
                'detalles.activo.modelo.marca',
                'detalles.activo.modelo.categoria',
                'detalles.componente',
            ]);

            $responsable = $solicitud->responsable;

            if (!$responsable) {
                Log::warning("No se pudo enviar el acta de retiro para la solicitud #{$solicitud->id}: La solicitud no tiene un responsable asignado.");
                return;
            }

            if (!$responsable->email) {
                Log::warning("No se pudo enviar el acta de retiro para la solicitud #{$solicitud->id}: El responsable '{$responsable->nombre}' no tiene un email registrado.");
                return;
            }

            // ============ OBTENER EQUIPO PRINCIPAL ============
            $detallePrincipal = $solicitud->detalles->first(fn ($d) => $d->activo !== null);
            $activo = $detallePrincipal ? $detallePrincipal->activo : null;

            // ============ AGRUPAR ACCESORIOS (componentes) ============
            $accesoriosAgrupados = [];
            foreach ($solicitud->detalles as $d) {
                if ($d->componente) {
                    $comp = $d->componente;
                    $clave = trim(($comp->tipo ?? '') . ' ' . ($comp->marca ?? ''));
                    if (!isset($accesoriosAgrupados[$clave])) {
                        $accesoriosAgrupados[$clave] = 0;
                    }
                    $accesoriosAgrupados[$clave] += $d->cantidad_solicitada ?? 1;
                }
            }

            $textoAccesorios = 'Sin accesorios adicionales';
            if (count($accesoriosAgrupados) > 0) {
                $lista = [];
                foreach ($accesoriosAgrupados as $nombre => $cantidad) {
                    $lista[] = $cantidad > 1 ? "{$nombre} ({$cantidad})" : $nombre;
                }
                if (count($lista) === 1) {
                    $textoAccesorios = 'Con su respectivo ' . $lista[0];
                } elseif (count($lista) === 2) {
                    $textoAccesorios = 'Con su respectivo ' . implode(' y ', $lista);
                } else {
                    $ultimo = array_pop($lista);
                    $textoAccesorios = 'Con su respectivo ' . implode(', ', $lista) . ' y ' . $ultimo;
                }
            }

// ============ DATOS PARA LA VISTA DEL PDF ============
            $data = [
    'codigo'                  => $solicitud->codigo ?? ('SOL-' . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT)),
    'numero_acta'             => 'ACTA-RETIRO-' . date('Ym') . '-' . str_pad($solicitud->id, 4, '0', STR_PAD_LEFT),
    'fecha'                   => now()->format('d/m/Y'),
    'institucion'             => $solicitud->nombre_entidad ?? 'No especificada',
    'responsable_nombre'      => $responsable->nombre ?? 'No especificado',
    'responsable_cargo'       => $responsable->cargo ?? 'Responsable',
    'responsable_documento'   => $responsable->documento ?? 'N/A',
    'encargado_nombre'        => 'Director de Informática',       
    'encargado_cargo'         => 'Director de Informática',      
    'equipo_nombre'           => $activo ? trim(($activo->modelo->marca->nombre ?? '') . ' ' . ($activo->modelo->nombre ?? '')) : 'Equipo no especificado',
    'serial'                  => $activo->serial ?? 'N/A',
    'marca'                   => $activo->modelo->marca->nombre ?? 'N/A',
    'modelo'                  => $activo->modelo->nombre ?? 'N/A',
    'accesorios'              => $textoAccesorios,
    'fecha_solicitud'         => $solicitud->fecha_solicitud ? $solicitud->fecha_solicitud->format('d/m/Y') : now()->format('d/m/Y'),
    'observaciones'           => $observaciones ?? $solicitud->observaciones ?? 'Sin observaciones adicionales.',
    'items'                   => $solicitud->detalles->map(function ($detalle) {
        return [
            'tipo_item'   => $detalle->tipo_item,
            'descripcion' => $detalle->descripcion_item ?? 'Item',
            'cantidad'    => $detalle->cantidad_solicitada ?? 1,
        ];
    })->toArray(),
];

           // ============ GENERAR PDF ============
            $pdf = Pdf::loadView('emails.acta-retiro', ['data' => $data]);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'Times New Roman',
            ]);


            // ============ ENVIAR CORREO CON PDF ADJUNTO ============
            Mail::to($responsable->email)
                ->send(new ActaRetiroMail(
                    $solicitud,
                    $pdf,
                    'Adjunto encontrará el Acta de Retiro correspondiente a su solicitud aprobada. Por favor imprímala, fírmela y preséntese en el Departamento de Informática para retirar los equipos.'
                ));

            Log::info("✅ Acta de Retiro enviada", [
                'solicitud_id' => $solicitud->id,
                'responsable' => $responsable->nombre,
                'email' => $responsable->email,
            ]);

        } catch (\Throwable $e) {
            Log::error('❌ Error al generar/enviar Acta de Retiro: ' . $e->getMessage(), [
                'solicitud_id' => $solicitud->id,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Rechazar una solicitud
     */
    public function reject(Request $request, Solicitud $solicitud)
    {
        $request->validate([
            'motivo' => 'required|string|min:10|max:500'
        ]);

        try {
            $solicitud->update([
                'estado_solicitud' => 'rechazada',
                'observaciones_rechazo' => $request->motivo,
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => now()
            ]);

            NotificacionSistema::create([
                'usuario_id' => $solicitud->id_solicitante,
                'tipo' => 'solicitud_rechazada',
                'titulo' => '❌ Solicitud Rechazada',
                'mensaje' => "Tu solicitud #{$solicitud->id} ha sido rechazada. Motivo: {$request->motivo}",
                'datos_extra' => ['solicitud_id' => $solicitud->id, 'motivo' => $request->motivo],
                'fecha_envio' => now(),
                'leida' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud rechazada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Poner solicitud en espera
     */
    public function pending(Request $request, Solicitud $solicitud)
    {
        $request->validate([
            'motivo' => 'nullable|string|max:500'
        ]);

        try {
            $solicitud->update([
                'estado_solicitud' => 'en_espera',
                'observaciones_espera' => $request->motivo,
                'fecha_espera' => now()
            ]);

            NotificacionSistema::create([
                'usuario_id' => $solicitud->id_solicitante,
                'tipo' => 'solicitud_en_espera',
                'titulo' => '⏳ Solicitud en Espera',
                'mensaje' => "Tu solicitud #{$solicitud->id} ha sido puesta en espera.",
                'datos_extra' => ['solicitud_id' => $solicitud->id],
                'fecha_envio' => now(),
                'leida' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud puesta en espera'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Realizar préstamo desde solicitud aprobada
     */
    public function realizarPrestamo(Request $request, Solicitud $solicitud)
    {
        $request->validate([
            'id_tecnico' => 'required|exists:usuario,id',
            'id_responsable' => 'required|exists:responsable,id',
            'fecha_retorno_estimada' => 'required|date|after:today',
            'observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Obtener la asignación guardada
            $asignacion = session()->get("asignacion_{$solicitud->id}");

            if (!$asignacion) {
                throw new \Exception("No hay asignación de equipos para esta solicitud");
            }

            // Crear el préstamo
            $prestamo = Prestamo::create([
                'id_solicitud' => $solicitud->id,
                'id_tecnico' => $request->id_tecnico,
                'id_responsable' => $request->id_responsable,
                'fecha_prestamo' => now()->toDateString(),
                'hora_prestamo' => now()->toTimeString(),
                'fecha_retorno_estimada' => $request->fecha_retorno_estimada,
                'tipo_prestamo' => $solicitud->tipo_solicitante,
                'estado_prestamo' => 'activo',
                'observaciones' => $request->observaciones,
                'aprobado_por' => auth()->id()
            ]);

            // Crear detalles del préstamo
            foreach ($asignacion as $item) {
                $detalleSolicitud = DetalleSolicitud::find($item['detalle_id']);

                if ($detalleSolicitud && $item['cantidad_asignada'] > 0) {
                    DetallePrestamo::create([
                        'id_prestamo' => $prestamo->id,
                        'tipo_item' => $detalleSolicitud->tipo_item,
                        'id_activo' => $detalleSolicitud->tipo_item === 'activo' ? $detalleSolicitud->id_activo : null,
                        'periferico_id' => $detalleSolicitud->tipo_item === 'periferico' ? $detalleSolicitud->periferico_id : null,
                        'cantidad' => $item['cantidad_asignada'],
                        'devuelto' => false
                    ]);

                    // Actualizar cantidades disponibles
                    if ($detalleSolicitud->tipo_item === 'activo') {
                        $activo = Activo::find($detalleSolicitud->id_activo);
                        if ($activo) {
                            $activo->decrement('cantidad', $item['cantidad_asignada']);
                        }
                    } else {
                        $periferico = Periferico::find($detalleSolicitud->periferico_id);
                        if ($periferico) {
                            $periferico->decrement('cantidad_disponible', $item['cantidad_asignada']);
                        }
                    }
                }
            }

            // Limpiar la sesión
            session()->forget("asignacion_{$solicitud->id}");

            DB::commit();

            NotificacionSistema::create([
                'usuario_id' => $solicitud->id_solicitante,
                'tipo' => 'prestamo_creado',
                'titulo' => '📦 Préstamo Registrado',
                'mensaje' => "Tu solicitud #{$solicitud->id} ha sido convertida en préstamo.",
                'datos_extra' => ['prestamo_id' => $prestamo->id],
                'fecha_envio' => now(),
                'leida' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Préstamo registrado exitosamente',
                'prestamo_id' => $prestamo->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extender la fecha de devolución de un préstamo
     */
    public function extendLoan(Request $request, Prestamo $prestamo)
    {
        $request->validate([
            'nueva_fecha' => 'required|date|after:' . $prestamo->fecha_retorno_estimada,
            'motivo' => 'required|string|min:10|max:500'
        ]);

        try {
            DB::beginTransaction();

            $extension = ExtensionPrestamo::create([
                'prestamo_id' => $prestamo->id,
                'solicitada_por' => auth()->id(),
                'nueva_fecha_devolucion' => $request->nueva_fecha,
                'motivo' => $request->motivo,
                'estado' => 'aprobada',
                'aprobada_por' => auth()->id(),
                'fecha_aprobacion' => now()
            ]);

            $fechaAnterior = $prestamo->fecha_retorno_estimada;
            $prestamo->update([
                'fecha_retorno_estimada' => $request->nueva_fecha
            ]);

            DB::commit();

            NotificacionSistema::create([
                'usuario_id' => $prestamo->solicitud->id_solicitante,
                'tipo' => 'prestamo_extendido',
                'titulo' => '📅 Préstamo Extendido',
                'mensaje' => "Tu préstamo ha sido extendido hasta el " . date('d/m/Y', strtotime($request->nueva_fecha)),
                'datos_extra' => ['prestamo_id' => $prestamo->id],
                'fecha_envio' => now(),
                'leida' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Préstamo extendido exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Devolución parcial de equipos
     */
    public function partialReturn(Request $request, Prestamo $prestamo)
    {
        $request->validate([
            'items_devueltos' => 'required|array',
            'items_devueltos.*.detalle_prestamo_id' => 'required|exists:detalle_prestamo,id',
            'items_devueltos.*.cantidad' => 'required|integer|min:1',
            'observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $devolucion = Devolucion::create([
                'id_prestamo' => $prestamo->id,
                'id_tecnico' => auth()->id(),
                'fecha_devolucion' => now()->toDateString(),
                'hora_devolucion' => now()->toTimeString(),
                'tipo_devolucion' => 'parcial',
                'observaciones' => $request->observaciones
            ]);

            foreach ($request->items_devueltos as $item) {
                $detallePrestamo = DetallePrestamo::find($item['detalle_prestamo_id']);

                if ($detallePrestamo) {
                    DetalleDevolucion::create([
                        'id_devolucion' => $devolucion->id,
                        'tipo_item' => $detallePrestamo->tipo_item,
                        'id_activo' => $detallePrestamo->id_activo,
                        'periferico_id' => $detallePrestamo->periferico_id,
                        'cantidad' => $item['cantidad'],
                        'observaciones' => $request->observaciones
                    ]);

                    // Marcar como devuelto si se devolvió todo
                    if ($item['cantidad'] >= $detallePrestamo->cantidad) {
                        $detallePrestamo->update(['devuelto' => true]);
                    }

                    // Restaurar cantidades disponibles
                    if ($detallePrestamo->tipo_item === 'activo' && $detallePrestamo->id_activo) {
                        $activo = Activo::find($detallePrestamo->id_activo);
                        if ($activo) {
                            $activo->increment('cantidad', $item['cantidad']);
                        }
                    } elseif ($detallePrestamo->periferico_id) {
                        $periferico = Periferico::find($detallePrestamo->periferico_id);
                        if ($periferico) {
                            $periferico->increment('cantidad_disponible', $item['cantidad']);
                        }
                    }
                }
            }

            // Verificar si todos los items están devueltos
            $todosDevueltos = $prestamo->detalles->every(function($detalle) {
                return $detalle->devuelto == true;
            });

            if ($todosDevueltos) {
                $prestamo->update([
                    'estado_prestamo' => 'completado',
                    'fecha_retorno_real' => now()->toDateString()
                ]);
            }

            DB::commit();

            NotificacionSistema::create([
                'usuario_id' => $prestamo->solicitud->id_solicitante,
                'tipo' => 'devolucion',
                'titulo' => '🔄 Devolución Registrada',
                'mensaje' => "Se ha registrado una devolución de tu préstamo #{$prestamo->id}",
                'datos_extra' => ['prestamo_id' => $prestamo->id],
                'fecha_envio' => now(),
                'leida' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Devolución registrada',
                'completado' => $todosDevueltos
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Devolución completa
     */
    public function completeReturn(Request $request, Prestamo $prestamo)
    {
        $request->validate([
            'observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $devolucion = Devolucion::create([
                'id_prestamo' => $prestamo->id,
                'id_tecnico' => auth()->id(),
                'fecha_devolucion' => now()->toDateString(),
                'hora_devolucion' => now()->toTimeString(),
                'tipo_devolucion' => 'total',
                'observaciones' => $request->observaciones
            ]);

            foreach ($prestamo->detalles as $detalle) {
                if (!$detalle->devuelto) {
                    DetalleDevolucion::create([
                        'id_devolucion' => $devolucion->id,
                        'tipo_item' => $detalle->tipo_item,
                        'id_activo' => $detalle->id_activo,
                        'periferico_id' => $detalle->periferico_id,
                        'cantidad' => $detalle->cantidad,
                        'observaciones' => $request->observaciones
                    ]);

                    $detalle->update(['devuelto' => true]);

                    // Restaurar cantidades
                    if ($detalle->tipo_item === 'activo' && $detalle->id_activo) {
                        $activo = Activo::find($detalle->id_activo);
                        if ($activo) $activo->increment('cantidad', $detalle->cantidad);
                    } elseif ($detalle->periferico_id) {
                        $periferico = Periferico::find($detalle->periferico_id);
                        if ($periferico) $periferico->increment('cantidad_disponible', $detalle->cantidad);
                    }
                }
            }

            $prestamo->update([
                'estado_prestamo' => 'completado',
                'fecha_retorno_real' => now()->toDateString()
            ]);

            DB::commit();

            NotificacionSistema::create([
                'usuario_id' => $prestamo->solicitud->id_solicitante,
                'tipo' => 'devolucion_completa',
                'titulo' => '✅ Devolución Completa',
                'mensaje' => "Tu préstamo #{$prestamo->id} ha sido completado",
                'datos_extra' => ['prestamo_id' => $prestamo->id],
                'fecha_envio' => now(),
                'leida' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Devolución completa registrada'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar acta de préstamo
     */
    public function generarActaPrestamo(Prestamo $prestamo)
    {
        $prestamo->load(['solicitud.solicitante', 'solicitud.institucion', 'detalles.activo', 'detalles.periferico', 'tecnico', 'responsable']);
        return view('pdf.acta_prestamo', compact('prestamo'));
    }

    /**
     * Generar acta de devolución
     */
    public function generarActaDevolucion(Prestamo $prestamo)
    {
        $devolucion = Devolucion::where('id_prestamo', $prestamo->id)->latest()->first();
        $prestamo->load(['solicitud.solicitante', 'detalles.activo', 'detalles.periferico']);
        return view('pdf.acta_devolucion', compact('prestamo', 'devolucion'));
    }

    /**
     * Obtener datos para el dashboard
     */
    public function getDashboardData()
    {
        $pendientes = Solicitud::where('estado_solicitud', 'pendiente')->count();
        $activos = Prestamo::where('estado_prestamo', 'activo')->count();
        $vencidos = Prestamo::where('estado_prestamo', 'activo')
            ->where('fecha_retorno_estimada', '<', now()->toDateString())
            ->count();

        return response()->json([
            'pendientes' => $pendientes,
            'activos' => $activos,
            'vencidos' => $vencidos
        ]);
    }
}