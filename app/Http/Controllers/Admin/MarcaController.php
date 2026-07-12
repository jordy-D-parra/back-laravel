<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarcaController extends Controller
{
    public function index(Request $request)
    {
        $query = Marca::query();

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', "%{$request->buscar}%");
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        $marcas = $query->withCount('modelos')->orderBy('nombre')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $marcas]);
        }

        $totalMarcas = Marca::count();
        $totalCategorias = \App\Models\Categoria::count();
        $totalModelos = \App\Models\Modelo::count();

        return view('admin.marcas.index', compact('marcas', 'totalMarcas', 'totalCategorias', 'totalModelos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:marcas',
            'descripcion' => 'nullable|string'
        ]);

        try {
            $marca = Marca::create($validated);
            return response()->json(['success' => true, 'message' => 'Marca creada exitosamente', 'data' => $marca]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear la marca: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $marca = Marca::with(['modelos' => function($q) {
            $q->with('categoria');
        }, 'modelos.categoria'])->withCount('modelos')->findOrFail($id);
        
        return response()->json(['success' => true, 'data' => $marca]);
    }

    public function update(Request $request, $id)
    {
        $marca = Marca::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:marcas,nombre,' . $id,
            'descripcion' => 'nullable|string'
        ]);

        try {
            $marca->update($validated);
            return response()->json(['success' => true, 'message' => 'Marca actualizada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar'], 500);
        }
    }

    public function destroy($id)
    {
        $marca = Marca::findOrFail($id);
        
        if ($marca->modelos()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar la marca porque tiene modelos asociados'], 400);
        }

        try {
            $marca->delete();
            return response()->json(['success' => true, 'message' => 'Marca eliminada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar'], 500);
        }
    }

    public function toggleStatus($id)
    {
        $marca = Marca::findOrFail($id);
        $marca->activo = !$marca->activo;
        $marca->save();

        return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente']);
    }
}