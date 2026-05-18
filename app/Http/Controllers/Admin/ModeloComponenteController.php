<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Modelo;
use App\Models\ModeloComponente;
use Illuminate\Http\Request;

class ModeloComponenteController extends Controller
{
    public function index($modeloId)
    {
        try {
            $componentes = ModeloComponente::where('modelo_id', $modeloId)
                ->orderBy('tipo')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $componentes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function store(Request $request, $modeloId)
    {
        try {
            $validated = $request->validate([
                'tipo' => 'required|string|max:50',
                'descripcion' => 'required|string|max:200',
                'capacidad' => 'nullable|string|max:50',
                'cantidad' => 'nullable|integer|min:1',
            ]);

            $validated['modelo_id'] = $modeloId;
            $validated['cantidad'] = $validated['cantidad'] ?? 1;
            $validated['requerido'] = true;

            $componente = ModeloComponente::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Componente agregado exitosamente',
                'data' => $componente
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function show($modeloId, $id)
    {
        $componente = ModeloComponente::where('modelo_id', $modeloId)->findOrFail($id);
        return response()->json(['success' => true, 'data' => $componente]);
    }

    public function update(Request $request, $modeloId, $id)
    {
        $componente = ModeloComponente::where('modelo_id', $modeloId)->findOrFail($id);

        $validated = $request->validate([
            'tipo' => 'required|string|max:50',
            'descripcion' => 'required|string|max:200',
            'capacidad' => 'nullable|string|max:50',
            'cantidad' => 'nullable|integer|min:1',
        ]);

        $componente->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Componente actualizado',
            'data' => $componente
        ]);
    }

    public function destroy($modeloId, $id)
    {
        try {
            $componente = ModeloComponente::where('modelo_id', $modeloId)->findOrFail($id);
            $componente->delete();
            return response()->json(['success' => true, 'message' => 'Componente eliminado']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
