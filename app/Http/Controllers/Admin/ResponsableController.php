<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Responsable;
use Illuminate\Http\Request;

class ResponsableController extends Controller
{
    public function index(Request $request)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('ver-responsables')) {
            abort(403, 'No tienes permiso para ver responsables');
        }

        $query = Responsable::with(['institucion', 'departamento']);

        if ($request->filled('institucion_id')) {
            $query->where('institucion_id', $request->institucion_id);
        }

        if ($request->filled('buscar')) {
            $query->where('nombre', 'ILIKE', "%{$request->buscar}%");
        }

        $responsables = $query->orderBy('nombre')->get();

        if ($request->wantsJson() || $request->has('todos')) {
            return response()->json(['success' => true, 'data' => $responsables]);
        }

        return view('admin.entidades.index', compact('responsables'));
    }

    public function store(Request $request)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('crear-responsable')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para crear responsables'], 403);
        }

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
        // Verificar permiso
        if (!auth()->user()->hasPermission('ver-responsables')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para ver responsables'], 403);
        }

        $responsable->load(['institucion:id,nombre', 'departamento:id,nombre']);
        return response()->json(['success' => true, 'data' => $responsable]);
    }

    public function update(Request $request, Responsable $responsable)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('editar-responsable')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para editar responsables'], 403);
        }

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
        // Verificar permiso
        if (!auth()->user()->hasPermission('eliminar-responsable')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar responsables'], 403);
        }

        $responsable->delete();
        return response()->json(['success' => true, 'message' => 'Responsable eliminado exitosamente']);
    }

    public function toggleStatus(Responsable $responsable)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('editar-responsable')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para cambiar el estado'], 403);
        }

        $responsable->update(['activo' => !$responsable->activo]);
        return response()->json(['success' => true, 'message' => 'Estado actualizado', 'activo' => $responsable->activo]);
    }
}
