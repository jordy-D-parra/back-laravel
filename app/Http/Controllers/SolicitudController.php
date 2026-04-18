<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\DetalleSolicitud;
use App\Models\Activo;
use App\Models\Periferico;
use App\Models\Institucion;
use App\Models\Responsable;
use App\Models\NotificacionSistema;
use App\Events\SolicitudCreada;
use App\Events\SolicitudAprobada;
use App\Events\SolicitudRechazada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SolicitudController extends Controller
{

public function index()
{
    $solicitudes = Solicitud::with(['detalles.activo', 'detalles.periferico', 'institucion'])
        ->where('id_solicitante', auth()->id())
        ->paginate(10);

    $activos = Activo::all();
    $perifericos = Periferico::all();

    return view('solicitudes.index', compact('solicitudes', 'activos', 'perifericos'));
}
   public function getItemsJson($id)
{
    try {
        $solicitud = Solicitud::with(['detalles.activo', 'detalles.periferico'])->find($id);

        if (!$solicitud) {
            return response()->json(['error' => 'Solicitud no encontrada'], 404);
        }

        $items = [];
        foreach($solicitud->detalles as $detalle) {
            $descripcion = '';
            if ($detalle->tipo_item == 'activo' && $detalle->activo) {
                $descripcion = $detalle->activo->serial . ' - ' . $detalle->activo->marca_modelo;
            } elseif ($detalle->periferico) {
                $descripcion = $detalle->periferico->nombre;
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
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    /**
     * Mostrar formulario crear solicitud
     */
    public function create()
    {
        $activos = Activo::where('cantidad', '>', 0)->get();
        $perifericos = Periferico::where('cantidad_disponible', '>', 0)->get();
        $instituciones = Institucion::where('activo', true)->get();
        $responsables = Responsable::all();

        return view('solicitudes.create', compact('activos', 'perifericos', 'instituciones', 'responsables'));
    }

    /**
     * Guardar nueva solicitud
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_solicitante' => 'required|in:interno,externo',
            'institucion_id' => 'required_if:tipo_solicitante,externo|exists:institucion,id|nullable',
            'responsable_id' => 'nullable|exists:responsable,id',
            'fecha_requerida' => 'required|date|after_or_equal:today',
            'fecha_fin_estimada' => 'required|date|after_or_equal:fecha_requerida',
            'justificacion' => 'required|string|min:20|max:1000',
            'prioridad' => 'required|in:baja,normal,alta,urgente',
            'observaciones' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.tipo_item' => 'required|in:activo,periferico',
            'items.*.item_id' => 'required|integer',
            'items.*.cantidad' => 'required|integer|min:1',
            'oficio_adjunto' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $oficioPath = null;
            if ($request->hasFile('oficio_adjunto')) {
                $oficioPath = $request->file('oficio_adjunto')->store('solicitudes/oficios', 'public');
            }

            $solicitud = Solicitud::create([
                'id_solicitante' => auth()->id(),
                'tipo_solicitante' => $request->tipo_solicitante,
                'institucion_id' => $request->tipo_solicitante === 'externo' ? $request->institucion_id : null,
                'responsable_id' => $request->responsable_id,
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
                if ($item['tipo_item'] === 'activo') {
                    $activo = Activo::find($item['item_id']);
                    if (!$activo || $activo->cantidad < $item['cantidad']) {
                        throw new \Exception("No hay suficiente cantidad disponible del activo seleccionado");
                    }
                } else {
                    $periferico = Periferico::find($item['item_id']);
                    if (!$periferico || $periferico->cantidad_disponible < $item['cantidad']) {
                        throw new \Exception("No hay suficiente cantidad disponible del periférico seleccionado");
                    }
                }

                DetalleSolicitud::create([
                    'id_solicitud' => $solicitud->id,
                    'tipo_item' => $item['tipo_item'],
                    'cantidad_solicitada' => $item['cantidad'],
                    'id_activo' => $item['tipo_item'] === 'activo' ? $item['item_id'] : null,
                    'periferico_id' => $item['tipo_item'] === 'periferico' ? $item['item_id'] : null,
                    'observaciones' => $item['observaciones'] ?? null
                ]);
            }

            DB::commit();

            event(new SolicitudCreada($solicitud));

            return redirect()->route('solicitudes.index')
                ->with('success', '✅ Solicitud creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '❌ Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Ver detalle de solicitud
     */
    public function show(Solicitud $solicitud)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->hasRole('admin') && $user->id !== $solicitud->id_solicitante) {
            abort(403);
        }

        $solicitud->load(['solicitante', 'aprobador', 'detalles.activo', 'detalles.periferico', 'institucion', 'responsable']);

        return view('solicitudes.show', compact('solicitud'));
    }

    /**
     * Aprobar solicitud
     */
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
                if ($detalle->tipo_item === 'activo') {
                    $activo = Activo::find($detalle->id_activo);
                    if (!$activo || $activo->cantidad < $detalle->cantidad_solicitada) {
                        throw new \Exception("No hay suficiente cantidad del activo");
                    }
                } else {
                    $periferico = Periferico::find($detalle->periferico_id);
                    if (!$periferico || $periferico->cantidad_disponible < $detalle->cantidad_solicitada) {
                        throw new \Exception("No hay suficiente cantidad del periférico");
                    }
                }
            }

            $solicitud->update([
                'estado_solicitud' => 'aprobada',
                'aprobado_por' => $user->id,
                'fecha_aprobacion' => now()
            ]);

            DB::commit();

            event(new SolicitudAprobada($solicitud));

            NotificacionSistema::create([
                'usuario_id' => $solicitud->id_solicitante,
                'tipo' => 'solicitud_aprobada',
                'titulo' => '✅ Solicitud Aprobada',
                'mensaje' => "Tu solicitud #{$solicitud->id} ha sido aprobada",
                'datos_extra' => ['solicitud_id' => $solicitud->id],
                'fecha_envio' => now()
            ]);

            return redirect()->route('solicitudes.show', $solicitud)
                ->with('success', 'Solicitud aprobada');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Rechazar solicitud
     */
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

        event(new SolicitudRechazada($solicitud, $request->motivo));

        NotificacionSistema::create([
            'usuario_id' => $solicitud->id_solicitante,
            'tipo' => 'solicitud_rechazada',
            'titulo' => '❌ Solicitud Rechazada',
            'mensaje' => "Tu solicitud #{$solicitud->id} ha sido rechazada. Motivo: {$request->motivo}",
            'datos_extra' => ['solicitud_id' => $solicitud->id],
            'fecha_envio' => now()
        ]);

        return redirect()->route('solicitudes.show', $solicitud)
            ->with('success', 'Solicitud rechazada');
    }

    /**
     * Cancelar solicitud
     */
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

    /**
     * Descargar oficio adjunto
     */
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
}
