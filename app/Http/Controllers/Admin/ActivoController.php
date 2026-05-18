<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Estatus;
use App\Models\Modelo;
use App\Models\Institucion;
use App\Models\Departamento;
use App\Models\Responsable;
use Illuminate\Http\Request;

class ActivoController extends Controller
{
    /**
     * Listado de activos (API).
     */
    public function index(Request $request)
    {
        try {
            $query = Activo::with(['modelo.marca', 'modelo.categoria', 'estatus', 'institucion', 'responsable']);

            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->where('serial', 'like', "%{$buscar}%")
                      ->orWhere('ubicacion', 'like', "%{$buscar}%")
                      ->orWhere('agrupacion', 'like', "%{$buscar}%")
                      ->orWhereHas('modelo', fn($q2) => $q2->where('nombre', 'like', "%{$buscar}%"))
                      ->orWhereHas('modelo.marca', fn($q2) => $q2->where('nombre', 'like', "%{$buscar}%"))
                      ->orWhereHas('institucion', fn($q2) => $q2->where('nombre', 'like', "%{$buscar}%"));
                });
            }

            if ($request->filled('estatus_id')) {
                $query->where('id_estatus', $request->estatus_id);
            }

            if ($request->filled('institucion_id')) {
                $query->where('institucion_id', $request->institucion_id);
            }

            if ($request->filled('modelo_id')) {
                $query->where('modelo_id', $request->modelo_id);
            }

            if ($request->filled('agrupacion')) {
                $query->where('agrupacion', $request->agrupacion);
            }

            $activos = $query->orderBy('created_at', 'desc')->get();

            return response()->json(['success' => true, 'data' => $activos]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar activos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Crear un nuevo activo.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'serial' => 'required|string|max:100|unique:activos',
                'modelo_id' => 'required|exists:modelos,id',
                'id_estatus' => 'required|exists:estatus,id',
                'institucion_id' => 'required|exists:instituciones,id',
                'departamento_id' => 'nullable|exists:departamentos,id',
                'responsable_id' => 'required|exists:responsables,id',
                'ubicacion' => 'nullable|string|max:100',
                'fecha_adquisicion' => 'nullable|date',
                'fecha_fin_garantia' => 'nullable|date',
                'vida_util_anos' => 'nullable|integer|min:1',
                'especificaciones_tecnicas' => 'nullable|json',
                'agrupacion' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string',
            ]);

            $activo = Activo::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Activo creado exitosamente',
                'data' => $activo->load(['modelo.marca', 'modelo.categoria', 'estatus', 'institucion', 'responsable']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear activo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mostrar detalle de un activo.
     */
    public function show($id)
    {
        try {
            $activo = Activo::with([
                'modelo.marca',
                'modelo.categoria',
                'modelo.modeloComponentes',
                'estatus',
                'institucion',
                'departamento',
                'responsable',
                'componentes',
            ])->findOrFail($id);

            return response()->json(['success' => true, 'data' => $activo]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Activo no encontrado'], 404);
        }
    }

    /**
     * Actualizar un activo.
     */
    public function update(Request $request, $id)
    {
        try {
            $activo = Activo::findOrFail($id);

            $validated = $request->validate([
                'serial' => 'required|string|max:100|unique:activos,serial,' . $id,
                'modelo_id' => 'required|exists:modelos,id',
                'id_estatus' => 'required|exists:estatus,id',
                'institucion_id' => 'required|exists:instituciones,id',
                'departamento_id' => 'nullable|exists:departamentos,id',
                'responsable_id' => 'required|exists:responsables,id',
                'ubicacion' => 'nullable|string|max:100',
                'fecha_adquisicion' => 'nullable|date',
                'fecha_fin_garantia' => 'nullable|date',
                'vida_util_anos' => 'nullable|integer|min:1',
                'especificaciones_tecnicas' => 'nullable|json',
                'agrupacion' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string',
            ]);

            $activo->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Activo actualizado exitosamente',
                'data' => $activo->fresh(['modelo.marca', 'modelo.categoria', 'estatus', 'institucion', 'responsable']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar un activo.
     */
    public function destroy($id)
    {
        try {
            $activo = Activo::findOrFail($id);

            // Desvincular componentes instalados
            $activo->componentes()->update(['activo_id' => null, 'estado' => 'en_bodega']);

            $activo->delete();

            return response()->json(['success' => true, 'message' => 'Activo eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cambiar estatus del activo.
     */
    public function toggleStatus($id)
    {
        try {
            $activo = Activo::with('estatus')->findOrFail($id);

            if ($activo->estatus->es_terminal) {
                return response()->json(['success' => false, 'message' => 'No se puede cambiar un estado terminal'], 400);
            }

            // Buscar el estatus "Disponible" o "Prestado" según corresponda
            $nuevoEstatusDescripcion = $activo->estatus->descripcion === 'Disponible' ? 'Prestado' : 'Disponible';
            $nuevoEstatus = Estatus::where('descripcion', $nuevoEstatusDescripcion)->first();

            if ($nuevoEstatus) {
                $activo->update(['id_estatus' => $nuevoEstatus->id]);
            }

            return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cambiar estado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener activos por modelo (para selects).
     */
    public function porModelo($modeloId)
    {
        try {
            $activos = Activo::where('modelo_id', $modeloId)
                             ->with('estatus')
                             ->orderBy('serial')
                             ->get(['id', 'serial', 'id_estatus']);

            return response()->json(['success' => true, 'data' => $activos]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar activos'], 500);
        }
    }
}
