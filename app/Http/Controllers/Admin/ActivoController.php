<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Estatus;
use App\Models\Modelo;
use App\Models\Institucion;
use App\Models\Departamento;
use App\Models\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivoController extends Controller
{
    // ============================================================
    // INDEX — Listado de activos con filtros
    // ============================================================
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-activos')) {
            abort(403, 'No tienes permiso para ver activos');
        }

        try {
            $query = Activo::with([
                'modelo.marca',
                'modelo.categoria',
                'estatus',
                'institucion',
                'departamento',
                'responsable',
                'componentes'
            ]);

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
            Log::error('Error al listar activos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar activos: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // STORE — Crear activo + componentes en una transacción
    // ✅ RESPONSABLE AUTO-ASIGNADO desde institución/departamento
    // ============================================================
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-activo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para crear activos'], 403);
        }

        try {
            $validated = $request->validate([
                // Datos del activo
                'serial' => 'required|string|max:100|unique:activos,serial',
                'modelo_id' => 'required|exists:modelos,id',
                'id_estatus' => 'required|exists:estatus,id',
                'institucion_id' => 'required|exists:instituciones,id',
                'departamento_id' => 'nullable|exists:departamentos,id',
                'responsable_id' => 'nullable|exists:responsables,id',
                'ubicacion' => 'nullable|string|max:100',
                'fecha_adquisicion' => 'nullable|date',
                'fecha_fin_garantia' => 'nullable|date',
                'vida_util_anos' => 'nullable|integer|min:1',
                'especificaciones_tecnicas' => 'nullable|json',
                'agrupacion' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string',

                // Componentes (opcional)
                'componentes' => 'nullable',
            ]);

            // ✅ RESOLVER RESPONSABLE AUTOMÁTICO
            $responsableId = $this->resolverResponsable(
                $request->departamento_id,
                $request->institucion_id
            );

            if (!$responsableId) {
                $entidad = $request->departamento_id
                    ? 'El departamento seleccionado'
                    : 'La institución seleccionada';

                return response()->json([
                    'success' => false,
                    'message' => "⚠️ {$entidad} no tiene un responsable asignado.\n\n" .
                                 "Por favor, asígnalo primero en el módulo de Entidades antes de continuar."
                ], 422);
            }

            DB::beginTransaction();

            // 1. Crear el activo
            $activo = Activo::create([
                'serial' => $validated['serial'],
                'modelo_id' => $validated['modelo_id'],
                'id_estatus' => $validated['id_estatus'],
                'institucion_id' => $validated['institucion_id'],
                'departamento_id' => $validated['departamento_id'] ?? null,
                'responsable_id' => $responsableId, // ✅ AUTO-ASIGNADO
                'ubicacion' => $validated['ubicacion'] ?? null,
                'fecha_adquisicion' => $validated['fecha_adquisicion'] ?? null,
                'fecha_fin_garantia' => $validated['fecha_fin_garantia'] ?? null,
                'vida_util_anos' => $validated['vida_util_anos'] ?? null,
                'especificaciones_tecnicas' => $validated['especificaciones_tecnicas'] ?? null,
                'agrupacion' => $validated['agrupacion'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            // 2. Crear los componentes
            $componentesData = $this->parseComponentes($request->input('componentes'));
            $componentesCreados = 0;

            foreach ($componentesData as $comp) {
                $this->crearComponente($comp, $activo);
                $componentesCreados++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Activo creado exitosamente con {$componentesCreados} componente(s). Responsable asignado automáticamente.",
                'data' => $activo->load([
                    'modelo.marca',
                    'modelo.categoria',
                    'estatus',
                    'institucion',
                    'responsable',
                    'componentes',
                ]),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear activo: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear activo: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // SHOW — Detalle del activo con componentes
    // ============================================================
    public function show($id)
    {
        if (!auth()->user()->hasPermission('ver-activos')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para ver activos'], 403);
        }

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

    // ============================================================
    // UPDATE — Actualizar activo + sincronizar componentes
    // ✅ RESPONSABLE AUTO-ASIGNADO desde institución/departamento
    // ============================================================
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('editar-activo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para editar activos'], 403);
        }

        try {
            $activo = Activo::findOrFail($id);

            // Soporte para cambio de estado parcial (desde modal de cambio de estado)
            if ($request->has('id_estatus') && count($request->all()) <= 2) {
                $request->validate([
                    'id_estatus' => 'required|exists:estatus,id',
                ]);

                $activo->update(['id_estatus' => $request->id_estatus]);

                return response()->json([
                    'success' => true,
                    'message' => 'Estado actualizado correctamente',
                    'data' => $activo->fresh(['estatus']),
                ]);
            }

            // Actualización completa
            $validated = $request->validate([
                'serial' => 'required|string|max:100|unique:activos,serial,' . $id,
                'modelo_id' => 'required|exists:modelos,id',
                'id_estatus' => 'required|exists:estatus,id',
                'institucion_id' => 'required|exists:instituciones,id',
                'departamento_id' => 'nullable|exists:departamentos,id',
                'responsable_id' => 'nullable|exists:responsables,id',
                'ubicacion' => 'nullable|string|max:100',
                'fecha_adquisicion' => 'nullable|date',
                'fecha_fin_garantia' => 'nullable|date',
                'vida_util_anos' => 'nullable|integer|min:1',
                'especificaciones_tecnicas' => 'nullable|json',
                'agrupacion' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string',
                'componentes' => 'nullable',
            ]);

            // ✅ RESOLVER RESPONSABLE AUTOMÁTICO
            $responsableId = $this->resolverResponsable(
                $request->departamento_id,
                $request->institucion_id
            );

            if (!$responsableId) {
                $entidad = $request->departamento_id
                    ? 'El departamento seleccionado'
                    : 'La institución seleccionada';

                return response()->json([
                    'success' => false,
                    'message' => "⚠️ {$entidad} no tiene un responsable asignado.\n\n" .
                                 "Por favor, asígnalo primero en el módulo de Entidades antes de continuar."
                ], 422);
            }

            DB::beginTransaction();

            // 1. Actualizar el activo
            $activo->update([
                'serial' => $validated['serial'],
                'modelo_id' => $validated['modelo_id'],
                'id_estatus' => $validated['id_estatus'],
                'institucion_id' => $validated['institucion_id'],
                'departamento_id' => $validated['departamento_id'] ?? null,
                'responsable_id' => $responsableId, // ✅ AUTO-ASIGNADO
                'ubicacion' => $validated['ubicacion'] ?? null,
                'fecha_adquisicion' => $validated['fecha_adquisicion'] ?? null,
                'fecha_fin_garantia' => $validated['fecha_fin_garantia'] ?? null,
                'vida_util_anos' => $validated['vida_util_anos'] ?? null,
                'especificaciones_tecnicas' => $validated['especificaciones_tecnicas'] ?? null,
                'agrupacion' => $validated['agrupacion'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            // 2. Sincronizar componentes
            $componentesData = $this->parseComponentes($request->input('componentes'));
            $idsRecibidos = collect($componentesData)->pluck('id')->filter()->all();

            // 2.1 Desvincular componentes que ya no vienen en la lista
            $componentesActuales = Componente::where('activo_id', $activo->id)->get();
            foreach ($componentesActuales as $compActual) {
                if (!in_array($compActual->id, $idsRecibidos)) {
                    $compActual->update([
                        'activo_id' => null,
                        'estado' => 'en_bodega',
                        'fecha_retiro' => now(),
                    ]);
                }
            }

            // 2.2 Crear o actualizar cada componente recibido
            $creados = 0;
            $actualizados = 0;

            foreach ($componentesData as $comp) {
                if (!empty($comp['id'])) {
                    // Actualizar existente
                    $componente = Componente::find($comp['id']);
                    if ($componente) {
                        $this->actualizarComponente($componente, $comp, $activo);
                        $actualizados++;
                    }
                } else {
                    // Crear nuevo
                    $this->crearComponente($comp, $activo);
                    $creados++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Activo actualizado. Componentes: {$creados} nuevo(s), {$actualizados} actualizado(s)",
                'data' => $activo->fresh()->load([
                    'modelo.marca',
                    'modelo.categoria',
                    'estatus',
                    'institucion',
                    'responsable',
                    'componentes',
                ]),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar activo: ' . $e->getMessage(), [
                'activo_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // DESTROY — Eliminar activo (desvinculando componentes)
    // ============================================================
    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('eliminar-activo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar activos'], 403);
        }

        try {
            $activo = Activo::findOrFail($id);

            DB::beginTransaction();

            // Desvincular componentes (no se eliminan, vuelven a bodega)
            Componente::where('activo_id', $activo->id)->update([
                'activo_id' => null,
                'estado' => 'en_bodega',
                'fecha_retiro' => now(),
            ]);

            $activo->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Activo eliminado exitosamente. Sus componentes volvieron a bodega.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar activo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // TOGGLE STATUS
    // ============================================================
    public function toggleStatus($id)
    {
        if (!auth()->user()->hasPermission('cambiar-estatus-activo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para cambiar el estado de activos'], 403);
        }

        try {
            $activo = Activo::with('estatus')->findOrFail($id);

            if ($activo->estatus && $activo->estatus->es_terminal) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cambiar un estado terminal'
                ], 400);
            }

            $nuevoEstatusDescripcion = ($activo->estatus && $activo->estatus->descripcion === 'Disponible')
                ? 'Prestado'
                : 'Disponible';

            $nuevoEstatus = Estatus::where('descripcion', $nuevoEstatusDescripcion)->first();

            if ($nuevoEstatus) {
                $activo->update(['id_estatus' => $nuevoEstatus->id]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado a: ' . $nuevoEstatusDescripcion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // POR MODELO — Activos de un modelo
    // ============================================================
    public function porModelo($modeloId)
    {
        try {
            $activos = Activo::where('modelo_id', $modeloId)
                             ->with('estatus')
                             ->orderBy('serial')
                             ->get(['id', 'serial', 'id_estatus']);

            return response()->json(['success' => true, 'data' => $activos]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar activos'
            ], 500);
        }
    }

    // ============================================================
    // HELPERS PRIVADOS
    // ============================================================

    /**
     * ✅ NUEVO: Resolver responsable automático.
     * Prioridad:
     *   1. Si hay departamento → responsable del departamento
     *   2. Si hay institución → responsable directo (sin departamento)
     *   3. Si no hay ninguno → null (el controlador rechaza)
     */
    private function resolverResponsable(?int $departamentoId, ?int $institucionId): ?int
    {
        // Prioridad 1: Departamento
        if ($departamentoId) {
            $responsable = Responsable::where('departamento_id', $departamentoId)
                ->where('activo', true)
                ->first();

            if ($responsable) {
                return $responsable->id;
            }
        }

        // Prioridad 2: Institución (responsable directo)
        if ($institucionId) {
            $responsable = Responsable::where('institucion_id', $institucionId)
                ->whereNull('departamento_id')
                ->where('activo', true)
                ->first();

            if ($responsable) {
                return $responsable->id;
            }
        }

        return null;
    }

    /**
     * Parsea los componentes que pueden venir como string JSON (desde FormData)
     * o como array directo (desde JSON body).
     */
    private function parseComponentes($componentes): array
    {
        if (empty($componentes)) {
            return [];
        }

        if (is_string($componentes)) {
            $decoded = json_decode($componentes, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($componentes) ? $componentes : [];
    }

    /**
     * Crea un componente vinculado al activo.
     */
    private function crearComponente(array $comp, Activo $activo): Componente
    {
        if (!empty($comp['serial'])) {
            $existe = Componente::where('serial', $comp['serial'])->exists();
            if ($existe) {
                throw new \Exception("El serial '{$comp['serial']}' del componente ya está registrado.");
            }
        }

        return Componente::create([
            'tipo' => $comp['tipo'],
            'marca' => $comp['marca'] ?? null,
            'modelo' => $comp['modelo'] ?? null,
            'serial' => $comp['serial'] ?? null,
            'capacidad' => $comp['capacidad'] ?? null,
            'estado' => $comp['estado'] ?? 'instalado',
            'activo_id' => $activo->id,
            'institucion_id' => $activo->institucion_id,
            'departamento_id' => $activo->departamento_id,
            'responsable_id' => $activo->responsable_id,
            'ubicacion' => $activo->ubicacion,
            'fecha_instalacion' => now(),
            'observaciones' => $comp['observaciones'] ?? null,
        ]);
    }

    /**
     * Actualiza un componente existente.
     */
    private function actualizarComponente(Componente $componente, array $comp, Activo $activo): void
    {
        if (!empty($comp['serial'])) {
            $existe = Componente::where('serial', $comp['serial'])
                ->where('id', '!=', $componente->id)
                ->exists();
            if ($existe) {
                throw new \Exception("El serial '{$comp['serial']}' del componente ya está registrado.");
            }
        }

        $componente->update([
            'tipo' => $comp['tipo'],
            'marca' => $comp['marca'] ?? null,
            'modelo' => $comp['modelo'] ?? null,
            'serial' => $comp['serial'] ?? null,
            'capacidad' => $comp['capacidad'] ?? null,
            'estado' => $comp['estado'] ?? 'instalado',
            'activo_id' => $activo->id,
            'institucion_id' => $activo->institucion_id,
            'departamento_id' => $activo->departamento_id,
            'responsable_id' => $activo->responsable_id,
            'ubicacion' => $activo->ubicacion,
            'observaciones' => $comp['observaciones'] ?? null,
        ]);
    }
}