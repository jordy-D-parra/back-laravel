<?php
// app/Http/Controllers/Admin/EquipoController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use App\Models\Categoria;
use App\Models\Modelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipoController extends Controller
{
    /**
     * Vista principal del catálogo de equipos
     */
    public function index()
    {
        $totalMarcas = Marca::count();
        $totalCategorias = Categoria::count();
        $totalModelos = Modelo::count();
        $totalActivos = Marca::where('activo', true)->count() +
                        Categoria::where('activo', true)->count() +
                        Modelo::where('activo', true)->count();

        return view('admin.equipos.index', compact(
            'totalMarcas',
            'totalCategorias',
            'totalModelos',
            'totalActivos'
        ));
    }

    // ==================== MARCAS ====================
 public function getMarcas(Request $request)
{
    try {
        $query = Marca::withCount('modelos');
        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', "%{$request->buscar}%");
        }
        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }
        $marcas = $query->orderBy('nombre')->get();
        return response()->json(['success' => true, 'data' => $marcas]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error al cargar marcas'], 500);
    }
}



    /**
     * Crear nueva marca
     */
    public function storeMarca(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:100|unique:marcas',
                'descripcion' => 'nullable|string'
            ]);

            $marca = Marca::create($validated);
            return response()->json(['success' => true, 'message' => 'Marca creada exitosamente', 'data' => $marca]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Error de validación: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear la marca: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mostrar detalle de una marca
     */
    public function showMarca($id)
    {
        try {
            $marca = Marca::with(['modelos' => function($q) {
                $q->with('categoria');
            }])->withCount('modelos')->findOrFail($id);

            return response()->json(['success' => true, 'data' => $marca]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Marca no encontrada'], 404);
        }
    }

    /**
     * Actualizar marca
     */
    public function updateMarca(Request $request, $id)
    {
        try {
            $marca = Marca::findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'required|string|max:100|unique:marcas,nombre,' . $id,
                'descripcion' => 'nullable|string'
            ]);

            $marca->update($validated);
            return response()->json(['success' => true, 'message' => 'Marca actualizada exitosamente']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Error de validación: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar la marca'], 500);
        }
    }

    /**
     * Eliminar marca
     */
    public function deleteMarca($id)
    {
        try {
            $marca = Marca::findOrFail($id);

            if ($marca->modelos()->count() > 0) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar la marca porque tiene modelos asociados'], 400);
            }

            $marca->delete();
            return response()->json(['success' => true, 'message' => 'Marca eliminada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar la marca'], 500);
        }
    }

    /**
     * Cambiar estado de marca (Activo/Inactivo)
     */
    public function toggleMarca($id)
    {
        try {
            $marca = Marca::findOrFail($id);
            $marca->activo = !$marca->activo;
            $marca->save();

            return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cambiar el estado'], 500);
        }
    }

    // ==================== CATEGORÍAS ====================

    /**
     * Obtener listado de categorías (API)
     */
    public function getCategorias(Request $request)
    {
        try {
            $query = Categoria::withCount('modelos');

            if ($request->filled('buscar')) {
                $query->where('nombre', 'like', "%{$request->buscar}%");
            }

            if ($request->filled('estado')) {
                $query->where('activo', $request->estado === 'activo');
            }

            $categorias = $query->orderBy('nombre')->get();

            return response()->json(['success' => true, 'data' => $categorias]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar categorías'], 500);
        }
    }

    /**
     * Crear nueva categoría
     */
    public function storeCategoria(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:100|unique:categorias',
                'descripcion' => 'nullable|string'
            ]);

            $categoria = Categoria::create($validated);
            return response()->json(['success' => true, 'message' => 'Categoría creada exitosamente', 'data' => $categoria]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Error de validación: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear la categoría'], 500);
        }
    }

    /**
     * Mostrar detalle de una categoría
     */
    public function showCategoria($id)
    {
        try {
            $categoria = Categoria::with(['modelos' => function($q) {
                $q->with('marca');
            }])->withCount('modelos')->findOrFail($id);

            return response()->json(['success' => true, 'data' => $categoria]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Categoría no encontrada'], 404);
        }
    }

    /**
     * Actualizar categoría
     */
    public function updateCategoria(Request $request, $id)
    {
        try {
            $categoria = Categoria::findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'required|string|max:100|unique:categorias,nombre,' . $id,
                'descripcion' => 'nullable|string'
            ]);

            $categoria->update($validated);
            return response()->json(['success' => true, 'message' => 'Categoría actualizada exitosamente']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Error de validación: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar la categoría'], 500);
        }
    }

    /**
     * Eliminar categoría
     */
    public function deleteCategoria($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);

            if ($categoria->modelos()->count() > 0) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar la categoría porque tiene modelos asociados'], 400);
            }

            $categoria->delete();
            return response()->json(['success' => true, 'message' => 'Categoría eliminada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar la categoría'], 500);
        }
    }

    /**
     * Cambiar estado de categoría (Activo/Inactivo)
     */
    public function toggleCategoria($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria->activo = !$categoria->activo;
            $categoria->save();

            return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cambiar el estado'], 500);
        }
    }

    // ==================== MODELOS ====================

    /**
     * Obtener listado de modelos (API)
     */
    public function getModelos(Request $request)
    {
        try {
            $query = Modelo::with(['marca', 'categoria']);

            if ($request->filled('buscar')) {
                $query->where(function($q) use ($request) {
                    $q->where('nombre', 'like', "%{$request->buscar}%")
                      ->orWhereHas('marca', fn($q2) => $q2->where('nombre', 'like', "%{$request->buscar}%"));
                });
            }

            if ($request->filled('marca_id')) {
                $query->where('marca_id', $request->marca_id);
            }

            if ($request->filled('categoria_id')) {
                $query->where('categoria_id', $request->categoria_id);
            }

            if ($request->filled('estado')) {
                $query->where('activo', $request->estado === 'activo');
            }

            $modelos = $query->orderBy('nombre')->get();

            return response()->json(['success' => true, 'data' => $modelos]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar modelos'], 500);
        }
    }

    /**
     * Crear nuevo modelo
     */
    public function storeModelo(Request $request)
    {
        try {
            $validated = $request->validate([
                'marca_id' => 'required|exists:marcas,id',
                'categoria_id' => 'required|exists:categorias,id',
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'especificaciones' => 'nullable|string'
            ]);

            // Verificar unicidad compuesta (marca + nombre)
            $exists = Modelo::where('marca_id', $validated['marca_id'])
                            ->where('nombre', $validated['nombre'])
                            ->exists();

            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Ya existe un modelo con este nombre para esta marca'], 422);
            }

            $modelo = Modelo::create($validated);
            return response()->json(['success' => true, 'message' => 'Modelo creado exitosamente', 'data' => $modelo->load(['marca', 'categoria'])]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Error de validación: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear el modelo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mostrar detalle de un modelo
     */
    public function showModelo($id)
    {
        try {
            $modelo = Modelo::with(['marca', 'categoria'])->findOrFail($id);
            return response()->json(['success' => true, 'data' => $modelo]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Modelo no encontrado'], 404);
        }
    }

    /**
     * Actualizar modelo
     */
    public function updateModelo(Request $request, $id)
    {
        try {
            $modelo = Modelo::findOrFail($id);

            $validated = $request->validate([
                'marca_id' => 'required|exists:marcas,id',
                'categoria_id' => 'required|exists:categorias,id',
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'especificaciones' => 'nullable|string'
            ]);

            // Verificar unicidad compuesta excluyendo el actual
            $exists = Modelo::where('marca_id', $validated['marca_id'])
                            ->where('nombre', $validated['nombre'])
                            ->where('id', '!=', $id)
                            ->exists();

            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Ya existe un modelo con este nombre para esta marca'], 422);
            }

            $modelo->update($validated);
            return response()->json(['success' => true, 'message' => 'Modelo actualizado exitosamente']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Error de validación: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar el modelo'], 500);
        }
    }

    /**
     * Eliminar modelo
     */
    public function deleteModelo($id)
    {
        try {
            $modelo = Modelo::findOrFail($id);
            $modelo->delete();
            return response()->json(['success' => true, 'message' => 'Modelo eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar el modelo'], 500);
        }
    }

    /**
     * Cambiar estado de modelo (Activo/Inactivo)
     */
    public function toggleModelo($id)
    {
        try {
            $modelo = Modelo::findOrFail($id);
            $modelo->activo = !$modelo->activo;
            $modelo->save();

            return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cambiar el estado'], 500);
        }
    }

    // ==================== LISTAS PARA SELECTS ====================

    /**
     * Obtener lista de marcas para selects (solo activas)
     */
    public function getMarcasList()
    {
        try {
            $marcas = Marca::where('activo', true)
                           ->orderBy('nombre')
                           ->get(['id', 'nombre']);
            return response()->json(['success' => true, 'data' => $marcas]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar marcas'], 500);
        }
    }

    /**
     * Obtener lista de categorías para selects (solo activas)
     */
    public function getCategoriasList()
    {
        try {
            $categorias = Categoria::where('activo', true)
                                   ->orderBy('nombre')
                                   ->get(['id', 'nombre']);
            return response()->json(['success' => true, 'data' => $categorias]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar categorías'], 500);
        }
    }
}
