<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Modelo;
use App\Models\Marca;
use App\Models\Categoria;
use Illuminate\Http\Request;

class ModeloController extends Controller
{
    public function index(Request $request)
    {
        $query = Modelo::with(['marca', 'categoria']);

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhereHas('marca', fn($q) => $q->where('nombre', 'like', "%{$request->buscar}%"));
        }

        if ($request->filled('marca_id')) {
            $query->where('marca_id', $request->marca_id);
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        $modelos = $query->orderBy('nombre')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $modelos]);
        }

        $marcas = Marca::where('activo', true)->orderBy('nombre')->get();
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();

        return view('admin.modelos.index', compact('modelos', 'marcas', 'categorias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'marca_id' => 'required|exists:marcas,id',
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:100|unique:modelos,nombre,NULL,id,marca_id,' . $request->marca_id,
            'descripcion' => 'nullable|string',
            'especificaciones' => 'nullable|string'
        ]);

        try {
            $modelo = Modelo::create($validated);
            return response()->json(['success' => true, 'message' => 'Modelo creado exitosamente', 'data' => $modelo->load(['marca', 'categoria'])]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear el modelo: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $modelo = Modelo::with(['marca', 'categoria'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $modelo]);
    }

    public function update(Request $request, $id)
    {
        $modelo = Modelo::findOrFail($id);

        $validated = $request->validate([
            'marca_id' => 'required|exists:marcas,id',
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:100|unique:modelos,nombre,' . $id . ',id,marca_id,' . $request->marca_id,
            'descripcion' => 'nullable|string',
            'especificaciones' => 'nullable|string'
        ]);

        try {
            $modelo->update($validated);
            return response()->json(['success' => true, 'message' => 'Modelo actualizado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar'], 500);
        }
    }

    public function destroy($id)
    {
        $modelo = Modelo::findOrFail($id);

        try {
            $modelo->delete();
            return response()->json(['success' => true, 'message' => 'Modelo eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar'], 500);
        }
    }

    public function toggleStatus($id)
    {
        $modelo = Modelo::findOrFail($id);
        $modelo->activo = !$modelo->activo;
        $modelo->save();

        return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente']);
    }
}