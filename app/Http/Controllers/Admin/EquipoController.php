<?php

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
        // Verificar permiso (al menos ver marcas, categorías o modelos)
        if (!auth()->user()->hasPermission('ver-marcas') &&
            !auth()->user()->hasPermission('ver-categorias-equipos') &&
            !auth()->user()->hasPermission('ver-modelos')) {
            abort(403, 'No tienes permiso para ver el catálogo de equipos');
        }

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
        if (!auth()->user()->hasPermission('ver-marcas')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para ver marcas'], 403);
        }

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

    public function storeMarca(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-marca')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para crear marcas'], 403);
        }

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

    public function showMarca($id)
    {
        if (!auth()->user()->hasPermission('ver-marcas')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para ver marcas'], 403);
        }

        try {
            $marca = Marca::with(['modelos' => function($q) {
                $q->with('categoria');
            }])->withCount('modelos')->findOrFail($id);

            return response()->json(['success' => true, 'data' => $marca]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Marca no encontrada'], 404);
        }
    }

    public function updateMarca(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('editar-marca')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para editar marcas'], 403);
        }

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

    public function deleteMarca($id)
    {
        if (!auth()->user()->hasPermission('eliminar-marca')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar marcas'], 403);
        }

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

    public function toggleMarca($id)
    {
        if (!auth()->user()->hasPermission('editar-marca')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para cambiar el estado de marcas'], 403);
        }

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

    public function getCategorias(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-categorias-equipos')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para ver categorías'], 403);
        }

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

    public function storeCategoria(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-categoria-equipo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para crear categorías'], 403);
        }

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

    public function showCategoria($id)
    {
        if (!auth()->user()->hasPermission('ver-categorias-equipos')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para ver categorías'], 403);
        }

        try {
            $categoria = Categoria::with(['modelos' => function($q) {
                $q->with('marca');
            }])->withCount('modelos')->findOrFail($id);

            return response()->json(['success' => true, 'data' => $categoria]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Categoría no encontrada'], 404);
        }
    }

    public function updateCategoria(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('editar-categoria-equipo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para editar categorías'], 403);
        }

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

    public function deleteCategoria($id)
    {
        if (!auth()->user()->hasPermission('eliminar-categoria-equipo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar categorías'], 403);
        }

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

    public function toggleCategoria($id)
    {
        if (!auth()->user()->hasPermission('editar-categoria-equipo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para cambiar el estado de categorías'], 403);
        }

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

    public function getModelos(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-modelos')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para ver modelos'], 403);
        }

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

    public function storeModelo(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-modelo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para crear modelos'], 403);
        }

        try {
            $validated = $request->validate([
                'marca_id' => 'required|exists:marcas,id',
                'categoria_id' => 'required|exists:categorias,id',
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'especificaciones' => 'nullable|string'
            ]);

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

    public function showModelo($id)
    {
        if (!auth()->user()->hasPermission('ver-modelos')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para ver modelos'], 403);
        }

        try {
            $modelo = Modelo::with(['marca', 'categoria'])->findOrFail($id);
            return response()->json(['success' => true, 'data' => $modelo]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Modelo no encontrado'], 404);
        }
    }

    public function updateModelo(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('editar-modelo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para editar modelos'], 403);
        }

        try {
            $modelo = Modelo::findOrFail($id);

            $validated = $request->validate([
                'marca_id' => 'required|exists:marcas,id',
                'categoria_id' => 'required|exists:categorias,id',
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'especificaciones' => 'nullable|string'
            ]);

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

    public function deleteModelo($id)
    {
        if (!auth()->user()->hasPermission('eliminar-modelo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar modelos'], 403);
        }

        try {
            $modelo = Modelo::findOrFail($id);
            $modelo->delete();
            return response()->json(['success' => true, 'message' => 'Modelo eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar el modelo'], 500);
        }
    }

    public function toggleModelo($id)
    {
        if (!auth()->user()->hasPermission('editar-modelo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para cambiar el estado de modelos'], 403);
        }

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
