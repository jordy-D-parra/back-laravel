<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Responsable;
use Illuminate\Http\Request;

class ResponsableController extends Controller
{
    public function index(Request $request)
    {
        $responsables = Responsable::with(['institucion:id,nombre', 'departamento:id,nombre'])
            ->when($request->buscar, function($query, $buscar) {
                return $query->where(function($q) use ($buscar) {
                    $q->where('nombre', 'ILIKE', "%{$buscar}%")
                      ->orWhere('documento', 'ILIKE', "%{$buscar}%")
                      ->orWhere('cargo', 'ILIKE', "%{$buscar}%");
                });
            })
            ->when($request->institucion_id, function($query, $institucionId) {
                return $query->where('institucion_id', $institucionId);
            })
            ->when($request->estado, function($query, $estado) {
                return $query->where('activo', $estado === 'activo');
            })
            ->orderBy('nombre')
            ->paginate(10);

        return response()->json($responsables);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'documento' => 'required|string|max:50',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'cargo' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:300',
            'institucion_id' => 'required|exists:instituciones,id',
            'departamento_id' => 'nullable|exists:departamentos,id',
        ]);

        $validated['activo'] = true;
        $responsable = Responsable::create($validated);
        $responsable->load(['institucion:id,nombre', 'departamento:id,nombre']);

        return response()->json([
            'success' => true,
            'message' => 'Responsable creado exitosamente',
            'data' => $responsable
        ]);
    }

    public function show(Responsable $responsable)
    {
        $responsable->load(['institucion:id,nombre', 'departamento:id,nombre']);
        return response()->json(['success' => true, 'data' => $responsable]);
    }

    public function update(Request $request, Responsable $responsable)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'documento' => 'required|string|max:50',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'cargo' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:300',
            'institucion_id' => 'required|exists:instituciones,id',
            'departamento_id' => 'nullable|exists:departamentos,id',
        ]);

        $responsable->update($validated);
        $responsable->load(['institucion:id,nombre', 'departamento:id,nombre']);

        return response()->json([
            'success' => true,
            'message' => 'Responsable actualizado exitosamente',
            'data' => $responsable
        ]);
    }

    public function destroy(Responsable $responsable)
    {
        $responsable->delete();
        return response()->json(['success' => true, 'message' => 'Responsable eliminado exitosamente']);
    }

    public function toggleStatus(Responsable $responsable)
    {
        $responsable->update(['activo' => !$responsable->activo]);
        return response()->json(['success' => true, 'message' => 'Estado actualizado', 'activo' => $responsable->activo]);
    }
}
