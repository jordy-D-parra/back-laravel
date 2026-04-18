<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\FichaSoporte;
use App\Models\FichaSoporteDetalle;
use App\Models\Seriales_activo;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SoporteController extends Controller
{
    /**
     * Dashboard de soporte
     */
    public function index()
    {
        $fichas = FichaSoporte::with(['activo', 'tecnico'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $estadisticas = [
            'en_proceso' => FichaSoporte::where('estado', 'en_proceso')->count(),
            'completados' => FichaSoporte::where('estado', 'completado')->count(),
            'pendientes' => FichaSoporte::where('estado', 'pendiente')->count(),
            'total_mes' => FichaSoporte::whereMonth('created_at', now()->month)->count(),
        ];

        return view('soporte.index', compact('fichas', 'estadisticas'));
    }

    /**
     * Formulario para crear nueva ficha de soporte
     */
    public function create()
    {
        // Obtener solo activos que tienen stock disponible o seriales disponibles
        $activos = Activo::where('id_estatus', 1)
            ->where(function($q) {
                $q->where('cantidad', '>', 0)
                  ->orWhereHas('seriales', function($sq) {
                      $sq->where('estado', 'disponible');
                  });
            })
            ->orderBy('marca_modelo')
            ->get();
        
        // Agregar seriales disponibles a cada activo para la vista
        foreach ($activos as $activo) {
            $activo->seriales_disponibles = $activo->seriales()->where('estado', 'disponible')->get();
        }
        
        // Obtener técnicos: SOLO admin y super_admin
        $rolesTecnicos = Rol::whereIn('nombre', ['admin', 'super_admin'])->pluck('id')->toArray();
        
        $tecnicos = User::whereIn('id_rol', $rolesTecnicos)
            ->where('estado_usuario', 'activo')
            ->get();
        
        return view('soporte.create', compact('activos', 'tecnicos'));
    }

    /**
     * Crear ficha de soporte
     */
    public function store(Request $request)
{
    // Validar los datos
    $request->validate([
        'serial_id' => 'required', // El ID del serial o 'sin_serial_X'
        'activo_id' => 'required|exists:activo,id',
        'tecnico_id' => 'nullable|exists:usuario,id',
        'diagnostico' => 'required|string|min:10',
        'observaciones' => 'nullable|string'
    ]);

    try {
        DB::beginTransaction();
        
        $activo = Activo::findOrFail($request->activo_id);
        $serialUsado = null;
        
        // Verificar si es un serial real o un equipo sin serial
        if (strpos($request->serial_id, 'sin_serial_') === false) {
            // Es un serial real
            $serial = Seriales_activo::findOrFail($request->serial_id);
            $serialUsado = $serial->serial;
            $serial->update(['estado' => 'reparacion']);
        }

        // Asignar técnico automáticamente
        $tecnicoId = $request->tecnico_id;
        if (empty($tecnicoId)) {
            $rolesTecnicos = Rol::whereIn('nombre', ['admin', 'super_admin'])->pluck('id')->toArray();
            $tecnicoAsignado = User::whereIn('id_rol', $rolesTecnicos)
                ->where('estado_usuario', 'activo')
                ->first();
            $tecnicoId = $tecnicoAsignado ? $tecnicoAsignado->id : Auth::id();
        }

        // Crear la ficha de soporte
        $ficha = FichaSoporte::create([
            'activo_id' => $request->activo_id,
            'tecnico_id' => $tecnicoId,
            'usuario_reporta_id' => Auth::id(),
            'serial_asignado' => $serialUsado,
            'fecha_ingreso' => now(),
            'diagnostico' => $request->diagnostico,
            'observaciones' => $request->observaciones,
            'estado' => 'en_proceso',
            'creado_por' => Auth::id()
        ]);

        DB::commit();

        $nombreTecnico = User::find($tecnicoId)->nombre ?? 'Técnico asignado';

        return redirect()->route('soporte.index')
            ->with('success', '✅ Ficha de soporte #' . $ficha->id . ' creada. Serial: ' . ($serialUsado ?? 'Ninguno') . ' | Técnico: ' . $nombreTecnico);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al crear ficha: ' . $e->getMessage());
        return redirect()->route('soporte.create')
            ->with('error', 'Error: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Ver detalle de ficha de soporte
     */
    public function show($id)
    {
        $ficha = FichaSoporte::with(['activo', 'tecnico', 'componentes.activo'])->findOrFail($id);
        
        $componentesDisponibles = Activo::where('tipo_equipo', 'componente')
            ->where('id_estatus', 1)
            ->with('seriales')
            ->get();
        
        // Obtener técnicos: SOLO admin y super_admin
        $rolesTecnicos = Rol::whereIn('nombre', ['admin', 'super_admin'])->pluck('id')->toArray();
        $tecnicos = User::whereIn('id_rol', $rolesTecnicos)
            ->where('estado_usuario', 'activo')
            ->get();
        
        return view('soporte.show', compact('ficha', 'componentesDisponibles', 'tecnicos'));
    }

    /**
     * Asignar técnico a la ficha
     */
    public function asignarTecnico(Request $request, $id)
    {
        $request->validate([
            'tecnico_id' => 'required|exists:usuario,id'
        ]);
        
        $ficha = FichaSoporte::findOrFail($id);
        $ficha->update([
            'tecnico_id' => $request->tecnico_id,
            'estado' => 'en_proceso'
        ]);

        $nombreTecnico = User::find($request->tecnico_id)->nombre ?? 'Técnico';

        return redirect()->route('soporte.show', $ficha)
            ->with('success', '👨‍🔧 Técnico ' . $nombreTecnico . ' asignado correctamente');
    }

    /**
     * Actualizar trabajo realizado
     */
    public function actualizarTrabajo(Request $request, $id)
    {
        $request->validate([
            'trabajo_realizado' => 'required|string|min:10'
        ]);
        
        $ficha = FichaSoporte::findOrFail($id);
        $ficha->update([
            'trabajo_realizado' => $request->trabajo_realizado
        ]);

        return redirect()->route('soporte.show', $ficha)
            ->with('success', '🛠️ Trabajo actualizado correctamente');
    }

    /**
     * Completar ficha de soporte
     */
    public function completar(Request $request, $id)
    {
        $request->validate([
            'fecha_entrega' => 'required|date',
            'costo_reparacion' => 'nullable|numeric|min:0'
        ]);
        
        try {
            DB::beginTransaction();
            
            $ficha = FichaSoporte::findOrFail($id);
            
            // Liberar el serial si estaba asignado
            if ($ficha->serial_asignado && $ficha->activo_id) {
                $serial = Seriales_activo::where('serial', $ficha->serial_asignado)
                    ->where('activo_id', $ficha->activo_id)
                    ->first();
                
                if ($serial && $serial->estado === 'reparacion') {
                    $serial->update(['estado' => 'disponible']);
                }
            }
            
            $ficha->update([
                'estado' => 'completado',
                'fecha_entrega' => $request->fecha_entrega,
                'costo_reparacion' => $request->costo_reparacion
            ]);
            
            DB::commit();

            return redirect()->route('soporte.show', $ficha)
                ->with('success', '✅ Ficha de soporte completada y serial liberado');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al completar: ' . $e->getMessage());
        }
    }

    /**
     * Agregar componente a la ficha
     */
    public function agregarComponente(Request $request, $id)
    {
        $request->validate([
            'componente_id' => 'required|exists:activo,id',
            'serial_id' => 'nullable|exists:seriales_activo,id',
            'cantidad' => 'required|integer|min:1',
            'descripcion' => 'nullable|string'
        ]);
        
        try {
            DB::beginTransaction();
            
            $ficha = FichaSoporte::findOrFail($id);
            $componente = Activo::findOrFail($request->componente_id);
            
            $serialUsado = null;
            
            if ($request->serial_id) {
                $serial = Seriales_activo::findOrFail($request->serial_id);
                if ($serial->estado !== 'disponible') {
                    return redirect()->back()->with('error', 'El serial seleccionado no está disponible');
                }
                $serialUsado = $serial->serial;
                $serial->update(['estado' => 'reparacion']);
            } else {
                $serialUsado = $componente->serial;
                $componente->decrement('cantidad', $request->cantidad);
            }
            
            FichaSoporteDetalle::create([
                'ficha_soporte_id' => $id,
                'activo_id' => $request->componente_id,
                'serial_usado' => $serialUsado,
                'cantidad' => $request->cantidad,
                'descripcion' => $request->descripcion,
                'fecha_salida' => now()
            ]);
            
            DB::commit();

            return redirect()->route('soporte.show', $ficha)
                ->with('success', '💾 Componente agregado correctamente. Serial: ' . $serialUsado);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al agregar componente: ' . $e->getMessage());
        }
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
        'equipo_nombre' => 'required|string|max:200',
        'serial' => 'nullable|string|max:100',
        'marca' => 'nullable|string|max:100',
        'modelo' => 'nullable|string|max:100',
        'diagnostico' => 'required|string|min:10',
        'observaciones' => 'nullable|string'
    ]);

    try {
        DB::beginTransaction();

        // Construir el nombre completo del equipo externo
        $nombreCompleto = $request->equipo_nombre;
        $marcaModelo = '';
        if ($request->marca || $request->modelo) {
            $marcaModelo = trim($request->marca . ' ' . $request->modelo);
            $nombreCompleto .= ' (' . $marcaModelo . ')';
        }
        if ($request->serial) {
            $nombreCompleto .= ' - Serial: ' . $request->serial;
        }

        // Buscar o crear la categoría "Equipo Externo"
        $categoriaExterna = \App\Models\TipoActivo::firstOrCreate(
            ['nombre' => 'Equipo Externo'],
            [
                'categoria' => 'externo',
                'requiere_serial' => false,
                'requiere_cantidad' => false,
                'requiere_mantenimiento' => true,
                'vida_util_meses' => null
            ]
        );

        // Buscar o crear el estatus "En Reparación"
        $estatusReparacion = \App\Models\Estatus::firstOrCreate(
            ['descripcion' => 'En Reparación'],
            ['color_badge' => 'warning']
        );

        // Generar un serial único si no se proporcionó
        $serialActivo = $request->serial ?? 'EXT-' . strtoupper(uniqid());

        // Crear el activo en la tabla 'activo'
        $activo = Activo::create([
            'serial' => $serialActivo,
            'marca_modelo' => $marcaModelo ?: $request->equipo_nombre,
            'capacidad' => null,
            'tipo_equipo' => 'externo',
            'id_tipo_activo' => $categoriaExterna->id,
            'id_estatus' => $estatusReparacion->id,
            'cantidad' => 1,
            'ubicacion' => 'Soporte Técnico - Equipo Externo',
            'detalles_tecnicos' => 'Equipo registrado desde soporte externo. Diagnóstico: ' . substr($request->diagnostico, 0, 200),
            'observaciones' => $request->observaciones,
            'fecha_adquisicion' => now(),
            'disponible_desde' => now(),
            'creado_por' => Auth::id()
        ]);

        // Si tiene serial, crearlo también en la tabla seriales_activo
        if ($request->serial) {
            Seriales_activo::create([
                'activo_id' => $activo->id,
                'serial' => $request->serial,
                'estado' => 'reparacion'
            ]);
        }

        // Obtener el primer admin/super_admin disponible para técnico
        $rolesTecnicos = Rol::whereIn('nombre', ['admin', 'super_admin'])->pluck('id')->toArray();
        $tecnicoAsignado = User::whereIn('id_rol', $rolesTecnicos)
            ->where('estado_usuario', 'activo')
            ->first();
        
        $tecnicoId = $tecnicoAsignado ? $tecnicoAsignado->id : Auth::id();

        // Crear la ficha de soporte vinculada al nuevo activo
        $ficha = FichaSoporte::create([
            'activo_id' => $activo->id,
            'equipo_externo_nombre' => $nombreCompleto,
            'tecnico_id' => $tecnicoId,
            'usuario_reporta_id' => Auth::id(),
            'serial_asignado' => $request->serial,
            'fecha_ingreso' => now(),
            'diagnostico' => $request->diagnostico,
            'observaciones' => $request->observaciones,
            'estado' => 'en_proceso',
            'creado_por' => Auth::id()
        ]);

        DB::commit();

        return redirect()->route('soporte.index')
            ->with('success', '📱 Equipo externo registrado correctamente. Se ha creado en inventario. Ficha #' . $ficha->id);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error al registrar equipo externo: ' . $e->getMessage());
        return redirect()->route('soporte.externo')
            ->with('error', 'Error al registrar: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * API para obtener seriales de un activo
     */
    public function getComponentes($activoId)
    {
        $activo = Activo::findOrFail($activoId);
        $seriales = $activo->seriales()->where('estado', 'disponible')->get();
        
        return response()->json([
            'success' => true,
            'activo' => $activo,
            'seriales' => $seriales,
            'tiene_seriales' => $seriales->count() > 0
        ]);
    }

 public function getDatosFicha($id)
{
    try {
        $ficha = FichaSoporte::with(['activo', 'tecnico.rol', 'componentes.activo'])->findOrFail($id);
        return response()->json($ficha);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}