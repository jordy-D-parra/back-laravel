<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Componente;
use App\Models\ModeloComponente;
use Illuminate\Http\Request;

class ComponenteController extends Controller
{
    /**
     * Listado de componentes físicos (API).
     */
    public function index(Request $request)
    {
        try {
            $query = Componente::with([
                'modeloComponente.modelo',
                'activo',
                'institucion',
                'responsable',
            ]);

            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->where('tipo', 'like', "%{$buscar}%")
                      ->orWhere('marca', 'like', "%{$buscar}%")
                      ->orWhere('serial', 'like', "%{$buscar}%")
                      ->orWhere('ubicacion', 'like', "%{$buscar}%");
                });
            }

            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('activo_id')) {
                $query->where('activo_id', $request->activo_id);
            }

            $componentes = $query->orderBy('created_at', 'desc')->get();

            return response()->json(['success' => true, 'data' => $componentes]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar componentes: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Crear un nuevo componente físico.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipo' => 'required|string|max:50',
                'modelo_componente_id' => 'nullable|exists:modelo_componente,id',
                'marca' => 'nullable|string|max:100',
                'modelo' => 'nullable|string|max:100',
                'serial' => 'nullable|string|max:100|unique:componentes',
                'capacidad' => 'nullable|string|max:50',
                'especificaciones' => 'nullable|json',
                'estado' => 'required|string|in:en_bodega,instalado,prestado,desechado,en_reparacion',
                'activo_id' => 'nullable|exists:activos,id',
                'institucion_id' => 'required|exists:instituciones,id',
                'departamento_id' => 'nullable|exists:departamentos,id',
                'responsable_id' => 'required|exists:responsables,id',
                'ubicacion' => 'nullable|string|max:100',
                'fecha_instalacion' => 'nullable|date',
                'fecha_retiro' => 'nullable|date',
                'observaciones' => 'nullable|string',
            ]);

            // Si se seleccionó un modelo_componente_id, autocompletar tipo y capacidad
            if ($request->filled('modelo_componente_id')) {
                $tipoComponente = ModeloComponente::find($request->modelo_componente_id);
                if ($tipoComponente) {
                    $validated['tipo'] = $validated['tipo'] ?? $tipoComponente->tipo;
                    $validated['capacidad'] = $validated['capacidad'] ?? $tipoComponente->capacidad;
                }
            }

            $componente = Componente::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Componente creado exitosamente',
                'data' => $componente->load(['modeloComponente', 'activo', 'institucion', 'responsable']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear componente: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mostrar detalle de un componente.
     */
    public function show($id)
    {
        try {
            $componente = Componente::with([
                'modeloComponente.modelo.marca',
                'activo',
                'institucion',
                'departamento',
                'responsable',
            ])->findOrFail($id);

            return response()->json(['success' => true, 'data' => $componente]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Componente no encontrado'], 404);
        }
    }

    /**
     * Actualizar un componente.
     */
    public function update(Request $request, $id)
    {
        try {
            $componente = Componente::findOrFail($id);

            $validated = $request->validate([
                'tipo' => 'required|string|max:50',
                'modelo_componente_id' => 'nullable|exists:modelo_componente,id',
                'marca' => 'nullable|string|max:100',
                'modelo' => 'nullable|string|max:100',
                'serial' => 'nullable|string|max:100|unique:componentes,serial,' . $id,
                'capacidad' => 'nullable|string|max:50',
                'especificaciones' => 'nullable|json',
                'estado' => 'required|string|in:en_bodega,instalado,prestado,desechado,en_reparacion',
                'activo_id' => 'nullable|exists:activos,id',
                'institucion_id' => 'required|exists:instituciones,id',
                'departamento_id' => 'nullable|exists:departamentos,id',
                'responsable_id' => 'required|exists:responsables,id',
                'ubicacion' => 'nullable|string|max:100',
                'fecha_instalacion' => 'nullable|date',
                'fecha_retiro' => 'nullable|date',
                'observaciones' => 'nullable|string',
            ]);

            $componente->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Componente actualizado exitosamente',
                'data' => $componente->fresh(['modeloComponente', 'activo', 'institucion', 'responsable']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar un componente.
     */
    public function destroy($id)
    {
        try {
            $componente = Componente::findOrFail($id);
            $componente->delete();

            return response()->json(['success' => true, 'message' => 'Componente eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cambiar estado del componente.
     */
    public function toggleStatus($id)
    {
        try {
            $componente = Componente::findOrFail($id);

            $transiciones = [
                'en_bodega' => 'prestado',
                'prestado' => 'en_bodega',
                'instalado' => 'en_bodega',
                'en_reparacion' => 'en_bodega',
            ];

            $nuevoEstado = $transiciones[$componente->estado] ?? 'en_bodega';

            $updateData = ['estado' => $nuevoEstado];

            if ($nuevoEstado === 'en_bodega') {
                $updateData['activo_id'] = null;
                $updateData['fecha_retiro'] = now();
            }

            $componente->update($updateData);

            return response()->json(['success' => true, 'message' => 'Estado actualizado a: ' . $nuevoEstado]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cambiar estado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener componentes por tipo (para filtros).
     */
    public function porTipo($tipo)
    {
        try {
            $componentes = Componente::where('tipo', $tipo)
                                     ->where('estado', 'en_bodega')
                                     ->orderBy('marca')
                                     ->get();

            return response()->json(['success' => true, 'data' => $componentes]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar componentes'], 500);
        }
    }

    /**
     * Obtener componentes en bodega.
     */
    public function enBodega()
    {
        try {
            $componentes = Componente::enBodega()
                                     ->with('modeloComponente')
                                     ->orderBy('tipo')
                                     ->get();

            return response()->json(['success' => true, 'data' => $componentes]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar componentes'], 500);
        }
    }
}
