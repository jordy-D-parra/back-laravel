<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Componente;
use App\Models\ModeloComponente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ComponenteController extends Controller
{
    // ============================================================
    // INDEX — Listado con filtros
    // ============================================================
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-componentes')) {
            abort(403, 'No tienes permiso para ver componentes');
        }

        try {
            // ✅ Sin modeloComponente (esa tabla ya no se usa)
            $query = Componente::with([
                'activo',
                'institucion',
                'responsable',
            ]);

            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->where('tipo', 'like', "%{$buscar}%")
                      ->orWhere('marca', 'like', "%{$buscar}%")
                      ->orWhere('modelo', 'like', "%{$buscar}%")
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
            Log::error('Error al listar componentes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar componentes: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // DISPONIBLES — Componentes sin activo, listos para asignar
    // ============================================================
    public function disponibles(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-componentes')) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        try {
            $query = Componente::query()
                ->whereNull('activo_id')
                ->whereNull('reservado_en_prestamo_id')
                ->whereNotIn('estado', ['desechado']);

            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->where('tipo', 'ILIKE', "%{$buscar}%")
                      ->orWhere('marca', 'ILIKE', "%{$buscar}%")
                      ->orWhere('modelo', 'ILIKE', "%{$buscar}%")
                      ->orWhere('serial', 'ILIKE', "%{$buscar}%");
                });
            }

            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            if ($request->filled('excluir_ids')) {
                $excluir = is_array($request->excluir_ids)
                    ? $request->excluir_ids
                    : explode(',', $request->excluir_ids);
                $query->whereNotIn('id', $excluir);
            }

            $total = $query->count();

            $componentes = $query
                ->orderBy('tipo')
                ->orderBy('marca')
                ->limit(100)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $componentes,
                'total' => $total,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al listar componentes disponibles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // STORE — Crear componente
    // ============================================================
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-componente')) {
            abort(403, 'No tienes permiso para crear componentes');
        }

        try {
            $validated = $request->validate([
                'tipo' => 'required|string|max:50',
                'marca' => 'nullable|string|max:100',
                'modelo' => 'nullable|string|max:100',
                'serial' => 'nullable|string|max:100|unique:componentes,serial',
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

            $componente = Componente::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Componente creado exitosamente',
                'data' => $componente->load(['activo', 'institucion', 'responsable']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear componente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear componente: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // SHOW — Detalle de un componente
    // ============================================================
    public function show($id)
    {
        if (!auth()->user()->hasPermission('ver-componentes')) {
            abort(403, 'No tienes permiso para ver componentes');
        }

        try {
            $componente = Componente::with([
                'activo',
                'institucion',
                'departamento',
                'responsable',
            ])->findOrFail($id);

            return response()->json(['success' => true, 'data' => $componente]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Componente no encontrado'
            ], 404);
        }
    }

    // ============================================================
    // UPDATE — Editar componente
    // ============================================================
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('editar-componente')) {
            abort(403, 'No tienes permiso para editar componentes');
        }

        try {
            $componente = Componente::findOrFail($id);

            $validated = $request->validate([
                'tipo' => 'required|string|max:50',
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
                'data' => $componente->fresh(['activo', 'institucion', 'responsable']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar componente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // DESTROY — Eliminar componente
    // ============================================================
    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('eliminar-componente')) {
            abort(403, 'No tienes permiso para eliminar componentes');
        }

        try {
            $componente = Componente::findOrFail($id);
            $componente->delete();

            return response()->json([
                'success' => true,
                'message' => 'Componente eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar componente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // TOGGLE STATUS
    // ============================================================
    public function toggleStatus($id)
    {
        if (!auth()->user()->hasPermission('editar-componente')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

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

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado a: ' . $nuevoEstado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // POR TIPO
    // ============================================================
    public function porTipo($tipo)
    {
        try {
            $componentes = Componente::where('tipo', $tipo)
                ->where('estado', 'en_bodega')
                ->orderBy('marca')
                ->get();

            return response()->json(['success' => true, 'data' => $componentes]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar componentes'
            ], 500);
        }
    }

    // ============================================================
    // EN BODEGA
    // ============================================================
    public function enBodega()
    {
        try {
            $componentes = Componente::enBodega()
                ->orderBy('tipo')
                ->get();

            return response()->json(['success' => true, 'data' => $componentes]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar componentes'
            ], 500);
        }
    }
}