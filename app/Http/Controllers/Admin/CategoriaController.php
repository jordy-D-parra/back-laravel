<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Categoria::query();

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', "%{$request->buscar}%");
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        $categorias = $query->withCount('modelos')->orderBy('nombre')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $categorias]);
        }

        return view('admin.categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias',
            'descripcion' => 'nullable|string'
        ]);

        try {
            $categoria = Categoria::create($validated);
            return response()->json(['success' => true, 'message' => 'Categoría creada exitosamente', 'data' => $categoria]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear la categoría'], 500);
        }
    }

    public function show($id)
    {
        $categoria = Categoria::with(['modelos' => function($q) {
            $q->with('marca');
        }])->withCount('modelos')->findOrFail($id);

        return response()->json(['success' => true, 'data' => $categoria]);
    }

    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias,nombre,' . $id,
            'descripcion' => 'nullable|string'
        ]);

        try {
            $categoria->update($validated);
            return response()->json(['success' => true, 'message' => 'Categoría actualizada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar'], 500);
        }
    }

    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);

        if ($categoria->modelos()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar la categoría porque tiene modelos asociados'], 400);
        }

        try {
            $categoria->delete();
            return response()->json(['success' => true, 'message' => 'Categoría eliminada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar'], 500);
        }
    }

    public function toggleStatus($id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->activo = !$categoria->activo;
        $categoria->save();

        return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente']);
    }

    public function listForSelect()
    {
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
        return response()->json(['success' => true, 'data' => $categorias]);
    }
}
