<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\Seriales_activo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Seriales_ActivoController extends Controller
{
    /**
     * Mostrar todos los seriales (vista principal)
     */
    public function index()
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            abort(403, 'No tienes permiso para acceder a esta sección');
        }

        $seriales = Seriales_activo::with('activo')->paginate(20);
        return view('inventario.seriales.index', compact('seriales'));
    }

    /**
     * Obtener seriales por activo (AJAX)
     */
    public function getSerialesByActivo($activoId)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $activo = Activo::findOrFail($activoId);
            $seriales = $activo->seriales;
            
            return response()->json([
                'success' => true,
                'activo' => $activo,
                'seriales' => $seriales,
                'total' => $seriales->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener seriales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los seriales disponibles (para asignación)
     */
    public function getSerialesDisponibles(Request $request)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $query = Seriales_activo::with('activo')
                ->where('estado', 'disponible');
            
            // Filtrar por categoría si se especifica
            if ($request->filled('categoria_id')) {
                $query->whereHas('activo', function($q) use ($request) {
                    $q->where('id_tipo_activo', $request->categoria_id);
                });
            }
            
            // Filtrar por tipo de equipo
            if ($request->filled('tipo_equipo')) {
                $query->whereHas('activo', function($q) use ($request) {
                    $q->where('tipo_equipo', $request->tipo_equipo);
                });
            }
            
            $seriales = $query->orderBy('created_at', 'desc')->paginate(20);
            
            return response()->json($seriales);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener seriales disponibles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar un serial a una persona
     */
    public function asignar(Request $request, $id)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $request->validate([
            'asignado_a' => 'required|string|max:100',
            'fecha_asignacion' => 'nullable|date'
        ]);

        try {
            DB::beginTransaction();
            
            $serial = Seriales_activo::findOrFail($id);
            
            // Verificar que esté disponible
            if ($serial->estado !== 'disponible') {
                return response()->json([
                    'success' => false,
                    'message' => 'El serial no está disponible para asignación'
                ], 400);
            }
            
            $serial->update([
                'asignado_a' => $request->asignado_a,
                'fecha_asignacion' => $request->fecha_asignacion ?? now(),
                'estado' => 'asignado'
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Serial asignado exitosamente',
                'data' => $serial
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar serial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de un serial
     */
    public function cambiarEstado(Request $request, $id)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $request->validate([
            'estado' => 'required|in:disponible,asignado,reparacion,dado_baja'
        ]);

        try {
            $serial = Seriales_activo::findOrFail($id);
            $serial->update(['estado' => $request->estado]);
            
            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente',
                'data' => $serial
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar serial como en reparación
     */
    public function marcarEnReparacion($id)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $serial = Seriales_activo::findOrFail($id);
            $serial->update(['estado' => 'reparacion']);
            
            return response()->json([
                'success' => true,
                'message' => 'Serial marcado en reparación',
                'data' => $serial
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dar de baja un serial
     */
    public function darBaja($id)
    {
        // Verificar permisos (solo super_admin)
        if (!Auth::user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $serial = Seriales_activo::findOrFail($id);
            $serial->update(['estado' => 'dado_baja']);
            
            return response()->json([
                'success' => true,
                'message' => 'Serial dado de baja',
                'data' => $serial
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liberar serial (dejar disponible)
     */
    public function liberar($id)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $serial = Seriales_activo::findOrFail($id);
            $serial->update([
                'estado' => 'disponible',
                'asignado_a' => null,
                'fecha_asignacion' => null
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Serial liberado exitosamente',
                'data' => $serial
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al liberar serial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un serial (solo si está disponible o dado de baja)
     */
    public function destroy($id)
    {
        // Verificar permisos (solo super_admin)
        if (!Auth::user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $serial = Seriales_activo::findOrFail($id);
            
            // Solo permitir eliminar si está disponible o dado de baja
            if (!in_array($serial->estado, ['disponible', 'dado_baja'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un serial que está asignado o en reparación'
                ], 400);
            }
            
            $serial->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Serial eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar serial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar reporte de seriales por estado
     */
    public function reportePorEstado()
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $reporte = [
                'disponible' => Seriales_activo::where('estado', 'disponible')->count(),
                'asignado' => Seriales_activo::where('estado', 'asignado')->count(),
                'reparacion' => Seriales_activo::where('estado', 'reparacion')->count(),
                'dado_baja' => Seriales_activo::where('estado', 'dado_baja')->count(),
                'total' => Seriales_activo::count(),
                'por_activo' => Seriales_activo::with('activo')
                    ->select('activo_id', DB::raw('count(*) as total'))
                    ->groupBy('activo_id')
                    ->get()
            ];
            
            return response()->json($reporte);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar serial por código
     */
    public function buscar(Request $request)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'serial' => 'required|string|min:3'
        ]);

        try {
            $serial = Seriales_activo::with('activo')
                ->where('serial', 'LIKE', "%{$request->serial}%")
                ->first();
            
            if ($serial) {
                return response()->json([
                    'success' => true,
                    'data' => $serial
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Serial no encontrado'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de asignaciones de un serial
     */
    public function historial($id)
    {
        // Verificar permisos
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $serial = Seriales_activo::with('activo')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'serial' => $serial,
                'ultima_asignacion' => [
                    'asignado_a' => $serial->asignado_a,
                    'fecha_asignacion' => $serial->fecha_asignacion,
                    'estado_actual' => $serial->estado
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: ' . $e->getMessage()
            ], 500);
        }
    }
}