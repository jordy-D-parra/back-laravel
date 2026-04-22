<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\FichaSoporte;
use App\Models\FichaSoporteDetalle;
use App\Models\Componente;
use App\Models\NotificacionSistema;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SoporteController extends Controller
{

    /**
     * Dashboard de soporte
     */
    public function index()
    {
        $fichas = FichaSoporte::with(['activo', 'tecnico', 'usuarioReporta'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $estadisticas = [
            'en_proceso' => FichaSoporte::where('estado', 'en_proceso')->count(),
            'completados' => FichaSoporte::where('estado', 'completado')->count(),
            'pendientes' => FichaSoporte::where('estado', 'pendiente')->count(),
            'total_mes' => FichaSoporte::whereMonth('created_at', now()->month)->count(),
        ];

        $activosEnSoporte = FichaSoporte::where('estado', 'en_proceso')
            ->with('activo')
            ->get();

        return view('soporte.index', compact('fichas', 'estadisticas', 'activosEnSoporte'));
    }

    /**
     * Formulario para crear nueva ficha de soporte
     */
    public function create()
    {
        $activos = Activo::where('cantidad', '>', 0)->get();
        $tecnicos = User::whereHas('rol', function($q) {
            $q->whereIn('nombre', ['tecnico', 'super_admin', 'admin']);
        })->get();

        return view('soporte.create', compact('activos', 'tecnicos'));
    }

    /**
     * Crear ficha de soporte
     */
    public function store(Request $request)
    {
        $request->validate([
            'activo_id' => 'required|exists:activo,id',
            'tecnico_id' => 'nullable|exists:usuario,id',
            'diagnostico' => 'required|string|min:10',
            'observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $ficha = FichaSoporte::create([
                'activo_id' => $request->activo_id,
                'tecnico_id' => $request->tecnico_id,
                'usuario_reporta_id' => auth()->id(),
                'fecha_ingreso' => now(),
                'diagnostico' => $request->diagnostico,
                'observaciones' => $request->observaciones,
                'estado' => $request->tecnico_id ? 'en_proceso' : 'pendiente'
            ]);

            // Si hay componentes afectados, registrarlos
            if ($request->has('componentes')) {
                foreach ($request->componentes as $componente) {
                    FichaSoporteDetalle::create([
                        'ficha_soporte_id' => $ficha->id,
                        'componente_id' => $componente['id'],
                        'estado_ingreso' => $componente['estado'] ?? 'desconocido',
                        'observaciones' => $componente['observaciones'] ?? null
                    ]);
                }
            }

            DB::commit();

            // Notificar al técnico si fue asignado
            if ($request->tecnico_id) {
                NotificacionSistema::create([
                    'usuario_id' => $request->tecnico_id,
                    'tipo' => 'soporte_asignado',
                    'titulo' => '🔧 Nueva Ficha de Soporte',
                    'mensaje' => "Se te ha asignado una nueva ficha de soporte #{$ficha->id}",
                    'datos_extra' => ['ficha_id' => $ficha->id],
                    'fecha_envio' => now(),
                    'leida' => false
                ]);
            }

            return redirect()->route('soporte.show', $ficha)
                ->with('success', '✅ Ficha de soporte creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de ficha de soporte
     */
    public function show(FichaSoporte $ficha)
    {
        $ficha->load(['activo', 'tecnico', 'usuarioReporta', 'detalles.componente']);
        $tecnicos = User::whereHas('rol', function($q) {
            $q->whereIn('nombre', ['tecnico', 'super_admin', 'admin']);
        })->get();

        return view('soporte.show', compact('ficha', 'tecnicos'));
    }

    /**
     * Asignar técnico a la ficha
     */
    public function asignarTecnico(Request $request, FichaSoporte $ficha)
    {
        $request->validate([
            'tecnico_id' => 'required|exists:usuario,id'
        ]);

        $ficha->update([
            'tecnico_id' => $request->tecnico_id,
            'estado' => 'en_proceso'
        ]);

        NotificacionSistema::create([
            'usuario_id' => $request->tecnico_id,
            'tipo' => 'soporte_asignado',
            'titulo' => '🔧 Ficha de Soporte Asignada',
            'mensaje' => "Se te ha asignado la ficha de soporte #{$ficha->id}",
            'datos_extra' => ['ficha_id' => $ficha->id],
            'fecha_envio' => now(),
            'leida' => false
        ]);

        return redirect()->route('soporte.show', $ficha)
            ->with('success', 'Técnico asignado correctamente');
    }

    /**
     * Actualizar trabajo realizado
     */
    public function actualizarTrabajo(Request $request, FichaSoporte $ficha)
    {
        $request->validate([
            'trabajo_realizado' => 'required|string|min:10',
            'observaciones' => 'nullable|string'
        ]);

        $ficha->update([
            'trabajo_realizado' => $request->trabajo_realizado,
            'observaciones' => $request->observaciones
        ]);

        return redirect()->route('soporte.show', $ficha)
            ->with('success', 'Trabajo actualizado correctamente');
    }

    /**
     * Completar ficha de soporte
     */
    public function completar(Request $request, FichaSoporte $ficha)
    {
        $request->validate([
            'observaciones_finales' => 'nullable|string'
        ]);

        $ficha->update([
            'estado' => 'completado',
            'fecha_salida' => now(),
            'observaciones' => $request->observaciones_finales ?? $ficha->observaciones
        ]);

        // Actualizar estado del activo a disponible
        $ficha->activo->update(['id_estatus' => 1]); // 1 = disponible

        NotificacionSistema::create([
            'usuario_id' => $ficha->usuario_reporta_id,
            'tipo' => 'soporte_completado',
            'titulo' => '✅ Soporte Completado',
            'mensaje' => "El soporte para el equipo {$ficha->activo->serial} ha sido completado",
            'datos_extra' => ['ficha_id' => $ficha->id],
            'fecha_envio' => now(),
            'leida' => false
        ]);

        return redirect()->route('soporte.show', $ficha)
            ->with('success', 'Ficha de soporte completada');
    }

    /**
     * Registrar componentes afectados
     */
    public function agregarComponente(Request $request, FichaSoporte $ficha)
    {
        $request->validate([
            'componente_id' => 'required|exists:componente,id',
            'estado_ingreso' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string'
        ]);

        FichaSoporteDetalle::create([
            'ficha_soporte_id' => $ficha->id,
            'componente_id' => $request->componente_id,
            'estado_ingreso' => $request->estado_ingreso,
            'observaciones' => $request->observaciones
        ]);

        return redirect()->route('soporte.show', $ficha)
            ->with('success', 'Componente agregado correctamente');
    }

    /**
     * Actualizar estado de componente al salir
     */
    public function actualizarComponenteSalida(Request $request, FichaSoporteDetalle $detalle)
    {
        $request->validate([
            'estado_salida' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string'
        ]);

        $detalle->update([
            'estado_salida' => $request->estado_salida,
            'observaciones' => $request->observaciones
        ]);

        return redirect()->back()->with('success', 'Componente actualizado correctamente');
    }

    /**
     * Soporte para equipos externos (no en inventario)
     */
    public function soporteExterno()
    {
        return view('soporte.externo');
    }

    /**
     * Registrar equipo externo para soporte
     */
    public function registrarExterno(Request $request)
    {
        $request->validate([
            'nombre_equipo' => 'required|string|max:200',
            'serial' => 'nullable|string|max:100',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'diagnostico' => 'required|string|min:10'
        ]);

        try {
            DB::beginTransaction();

            // Crear un registro temporal en activo? O tabla especial
            // Por ahora, creamos una ficha sin vincular a activo
            $ficha = FichaSoporte::create([
                'activo_id' => null, // Equipo externo
                'tecnico_id' => null,
                'usuario_reporta_id' => auth()->id(),
                'fecha_ingreso' => now(),
                'diagnostico' => $request->diagnostico,
                'observaciones' => "Equipo Externo: {$request->nombre_equipo} | Serial: {$request->serial} | Marca: {$request->marca} | Modelo: {$request->modelo}",
                'estado' => 'pendiente'
            ]);

            DB::commit();

            return redirect()->route('soporte.show', $ficha)
                ->with('success', 'Soporte para equipo externo registrado');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * API para obtener componentes de un activo
     */
    public function getComponentes(Activo $activo)
    {
        $componentes = $activo->componentes()->withPivot('cantidad')->get();
        return response()->json($componentes);
    }
}
