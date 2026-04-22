<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\Estatus;
use App\Models\TipoActivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ActivoController extends Controller
{
    /**
     * Display a listing of the resource (para vista principal)
     */
    public function index()
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            abort(403, 'No tienes permiso para acceder a esta sección');
        }

        $tiposActivo = TipoActivo::all();
        $estatusList = Estatus::all();
        
        return view('inventario.index', compact('tiposActivo', 'estatusList'));
    }

    /**
     * Get data for DataTable (AJAX)
     */
    public function getData(Request $request)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $query = Activo::with(['estatus', 'tipoActivo']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('serial', 'like', "%{$search}%")
                  ->orWhere('marca_modelo', 'like', "%{$search}%")
                  ->orWhere('ubicacion', 'like', "%{$search}%");
            });
        }

        if ($request->filled('id_tipo_activo')) {
            $query->where('id_tipo_activo', $request->id_tipo_activo);
        }

        if ($request->filled('id_estatus')) {
            $query->where('id_estatus', $request->id_estatus);
        }

        // Ordenar por fecha de creación descendente
        $activos = $query->orderBy('id', 'desc')->paginate(15);

        return response()->json($activos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        // Validación actualizada sin valor_compra
        $request->validate([
            'serial' => 'required|unique:activo,serial|max:100',
            'tipo_equipo' => 'required|in:principal,secundario,componente',
            'marca_modelo' => 'required|max:200',
            'id_estatus' => 'required|integer|min:1',
            'id_tipo_activo' => 'required|integer|min:1',
            'cantidad' => 'required|integer|min:0',
            'ubicacion' => 'nullable|max:100',
            'disponible_desde' => 'nullable|date',
            'fecha_adquisicion' => 'nullable|date',
            'vida_util_anos' => 'nullable|integer|min:1|max:50',
            'fecha_fin_garantia' => 'nullable|date|after_or_equal:fecha_adquisicion',
            'observaciones' => 'nullable|string',
            'especificaciones_tecnicas' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            // Preparar datos
            $data = $request->except('especificaciones_tecnicas');
            
            // Convertir especificaciones a JSON si vienen como array
            if ($request->has('especificaciones_tecnicas') && is_array($request->especificaciones_tecnicas)) {
                $data['especificaciones_tecnicas'] = json_encode($request->especificaciones_tecnicas);
            }

            $activo = Activo::create($data);

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Activo creado exitosamente',
                'activo' => $activo->load(['estatus', 'tipoActivo'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false, 
                'message' => 'Error al crear el activo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $activo = Activo::with(['estatus', 'tipoActivo'])->findOrFail($id);
        
        // Decodificar especificaciones técnicas si existen
        if ($activo->especificaciones_tecnicas && is_string($activo->especificaciones_tecnicas)) {
            $activo->especificaciones_tecnicas = json_decode($activo->especificaciones_tecnicas, true);
        }
        
        return response()->json($activo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $activo = Activo::findOrFail($id);

        // Validación actualizada sin valor_compra
        $request->validate([
            'serial' => 'required|max:100|unique:activo,serial,' . $id,
            'tipo_equipo' => 'required|in:principal,secundario,componente',
            'marca_modelo' => 'required|max:200',
            'id_estatus' => 'required|integer|min:1',
            'id_tipo_activo' => 'required|integer|min:1',
            'cantidad' => 'required|integer|min:0',
            'ubicacion' => 'nullable|max:100',
            'disponible_desde' => 'nullable|date',
            'fecha_adquisicion' => 'nullable|date',
            'vida_util_anos' => 'nullable|integer|min:1|max:50',
            'fecha_fin_garantia' => 'nullable|date|after_or_equal:fecha_adquisicion',
            'observaciones' => 'nullable|string',
            'especificaciones_tecnicas' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            // Preparar datos
            $data = $request->except('especificaciones_tecnicas');
            
            // Convertir especificaciones a JSON si vienen como array
            if ($request->has('especificaciones_tecnicas') && is_array($request->especificaciones_tecnicas)) {
                $data['especificaciones_tecnicas'] = json_encode($request->especificaciones_tecnicas);
            }

            $activo->update($data);

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Activo actualizado exitosamente',
                'activo' => $activo->load(['estatus', 'tipoActivo'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false, 
                'message' => 'Error al actualizar el activo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $activo = Activo::findOrFail($id);
            $activo->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Activo eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error al eliminar el activo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener activos próximos a vencer su vida útil
     */
    public function getProximosAVencer()
    {
        $hoy = now();
        $seisMesesDespues = now()->addMonths(6);
        
        $activos = Activo::with(['estatus', 'tipoActivo'])
            ->whereNotNull('fecha_adquisicion')
            ->whereNotNull('vida_util_anos')
            ->where('id_estatus', '!=', 4) // Excluir dados de baja
            ->get()
            ->filter(function($activo) use ($hoy, $seisMesesDespues) {
                $fechaFin = $activo->fecha_adquisicion->copy()->addYears($activo->vida_util_anos);
                return $fechaFin->between($hoy, $seisMesesDespues);
            });
        
        return response()->json([
            'success' => true,
            'data' => $activos->values()
        ]);
    }
}