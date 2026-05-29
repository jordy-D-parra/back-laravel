<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Responsable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartamentoController extends Controller
{
    public function index(Request $request)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('ver-departamentos')) {
            abort(403, 'No tienes permiso para ver departamentos');
        }

        $departamentos = Departamento::with('institucion:id,nombre')
            ->withCount('responsables')
            ->when($request->buscar, function($query, $buscar) {
                return $query->where(function($q) use ($buscar) {
                    $q->where('nombre', 'ILIKE', "%{$buscar}%")
                      ->orWhere('representante', 'ILIKE', "%{$buscar}%");
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

        return response()->json($departamentos);
    }

    public function store(Request $request)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('crear-departamento')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para crear departamentos'], 403);
        }

        $validated = $request->validate([
            'institucion_id' => 'nullable|exists:instituciones,id',
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('departamentos', 'nombre')->where(function($query) use ($request) {
                    if ($request->institucion_id) {
                        return $query->where('institucion_id', $request->institucion_id);
                    }
                    return $query->whereNull('institucion_id');
                })
            ],
            'ubicacion' => 'required|string|max:200',
            'informacion' => 'required|string|max:500',
            'representante_nombre' => 'required|string|max:150',
            'representante_documento' => 'required|string|max:50',
            'representante_telefono' => 'required|string|max:20',
            'representante_email' => 'nullable|email|max:100',
            'representante_cargo' => 'required|string|max:100',
            'representante_direccion' => 'nullable|string|max:300',
        ]);

        $departamento = Departamento::create([
            'institucion_id' => $validated['institucion_id'] ?? null,
            'nombre' => $validated['nombre'],
            'representante' => $validated['representante_nombre'],
            'ubicacion' => $validated['ubicacion'],
            'informacion' => $validated['informacion'],
            'activo' => true,
        ]);

        Responsable::create([
            'nombre' => $validated['representante_nombre'],
            'documento' => $validated['representante_documento'],
            'telefono' => $validated['representante_telefono'],
            'email' => $validated['representante_email'] ?? null,
            'cargo' => $validated['representante_cargo'],
            'direccion' => $validated['representante_direccion'] ?? null,
            'institucion_id' => $departamento->institucion_id,
            'departamento_id' => $departamento->id,
            'activo' => true,
        ]);

        $departamento->load('institucion:id,nombre');
        $departamento->loadCount('responsables');

        return response()->json([
            'success' => true,
            'message' => 'Departamento creado exitosamente',
            'data' => $departamento
        ]);
    }

    public function show(Departamento $departamento)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('ver-departamentos')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para ver departamentos'], 403);
        }

        $departamento->loadCount('responsables');
        $departamento->load([
            'institucion:id,nombre,representante',
            'responsables' => function($q) {
                $q->select('id', 'nombre', 'documento', 'telefono', 'email', 'cargo', 'direccion', 'institucion_id', 'departamento_id', 'activo')
                  ->orderBy('nombre');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $departamento
        ]);
    }

    public function update(Request $request, Departamento $departamento)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('editar-departamento')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para editar departamentos'], 403);
        }

        $validated = $request->validate([
            'institucion_id' => 'nullable|exists:instituciones,id',
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('departamentos', 'nombre')
                    ->where(function($query) use ($request) {
                        if ($request->institucion_id) {
                            return $query->where('institucion_id', $request->institucion_id);
                        }
                        return $query->whereNull('institucion_id');
                    })
                    ->ignore($departamento->id)
            ],
            'ubicacion' => 'required|string|max:200',
            'informacion' => 'required|string|max:500',
            'representante_nombre' => 'required|string|max:150',
            'representante_documento' => 'required|string|max:50',
            'representante_telefono' => 'required|string|max:20',
            'representante_email' => 'nullable|email|max:100',
            'representante_cargo' => 'required|string|max:100',
            'representante_direccion' => 'nullable|string|max:300',
        ]);

        $departamento->update([
            'institucion_id' => $validated['institucion_id'] ?? null,
            'nombre' => $validated['nombre'],
            'representante' => $validated['representante_nombre'],
            'ubicacion' => $validated['ubicacion'],
            'informacion' => $validated['informacion'],
        ]);

        $responsable = Responsable::where('departamento_id', $departamento->id)
            ->where('cargo', $validated['representante_cargo'])
            ->first();

        $dataResponsable = [
            'nombre' => $validated['representante_nombre'],
            'documento' => $validated['representante_documento'],
            'telefono' => $validated['representante_telefono'],
            'email' => $validated['representante_email'] ?? null,
            'cargo' => $validated['representante_cargo'],
            'direccion' => $validated['representante_direccion'] ?? null,
            'institucion_id' => $departamento->institucion_id,
            'departamento_id' => $departamento->id,
            'activo' => true,
        ];

        if ($responsable) {
            $responsable->update($dataResponsable);
        } else {
            Responsable::create($dataResponsable);
        }

        $departamento->load('institucion:id,nombre');
        $departamento->loadCount('responsables');

        return response()->json([
            'success' => true,
            'message' => 'Departamento actualizado exitosamente',
            'data' => $departamento
        ]);
    }

    public function destroy(Departamento $departamento)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('eliminar-departamento')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar departamentos'], 403);
        }

        Responsable::where('departamento_id', $departamento->id)
            ->where('cargo', 'Jefe de Departamento')
            ->delete();
        $departamento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Departamento eliminado exitosamente'
        ]);
    }

    public function toggleStatus(Departamento $departamento)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('editar-departamento')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para cambiar el estado'], 403);
        }

        $departamento->update(['activo' => !$departamento->activo]);
        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'activo' => $departamento->activo
        ]);
    }

    public function porInstitucion($institucionId)
    {
        $departamentos = Departamento::activos()
            ->where('institucion_id', $institucionId)
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $departamentos
        ]);
    }
}
