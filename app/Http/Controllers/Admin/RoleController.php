<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Lista de roles con vista principal
     */
    public function index(Request $request)
    {
        $roles = Rol::withCount('usuarios')->orderBy('nombre')->get();
        $permisos = Permiso::orderBy('categoria')->orderBy('nombre')->get();
        $permisosAgrupados = $permisos->groupBy('categoria');

        return view('admin.roles.index', compact('roles', 'permisosAgrupados'));
    }

    /**
     * Obtener lista de roles (API)
     */
    public function getRoles(Request $request)
    {
        try {
            $roles = Rol::withCount('usuarios')->orderBy('nombre')->get();

            // Agregar conteo de permisos manualmente
            foreach ($roles as $rol) {
                $rol->permisos_count = DB::table('permiso_rol')->where('rol_id', $rol->id)->count();
            }

            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalle de un rol
     */
    public function show($id)
    {
        try {
            $rol = Rol::with('permisos')->withCount('usuarios')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $rol->id,
                    'nombre' => $rol->nombre,
                    'descripcion' => $rol->descripcion,
                    'usuarios_count' => $rol->usuarios_count,
                    'permisos' => $rol->permisos->pluck('id')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado'
            ], 404);
        }
    }

    /**
     * Crear nuevo rol
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:50|unique:roles,nombre',
                'descripcion' => 'nullable|string|max:255',
                'permisos' => 'nullable|array',
                'permisos.*' => 'exists:permisos,id',
            ]);

            $rol = Rol::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
            ]);

            if (!empty($validated['permisos'])) {
                $rol->permisos()->sync($validated['permisos']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rol creado exitosamente',
                'data' => $rol
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar rol
     */
    public function update(Request $request, $id)
    {
        try {
            $rol = Rol::findOrFail($id);

            if ($rol->nombre === 'admin' && $request->nombre !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cambiar el nombre del rol Administrador'
                ], 403);
            }

            $validated = $request->validate([
                'nombre' => 'required|string|max:50|unique:roles,nombre,' . $id,
                'descripcion' => 'nullable|string|max:255',
                'permisos' => 'nullable|array',
                'permisos.*' => 'exists:permisos,id',
            ]);

            $rol->update([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
            ]);

            $rol->permisos()->sync($validated['permisos'] ?? []);

            return response()->json([
                'success' => true,
                'message' => 'Rol actualizado exitosamente'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar rol
     */
    public function destroy($id)
    {
        try {
            $rol = Rol::findOrFail($id);

            if ($rol->nombre === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el rol Administrador'
                ], 403);
            }

            if ($rol->usuarios()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar porque tiene usuarios asignados'
                ], 400);
            }

            $rol->permisos()->detach();
            $rol->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rol eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los permisos
     */
    public function getPermisos()
    {
        try {
            $permisos = Permiso::orderBy('categoria')->orderBy('nombre')->get();
            $agrupados = $permisos->groupBy('categoria');

            return response()->json([
                'success' => true,
                'data' => $agrupados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar permisos'
            ], 500);
        }
    }
}
