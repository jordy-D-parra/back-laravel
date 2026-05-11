<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\Estatus;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Modelo;
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

        $tiposActivo = Categoria::where('activo', true)->orderBy('nombre')->get();
        $estatusList = Estatus::all();
        $marcas = Marca::where('activo', true)->orderBy('nombre')->get();

        return view('inventario.index', compact('tiposActivo', 'estatusList', 'marcas'));
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

        $query = Activo::with(['estatus', 'categoria', 'marca', 'modelo']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('serial', 'ilike', "%{$search}%")
                  ->orWhereHas('marca', function($q2) use ($search) {
                      $q2->where('nombre', 'ilike', "%{$search}%");
                  })
                  ->orWhereHas('modelo', function($q2) use ($search) {
                      $q2->where('nombre', 'ilike', "%{$search}%");
                  })
                  ->orWhere('ubicacion', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        if ($request->filled('id_estatus')) {
            $query->where('id_estatus', $request->id_estatus);
        }

        // Ordenar por fecha de creación descendente
        $activos = $query->orderBy('id', 'desc')->paginate(15);

        // Transformar los datos para la vista
        $activos->getCollection()->transform(function($activo) {
            $activo->marca_modelo = $activo->marca && $activo->modelo
                ? $activo->marca->nombre . ' ' . $activo->modelo->nombre
                : ($activo->marca_modelo ?? '-');
            return $activo;
        });

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

        // Validación
        $request->validate([
            'serial' => 'required|unique:activo,serial|max:100',
            'id_categoria' => 'required|integer|min:1',
            'id_marca' => 'required|integer|min:1',
            'id_modelo' => 'required|integer|min:1',
            'id_estatus' => 'required|integer|min:1',
            'ubicacion' => 'nullable|max:100',
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
                'activo' => $activo->load(['estatus', 'categoria', 'marca', 'modelo'])
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

        $activo = Activo::with(['estatus', 'categoria', 'marca', 'modelo'])->findOrFail($id);

        // Decodificar especificaciones técnicas si existen
        if ($activo->especificaciones_tecnicas && is_string($activo->especificaciones_tecnicas)) {
            $activo->especificaciones_tecnicas = json_decode($activo->especificaciones_tecnicas, true);
        }

        // Agregar campo compuesto para la vista
        $activo->marca_modelo = $activo->marca && $activo->modelo
            ? $activo->marca->nombre . ' ' . $activo->modelo->nombre
            : $activo->marca_modelo;

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

        // Validación
        $request->validate([
            'serial' => 'required|max:100|unique:activo,serial,' . $id,
            'id_categoria' => 'required|integer|min:1',
            'id_marca' => 'required|integer|min:1',
            'id_modelo' => 'required|integer|min:1',
            'id_estatus' => 'required|integer|min:1',
            'ubicacion' => 'nullable|max:100',
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
                'activo' => $activo->load(['estatus', 'categoria', 'marca', 'modelo'])
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

        $activos = Activo::with(['estatus', 'categoria'])
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
