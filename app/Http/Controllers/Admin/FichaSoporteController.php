<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FichaSoporte;
use App\Models\FichaSoporteDetalle;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Usuario;
use App\Models\Estatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FichaSoporteController extends Controller
{
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
            ->whereDoesntHave('fichasSoporte', function($q) {
                $q->where('estado', 'en_proceso');
            })
            ->when($estatusReparacion, function($q) use ($estatusReparacion) {
                $q->where('id_estatus', '!=', $estatusReparacion->id);
            })
            ->orderBy('serial')
            ->get();

        // Técnicos disponibles (usuarios con rol admin, ingeniero o tecnico)
        $tecnicos = Usuario::whereHas('rol', function($q) {
                $q->whereIn('nombre', ['admin', 'ingeniero', 'tecnico']);
            })
            ->with('trabajador')
            ->orderBy('usuario')
            ->get();

        // Estadísticas para el dashboard
        $totalFichas = FichaSoporte::count();
        $enProceso = FichaSoporte::where('estado', 'en_proceso')->count();
        $finalizados = FichaSoporte::where('estado', 'finalizado')->count();
        $equiposReparacion = Activo::whereHas('estatus', function($q) {
            $q->where('descripcion', 'En reparación');
        })->count();

        return view('admin.soporte.index', compact(
            'fichas', 
            'activosDisponibles', 
            'tecnicos',
            'totalFichas',
            'enProceso',
            'finalizados',
            'equiposReparacion'
        ));
    }

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
/**
 * Registrar equipo externo y crear ficha de soporte
 */
public function storeEquipoExterno(Request $request)
{
    if (!auth()->user()->hasPermission('crear-ficha-soporte')) {
        return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
    }

    try {
        $validated = $request->validate([
            // Datos del equipo
            'serial' => 'required|string|max:100|unique:activos,serial',
            'modelo_nombre' => 'required|string|max:100',
            'marca' => 'required|string|max:100',
            'categoria_id' => 'required|exists:categorias,id',
            'institucion_id' => 'required|exists:instituciones,id',
            'responsable_id' => 'required|exists:responsables,id',
            'ubicacion' => 'nullable|string|max:100',
            'fecha_adquisicion' => 'nullable|date',
            'observaciones' => 'nullable|string',
            
            // Datos de la ficha
            'tecnico_id' => 'nullable|exists:usuarios,id',
            'tecnico_nombre' => 'nullable|string|max:150',
            'usuario_reporta_nombre' => 'required|string|max:150',
            'diagnostico' => 'nullable|string',
            'observaciones_ficha' => 'nullable|string',
        ]);

        DB::beginTransaction();

        // 1. Crear la marca si no existe
        $marca = \App\Models\Marca::firstOrCreate(
            ['nombre' => $validated['marca']],
            ['activo' => true]
        );

        // 2. Crear el modelo si no existe
        $modelo = \App\Models\Modelo::firstOrCreate(
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
        $estatusReparacion = \App\Models\Estatus::where('descripcion', 'En reparación')->first();
        if (!$estatusReparacion) {
            throw new \Exception('No se encontró el estatus "En reparación"');
        }

        // 4. Crear el activo en inventario con estado "En reparación"
        $activo = \App\Models\Activo::create([
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
            $tecnico = \App\Models\Usuario::with('trabajador')->find($validated['tecnico_id']);
            if ($tecnico && $tecnico->trabajador) {
                $tecnicoNombre = $tecnico->trabajador->nombre . ' ' . $tecnico->trabajador->apellido;
            }
        }

        $ficha = \App\Models\FichaSoporte::create([
            'activo_id' => $activo->id,
            'tecnico_id' => $validated['tecnico_id'] ?? null,
            'tecnico_nombre' => $tecnicoNombre ?? 'No asignado',
            'usuario_reporta_id' => auth()->id(),
            'usuario_reporta_nombre' => $validated['usuario_reporta_nombre'],
            'fecha_ingreso' => now(),
            'diagnostico' => $validated['diagnostico'] ?? null,
            'observaciones' => $validated['observaciones_ficha'] ?? null,
            'estado' => 'en_proceso',
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

    public function show($id)
    {
        if (!auth()->user()->hasPermission('ver-fichas-soporte')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $ficha = FichaSoporte::with([
                'activo.modelo.marca', 
                'tecnico.trabajador', 
                'detalles.componente'
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
}