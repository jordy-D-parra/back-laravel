<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Responsable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstitucionController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-instituciones')) {
            abort(403, 'No tienes permiso para ver instituciones');
        }

        $query = Institucion::withCount(['departamentos', 'responsables'])
            ->with(['responsablesDirectos']);

        if ($request->wantsJson() || $request->has('todos')) {
            $instituciones = $query->orderBy('nombre')->get();

            $instituciones->each(function($institucion) {
                $responsable = $institucion->responsablesDirectos->first();
                $institucion->representante_nombre = $responsable ? $responsable->nombre : null;
                $institucion->representante_documento = $responsable ? $responsable->documento : null;
                $institucion->representante_telefono = $responsable ? $responsable->telefono : null;
                $institucion->representante_email = $responsable ? $responsable->email : null;
                $institucion->representante_cargo = $responsable ? $responsable->cargo : null;
                $institucion->representante_direccion = $responsable ? $responsable->direccion : null;
            });

            return response()->json(['success' => true, 'data' => $instituciones]);
        }

        $instituciones = $query
            ->when($request->buscar, function($query, $buscar) {
                return $query->where(function($q) use ($buscar) {
                    $q->where('nombre', 'ILIKE', "%{$buscar}%")
                      ->orWhere('ubicacion', 'ILIKE', "%{$buscar}%");
                });
            })
            ->when($request->estado, function($query, $estado) {
                return $query->where('activo', $estado === 'activo');
            })
            ->orderBy('nombre')
            ->get();

        return view('admin.entidades.index', compact('instituciones'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-institucion')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para crear instituciones'], 403);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:200|unique:instituciones,nombre',
            'ubicacion' => 'required|string|max:200',
            'informacion' => 'required|string|max:500',
            'representante_nombre' => 'required|string|max:150',
            'representante_documento' => [
                'required',
                'string',
                'max:50',
                Rule::unique('responsables', 'documento')->where(function ($query) {
                    return $query->whereNotNull('documento')->where('documento', '!=', '');
                })
            ],
            'representante_telefono' => 'required|string|max:20',
            'representante_email' => 'nullable|email|max:100',
            'representante_cargo' => 'required|string|max:100',
            'representante_direccion' => 'nullable|string|max:300',
        ]);

        $institucion = Institucion::create([
            'nombre' => $validated['nombre'],
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
            'institucion_id' => $institucion->id,
            'departamento_id' => null,
            'activo' => true,
        ]);

        $institucion->loadCount(['departamentos', 'responsables']);

        return response()->json([
            'success' => true,
            'message' => 'Institución creada exitosamente',
            'data' => $institucion
        ]);
    }

    public function show(Institucion $institucione)
    {
        if (!auth()->user()->hasPermission('ver-instituciones')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para ver instituciones'], 403);
        }

        $institucione->loadCount(['departamentos', 'responsables']);
        $institucione->load([
            'departamentos' => function($q) {
                $q->select('id', 'nombre', 'institucion_id', 'activo')
                  ->withCount('responsables')
                  ->orderBy('nombre');
            },
            'responsables' => function($q) {
                $q->select('id', 'nombre', 'documento', 'telefono', 'email', 'cargo', 'direccion', 'institucion_id', 'departamento_id', 'activo')
                  ->with('departamento:id,nombre')
                  ->orderBy('nombre');
            }
        ]);

        $representante = $institucione->responsablesDirectos()->first();

        $data = $institucione->toArray();
        $data['representante_nombre'] = $representante ? $representante->nombre : null;
        $data['representante_documento'] = $representante ? $representante->documento : null;
        $data['representante_telefono'] = $representante ? $representante->telefono : null;
        $data['representante_email'] = $representante ? $representante->email : null;
        $data['representante_cargo'] = $representante ? $representante->cargo : null;
        $data['representante_direccion'] = $representante ? $representante->direccion : null;

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, Institucion $institucione)
    {
        if (!auth()->user()->hasPermission('editar-institucion')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para editar instituciones'], 403);
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:200', Rule::unique('instituciones', 'nombre')->ignore($institucione->id)],
            'ubicacion' => 'required|string|max:200',
            'informacion' => 'required|string|max:500',
            'representante_nombre' => 'required|string|max:150',
            'representante_documento' => [
                'required',
                'string',
                'max:50',
                Rule::unique('responsables', 'documento')
                    ->where(function ($query) {
                        return $query->whereNotNull('documento')->where('documento', '!=', '');
                    })
                    ->ignore($institucione->responsablesDirectos()->first()?->id)
            ],
            'representante_telefono' => 'required|string|max:20',
            'representante_email' => 'nullable|email|max:100',
            'representante_cargo' => 'required|string|max:100',
            'representante_direccion' => 'nullable|string|max:300',
        ]);

        $institucione->update([
            'nombre' => $validated['nombre'],
            'ubicacion' => $validated['ubicacion'],
            'informacion' => $validated['informacion'],
        ]);

        $responsable = Responsable::where('institucion_id', $institucione->id)
            ->whereNull('departamento_id')
            ->first();

        if ($responsable) {
            $responsable->update([
                'nombre' => $validated['representante_nombre'],
                'documento' => $validated['representante_documento'],
                'telefono' => $validated['representante_telefono'],
                'email' => $validated['representante_email'] ?? null,
                'cargo' => $validated['representante_cargo'],
                'direccion' => $validated['representante_direccion'] ?? null,
            ]);
        } else {
            Responsable::create([
                'nombre' => $validated['representante_nombre'],
                'documento' => $validated['representante_documento'],
                'telefono' => $validated['representante_telefono'],
                'email' => $validated['representante_email'] ?? null,
                'cargo' => $validated['representante_cargo'],
                'direccion' => $validated['representante_direccion'] ?? null,
                'institucion_id' => $institucione->id,
                'departamento_id' => null,
                'activo' => true,
            ]);
        }

        $institucione->loadCount(['departamentos', 'responsables']);

        return response()->json([
            'success' => true,
            'message' => 'Institución actualizada exitosamente',
            'data' => $institucione
        ]);
    }

    public function destroy(Institucion $institucione)
    {
        if (!auth()->user()->hasPermission('eliminar-institucion')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar instituciones'], 403);
        }

        $institucione->responsables()->delete();

        foreach ($institucione->departamentos as $departamento) {
            $departamento->responsables()->delete();
            $departamento->delete();
        }

        $institucione->delete();

        return response()->json([
            'success' => true,
            'message' => 'Institución eliminada junto con sus departamentos y responsables'
        ]);
    }

    public function toggleStatus(Institucion $institucione)
    {
        if (!auth()->user()->hasPermission('editar-institucion')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para cambiar el estado de instituciones'], 403);
        }

        $institucione->update(['activo' => !$institucione->activo]);
        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'activo' => $institucione->activo
        ]);
    }
}
