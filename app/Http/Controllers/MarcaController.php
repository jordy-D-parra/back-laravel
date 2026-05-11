<?php
// app/Http/Controllers/MarcaController.php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarcaController extends Controller
{
    public function index(Request $request)
    {
        $query = Marca::query();

        if ($request->filled('search')) {
            $query->where('nombre', 'ilike', "%{$request->search}%");
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === 'true');
        }

        $marcas = $query->orderBy('nombre')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($marcas);
        }

        return view('admin.marcas.index', compact('marcas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|unique:marcas|max:100',
            'descripcion' => 'nullable|string'
        ]);

        try {
            $marca = Marca::create($request->all());
            return response()->json(['success' => true, 'marca' => $marca, 'message' => 'Marca creada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear la marca'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $marca = Marca::findOrFail($id);

        $request->validate([
            'nombre' => 'required|unique:marcas,nombre,' . $id . '|max:100',
            'descripcion' => 'nullable|string'
        ]);

        try {
            $marca->update($request->all());
            return response()->json(['success' => true, 'marca' => $marca, 'message' => 'Marca actualizada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar la marca'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $marca = Marca::findOrFail($id);
            $marca->delete();
            return response()->json(['success' => true, 'message' => 'Marca eliminada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar la marca'], 500);
        }
    }
}
