<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\Estatus;
use App\Models\TipoActivo;
use App\Models\Seriales_activo; // ← Nombre correcto
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
                  ->orWhere('ubicacion', 'like', "%{$search}%")
                  ->orWhere('capacidad', 'like', "%{$search}%");
            });
        }

        if ($request->filled('id_tipo_activo')) {
            $query->where('id_tipo_activo', $request->id_tipo_activo);
        }

        if ($request->filled('id_estatus')) {
            $query->where('id_estatus', $request->id_estatus);
        }

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

        // Validación actualizada con nuevos campos
        $request->validate([
            'serial' => 'required|unique:activo,serial|max:100',
            'tipo_equipo' => 'required|in:principal,secundario,componente',
            'marca_modelo' => 'required|max:200',
            'capacidad' => 'nullable|max:50',
            'id_estatus' => 'required|integer|min:1',
            'id_tipo_activo' => 'required|integer|min:1',
            'cantidad' => 'required|integer|min:1',
            'ubicacion' => 'nullable|max:100',
            'disponible_desde' => 'nullable|date',
            'fecha_adquisicion' => 'nullable|date',
            'valor_compra' => 'nullable|numeric|min:0',
            'detalles_tecnicos' => 'nullable|string',
            'observaciones' => 'nullable'
        ]);

        try {
            DB::beginTransaction();

            $cantidad = $request->cantidad;
            $serialBase = $request->serial;
            
            // Crear el activo principal
            $activo = Activo::create($request->all());
            
            // Si cantidad > 1, generar seriales individuales
            if ($cantidad > 1) {
                for ($i = 1; $i <= $cantidad; $i++) {
                    $serialGenerado = $serialBase . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
                    Seriales_activo::create([ // ← Nombre corregido
                        'activo_id' => $activo->id,
                        'serial' => $serialGenerado,
                        'estado' => 'disponible'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => $cantidad > 1 
                    ? "Activo creado exitosamente con {$cantidad} seriales generados" 
                    : 'Activo creado exitosamente',
                'activo' => $activo
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

        $activo = Activo::with(['estatus', 'tipoActivo', 'seriales'])->findOrFail($id);
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

        $request->validate([
            'serial' => 'required|max:100|unique:activo,serial,' . $id,
            'tipo_equipo' => 'required|in:principal,secundario,componente',
            'marca_modelo' => 'required|max:200',
            'capacidad' => 'nullable|max:50',
            'id_estatus' => 'required|integer|min:1',
            'id_tipo_activo' => 'required|integer|min:1',
            'cantidad' => 'required|integer|min:0',
            'ubicacion' => 'nullable|max:100',
            'disponible_desde' => 'nullable|date',
            'fecha_adquisicion' => 'nullable|date',
            'valor_compra' => 'nullable|numeric|min:0',
            'detalles_tecnicos' => 'nullable|string',
            'observaciones' => 'nullable'
        ]);

        try {
            DB::beginTransaction();

            $activo->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Activo actualizado exitosamente',
                'activo' => $activo
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
            
            // Eliminar seriales asociados primero
            $activo->seriales()->delete();
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

    // ========== NUEVOS MÉTODOS AGREGADOS ==========

    /**
     * Obtener componentes por categoría con sus seriales
     */
    public function componentesPorCategoria($categoriaId)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $componentes = Activo::where('id_tipo_activo', $categoriaId)
                ->with(['seriales', 'estatus'])
                ->orderBy('marca_modelo')
                ->get()
                ->map(function($componente) {
                    if ($componente->seriales && $componente->seriales->count() > 0) {
                        $componente->seriales_list = $componente->seriales;
                    }
                    return $componente;
                });
            
            return response()->json($componentes);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar componentes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener activos disponibles para selección
     */
    public function getActivosDisponibles()
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $activos = Activo::where('id_estatus', 1)
                ->orderBy('marca_modelo')
                ->get(['id', 'serial', 'marca_modelo', 'capacidad']);
            
            return response()->json($activos);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar activos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de inventario
     */
    public function getEstadisticas()
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $estadisticas = [
                'total_activos' => Activo::count(),
                'por_tipo' => Activo::select('tipo_equipo', DB::raw('count(*) as total'))
                    ->groupBy('tipo_equipo')
                    ->get(),
                'por_categoria' => TipoActivo::withCount('activos')->get(),
                'valor_total' => Activo::sum('valor_compra'),
                'activos_disponibles' => Activo::where('id_estatus', 1)->count(),
                'componentes_registrados' => Activo::where('tipo_equipo', 'componente')->count(),
                'discos_duros' => Activo::where('capacidad', 'like', '%TB%')
                    ->orWhere('capacidad', 'like', '%GB%')
                    ->count()
            ];
            
            return response()->json($estadisticas);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener seriales de un activo específico
     */
    public function getSeriales($id)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $activo = Activo::findOrFail($id);
            $seriales = $activo->seriales;
            
            return response()->json([
                'success' => true,
                'activo' => $activo,
                'seriales' => $seriales,
                'tiene_seriales_multiples' => $seriales->count() > 0
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar seriales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de inventario por capacidad
     */
    public function reportePorCapacidad()
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $discos = Activo::whereNotNull('capacidad')
                ->where('capacidad', '!=', '')
                ->select('capacidad', DB::raw('count(*) as total'), DB::raw('sum(cantidad) as unidades'))
                ->groupBy('capacidad')
                ->orderBy('capacidad')
                ->get();
            
            return response()->json($discos);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de inventario por categoría
     */
    public function reportePorCategoria()
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $reporte = TipoActivo::withCount('activos')
                ->withSum('activos', 'valor_compra')
                ->get();
            
            return response()->json($reporte);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }
}