<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Institucion;
use App\Models\Responsable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartamentoController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-departamentos')) {
            abort(403, 'No tienes permiso para ver departamentos');
        }

        $departamentos = Departamento::with('institucion:id,nombre')
            ->with(['responsables'])
            ->withCount('responsables')
            ->when($request->buscar, function($query, $buscar) {
                return $query->where(function($q) use ($buscar) {
                    $q->where('nombre', 'ILIKE', "%{$buscar}%");
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

        $departamentos->getCollection()->transform(function($departamento) {
            $responsable = $departamento->responsables->first();
            $departamento->representante_nombre = $responsable ? $responsable->nombre : null;
            $departamento->representante_documento = $responsable ? $responsable->documento : null;
            $departamento->representante_telefono = $responsable ? $responsable->telefono : null;
            $departamento->representante_email = $responsable ? $responsable->email : null;
            $departamento->representante_cargo = $responsable ? $responsable->cargo : null;
            $departamento->representante_direccion = $responsable ? $responsable->direccion : null;
            return $departamento;
        });

        return response()->json($departamentos);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-departamento')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para crear departamentos'], 403);
        }

        $usarRepresentanteInstitucion = $request->boolean('usar_representante_institucion');
        $tieneInstitucion = !empty($request->institucion_id);

        $rules = [
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
            'usar_representante_institucion' => 'nullable|boolean',
        ];

        if (!$usarRepresentanteInstitucion) {
            $rules['representante_nombre'] = 'required|string|max:150';
            $rules['representante_documento'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('responsables', 'documento')->where(function ($query) {
                    return $query->whereNotNull('documento')->where('documento', '!=', '');
                })
            ];
            $rules['representante_telefono'] = 'required|string|max:20';
            $rules['representante_cargo'] = 'required|string|max:100';
        } else {
            $rules['representante_nombre'] = 'nullable|string|max:150';
            $rules['representante_documento'] = 'nullable|string|max:50';
            $rules['representante_telefono'] = 'nullable|string|max:20';
            $rules['representante_cargo'] = 'nullable|string|max:100';
        }

        $rules['representante_email'] = 'nullable|email|max:100';
        $rules['representante_direccion'] = 'nullable|string|max:300';

        $validated = $request->validate($rules);

        $departamento = Departamento::create([
            'institucion_id' => $validated['institucion_id'] ?? null,
            'nombre' => $validated['nombre'],
            'ubicacion' => $validated['ubicacion'],
            'informacion' => $validated['informacion'],
            'activo' => true,
        ]);

        if ($tieneInstitucion && $usarRepresentanteInstitucion) {
            $responsableInstitucion = Responsable::where('institucion_id', $request->institucion_id)
                ->whereNull('departamento_id')
                ->first();

            if ($responsableInstitucion) {
                $responsableInstitucion->update([
                    'departamento_id' => $departamento->id,
                ]);
            } else {
                Responsable::create([
                    'nombre' => $validated['representante_nombre'] ?? '',
                    'documento' => $validated['representante_documento'] ?? '',
                    'telefono' => $validated['representante_telefono'] ?? '',
                    'email' => $validated['representante_email'] ?? '',
                    'cargo' => $validated['representante_cargo'] ?? 'Jefe de Departamento',
                    'direccion' => $validated['representante_direccion'] ?? '',
                    'institucion_id' => $departamento->institucion_id,
                    'departamento_id' => $departamento->id,
                    'activo' => true,
                ]);
            }
        } else {
            if (empty($validated['representante_nombre'] ?? '')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe especificar un nombre para el responsable del departamento'
                ], 422);
            }

            Responsable::create([
                'nombre' => $validated['representante_nombre'],
                'documento' => $validated['representante_documento'] ?? '',
                'telefono' => $validated['representante_telefono'] ?? '',
                'email' => $validated['representante_email'] ?? '',
                'cargo' => $validated['representante_cargo'] ?? 'Jefe de Departamento',
                'direccion' => $validated['representante_direccion'] ?? '',
                'institucion_id' => $departamento->institucion_id,
                'departamento_id' => $departamento->id,
                'activo' => true,
            ]);
        }

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
        if (!auth()->user()->hasPermission('ver-departamentos')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para ver departamentos'], 403);
        }

        $departamento->loadCount('responsables');
        $departamento->load([
            'institucion:id,nombre',
            'responsables' => function($q) {
                $q->select('id', 'nombre', 'documento', 'telefono', 'email', 'cargo', 'direccion', 'institucion_id', 'departamento_id', 'activo')
                  ->orderBy('nombre');
            }
        ]);

        $responsable = $departamento->responsables->first();
        $data = $departamento->toArray();
        $data['representante_nombre'] = $responsable ? $responsable->nombre : null;
        $data['representante_documento'] = $responsable ? $responsable->documento : null;
        $data['representante_telefono'] = $responsable ? $responsable->telefono : null;
        $data['representante_email'] = $responsable ? $responsable->email : null;
        $data['representante_cargo'] = $responsable ? $responsable->cargo : null;
        $data['representante_direccion'] = $responsable ? $responsable->direccion : null;

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, Departamento $departamento)
    {
        if (!auth()->user()->hasPermission('editar-departamento')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para editar departamentos'], 403);
        }

        $usarRepresentanteInstitucion = $request->boolean('usar_representante_institucion');
        $tieneInstitucion = !empty($request->institucion_id);
        $responsableActual = Responsable::where('departamento_id', $departamento->id)->first();

        $rules = [
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
            'usar_representante_institucion' => 'nullable|boolean',
        ];

        if (!$usarRepresentanteInstitucion) {
            $rules['representante_nombre'] = 'required|string|max:150';
            $rules['representante_documento'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('responsables', 'documento')
                    ->where(function ($query) {
                        return $query->whereNotNull('documento')->where('documento', '!=', '');
                    })
                    ->ignore($responsableActual?->id)
            ];
            $rules['representante_telefono'] = 'required|string|max:20';
            $rules['representante_cargo'] = 'required|string|max:100';
        } else {
            $rules['representante_nombre'] = 'nullable|string|max:150';
            $rules['representante_documento'] = 'nullable|string|max:50';
            $rules['representante_telefono'] = 'nullable|string|max:20';
            $rules['representante_cargo'] = 'nullable|string|max:100';
        }

        $rules['representante_email'] = 'nullable|email|max:100';
        $rules['representante_direccion'] = 'nullable|string|max:300';

        $validated = $request->validate($rules);

        $departamento->update([
            'institucion_id' => $validated['institucion_id'] ?? null,
            'nombre' => $validated['nombre'],
            'ubicacion' => $validated['ubicacion'],
            'informacion' => $validated['informacion'],
        ]);

        if ($tieneInstitucion && $usarRepresentanteInstitucion) {
            $responsableInstitucion = Responsable::where('institucion_id', $request->institucion_id)
                ->whereNull('departamento_id')
                ->first();

            if ($responsableInstitucion) {
                if ($responsableActual && $responsableActual->id !== $responsableInstitucion->id) {
                    $responsableActual->delete();
                }
                $responsableInstitucion->update([
                    'departamento_id' => $departamento->id,
                    'cargo' => $validated['representante_cargo'] ?? $responsableInstitucion->cargo,
                ]);
            } else {
                if ($responsableActual) {
                    $responsableActual->update([
                        'nombre' => $validated['representante_nombre'] ?? $responsableActual->nombre,
                        'documento' => $validated['representante_documento'] ?? $responsableActual->documento,
                        'telefono' => $validated['representante_telefono'] ?? $responsableActual->telefono,
                        'email' => $validated['representante_email'] ?? $responsableActual->email,
                        'cargo' => $validated['representante_cargo'] ?? $responsableActual->cargo,
                        'direccion' => $validated['representante_direccion'] ?? $responsableActual->direccion,
                        'institucion_id' => $departamento->institucion_id,
                    ]);
                } else {
                    Responsable::create([
                        'nombre' => $validated['representante_nombre'] ?? '',
                        'documento' => $validated['representante_documento'] ?? '',
                        'telefono' => $validated['representante_telefono'] ?? '',
                        'email' => $validated['representante_email'] ?? '',
                        'cargo' => $validated['representante_cargo'] ?? 'Jefe de Departamento',
                        'direccion' => $validated['representante_direccion'] ?? '',
                        'institucion_id' => $departamento->institucion_id,
                        'departamento_id' => $departamento->id,
                        'activo' => true,
                    ]);
                }
            }
        } else {
            if ($responsableActual) {
                $responsableActual->update([
                    'nombre' => $validated['representante_nombre'] ?? $responsableActual->nombre,
                    'documento' => $validated['representante_documento'] ?? $responsableActual->documento,
                    'telefono' => $validated['representante_telefono'] ?? $responsableActual->telefono,
                    'email' => $validated['representante_email'] ?? $responsableActual->email,
                    'cargo' => $validated['representante_cargo'] ?? $responsableActual->cargo,
                    'direccion' => $validated['representante_direccion'] ?? $responsableActual->direccion,
                    'institucion_id' => $departamento->institucion_id,
                ]);
            } else {
                Responsable::create([
                    'nombre' => $validated['representante_nombre'] ?? '',
                    'documento' => $validated['representante_documento'] ?? '',
                    'telefono' => $validated['representante_telefono'] ?? '',
                    'email' => $validated['representante_email'] ?? '',
                    'cargo' => $validated['representante_cargo'] ?? 'Jefe de Departamento',
                    'direccion' => $validated['representante_direccion'] ?? '',
                    'institucion_id' => $departamento->institucion_id,
                    'departamento_id' => $departamento->id,
                    'activo' => true,
                ]);
            }
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
        if (!auth()->user()->hasPermission('eliminar-departamento')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar departamentos'], 403);
        }

        try {
            \DB::beginTransaction();

            $responsable = Responsable::where('departamento_id', $departamento->id)->first();

            if ($responsable) {
                $tieneActivos = \App\Models\Activo::where('responsable_id', $responsable->id)->exists();

                if ($tieneActivos) {
                    $responsable->update(['departamento_id' => null]);
                } else {
                    $responsable->delete();
                }
            }

            $departamento->delete();

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Departamento eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(Departamento $departamento)
    {
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
