<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestamo;
use App\Models\PrestamoDetalle;
use App\Models\PrestamoExtension;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Departamento;
use App\Models\Institucion;
use App\Models\Responsable;
use App\Models\Solicitud;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PrestamoController extends Controller
{
    // ===========================
    // VISTA PRINCIPAL
    // ===========================
    public function index()
    {
        if (!auth()->user()->hasPermission('ver-prestamos')) {
            abort(403, 'No tienes permiso para ver préstamos');
        }

        $totalPrestamos = Prestamo::count();
        $prestamosActivos = Prestamo::whereIn('estado', ['aprobado', 'entregado', 'extendido'])->count();
        $prestamosVencidos = Prestamo::whereIn('estado', ['entregado', 'extendido'])
            ->where('fecha_devolucion_esperada', '<', now()->format('Y-m-d'))
            ->count();
        $prestamosDevueltos = Prestamo::where('estado', 'devuelto')->count();

        $departamentos = Departamento::where('activo', true)->orderBy('nombre')->get();
        $instituciones = Institucion::where('activo', true)->orderBy('nombre')->get();

        $responsableInformatica = null;
        $deptoInformatica = Departamento::where('nombre', 'ILIKE', '%informatica%')
            ->orWhere('nombre', 'ILIKE', '%informática%')
            ->orWhere('nombre', 'ILIKE', '%sistemas%')
            ->orWhere('nombre', 'ILIKE', '%tecnologia%')
            ->first();

        if ($deptoInformatica) {
            $responsableInformatica = $deptoInformatica->responsables()
                ->where('activo', true)
                ->first();
        }

        return view('admin.prestamos.index', compact(
            'totalPrestamos',
            'prestamosActivos',
            'prestamosVencidos',
            'prestamosDevueltos',
            'departamentos',
            'instituciones',
            'responsableInformatica'
        ));
    }

    // ===========================
    // API: LISTAR PRÉSTAMOS
    // ===========================
    public function listar(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-prestamos')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        $query = Prestamo::with([
            'departamento:id,nombre',
            'institucion:id,nombre',
            'responsableReceptor:id,nombre',
            'responsableEmisor:id,nombre',
            'solicitud:id,estado_solicitud',
            'detalles.prestable',
        ])->withCount('detalles');

        if ($request->buscar) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('codigo', 'ILIKE', "%{$buscar}%")
                  ->orWhereHas('departamento', fn($q) => $q->where('nombre', 'ILIKE', "%{$buscar}%"))
                  ->orWhereHas('institucion', fn($q) => $q->where('nombre', 'ILIKE', "%{$buscar}%"))
                  ->orWhereHas('responsableReceptor', fn($q) => $q->where('nombre', 'ILIKE', "%{$buscar}%"))
                  ->orWhereHas('responsableEmisor', fn($q) => $q->where('nombre', 'ILIKE', "%{$buscar}%"));
            });
        }

        if ($request->estado) {
            $estados = $request->estado;

            if (is_string($estados) && str_contains($estados, ',')) {
                $estados = explode(',', $estados);
            }

            if ($estados === 'vencido' || (is_array($estados) && count($estados) === 1 && $estados[0] === 'vencido')) {
                $query->whereIn('estado', ['entregado', 'extendido'])
                    ->where('fecha_devolucion_esperada', '<', now()->format('Y-m-d'));
            } elseif (is_array($estados)) {
                $query->whereIn('estado', $estados);
            } else {
                $query->where('estado', $estados);
            }
        }

        if ($request->tipo) {
            $query->where('tipo_prestamo', $request->tipo);
        }

        if ($request->departamento_id) {
            $query->where('departamento_id', $request->departamento_id);
        }

        if ($request->institucion_id) {
            $query->where('institucion_id', $request->institucion_id);
        }

        if ($request->fecha_desde) {
            $query->where('fecha_prestamo', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->where('fecha_prestamo', '<=', $request->fecha_hasta);
        }

        $prestamos = $query->orderBy('created_at', 'desc')->paginate(10);

        $prestamos->getCollection()->transform(function ($prestamo) {
            $prestamo->dias_restantes = $prestamo->dias_restantes;
            $prestamo->esta_vencido = $prestamo->esta_vencido;
            $prestamo->destino_nombre = $prestamo->destino_nombre;
            $prestamo->solicitud_codigo = $prestamo->solicitud
                ? 'Solicitud #' . $prestamo->solicitud->id
                : null;
            return $prestamo;
        });

        return response()->json($prestamos);
    }

    // ===========================
    // API: MOSTRAR PRÉSTAMO
    // ===========================
    public function show(Prestamo $prestamo)
    {
        if (!auth()->user()->hasPermission('ver-prestamos')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        $prestamo->load([
            'departamento:id,nombre',
            'institucion:id,nombre',
            'responsableReceptor:id,nombre,documento,telefono,email,cargo',
            'responsableEmisor:id,nombre,documento,telefono,email,cargo',
            'usuarioRegistra:id,usuario', // <--- CORREGIDO
            'solicitud:id,estado_solicitud',
            'detalles.prestable',
            'extensiones.aprobadoPor:id,nombre,usuario',
        ]);

        $prestamo->dias_restantes = $prestamo->dias_restantes;
        $prestamo->esta_vencido = $prestamo->esta_vencido;
        $prestamo->destino_nombre = $prestamo->destino_nombre;
        $prestamo->solicitud_codigo = $prestamo->solicitud
            ? 'Solicitud #' . $prestamo->solicitud->id
            : null;

        return response()->json(['success' => true, 'data' => $prestamo]);
    }

    // ===========================
    // API: CREAR PRÉSTAMO
    // ===========================
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-prestamo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para crear préstamos'], 403);
        }

        $validated = $request->validate([
            'tipo_prestamo' => 'required|in:equipo,componente,mixto',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'institucion_id' => 'nullable|exists:instituciones,id',
            'responsable_receptor_id' => 'required|exists:responsables,id',
            'responsable_emisor_id' => 'required|exists:responsables,id',
            'fecha_prestamo' => 'required|date',
            'fecha_devolucion_esperada' => 'required|date|after_or_equal:fecha_prestamo',
            'observaciones' => 'nullable|string|max:1000',
            'condiciones' => 'nullable|string|max:1000',
            'solicitud_id' => 'nullable|exists:solicitudes,id',
            'estado' => 'nullable|in:pendiente,aprobado,entregado',
            'items' => 'required|array|min:1',
            'items.*.prestable_type' => 'required|in:App\\Models\\Activo,App\\Models\\Componente',
            'items.*.prestable_id' => 'required|integer',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.estado_entrega' => 'nullable|string|max:500',
            'items.*.observaciones' => 'nullable|string|max:500',
        ]);

        if (!$request->departamento_id && !$request->institucion_id) {
            return response()->json([
                'success' => false,
                'message' => 'Debe seleccionar un departamento o institución de destino'
            ], 422);
        }

        $itemsData = collect($validated['items']);

        foreach ($itemsData as $item) {
            if ($item['prestable_type'] === Activo::class) {
                $activo = Activo::find($item['prestable_id']);
                if (!$activo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El activo con ID ' . $item['prestable_id'] . ' no existe',
                    ], 422);
                }
                if (!$activo->estaDisponible()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El activo ' . ($activo->serial ?? '') . ' no está disponible',
                    ], 422);
                }
            } else {
                $componente = Componente::find($item['prestable_id']);
                if (!$componente) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El componente con ID ' . $item['prestable_id'] . ' no existe',
                    ], 422);
                }
                if (!$componente->estaDisponible()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El componente ' . ($componente->serial ?? '') . ' no está disponible',
                    ], 422);
                }
            }
        }

        DB::beginTransaction();
        try {
            $estado = $validated['estado'] ?? 'aprobado';

            $prestamo = Prestamo::create([
                'codigo' => Prestamo::generarCodigo(),
                'tipo_prestamo' => $validated['tipo_prestamo'],
                'estado' => $estado,
                'departamento_id' => $validated['departamento_id'] ?? null,
                'institucion_id' => $validated['institucion_id'] ?? null,
                'responsable_receptor_id' => $validated['responsable_receptor_id'],
                'responsable_emisor_id' => $validated['responsable_emisor_id'],
                'usuario_registra_id' => auth()->id(),
                'fecha_prestamo' => $validated['fecha_prestamo'],
                'fecha_devolucion_esperada' => $validated['fecha_devolucion_esperada'],
                'observaciones' => $validated['observaciones'] ?? null,
                'condiciones' => $validated['condiciones'] ?? null,
                'solicitud_id' => $validated['solicitud_id'] ?? null,
            ]);

            foreach ($itemsData as $item) {
                $detalle = PrestamoDetalle::create([
                    'prestamo_id' => $prestamo->id,
                    'prestable_type' => $item['prestable_type'],
                    'prestable_id' => $item['prestable_id'],
                    'cantidad' => $item['cantidad'],
                    'estado_entrega' => $item['estado_entrega'] ?? null,
                    'observaciones' => $item['observaciones'] ?? null,
                ]);

                if (in_array($estado, ['entregado', 'aprobado'])) {
                    if ($item['prestable_type'] === Activo::class) {
                        $activo = Activo::find($item['prestable_id']);
                        if ($activo) {
                            $activo->marcarComoPrestado();
                        }
                    } else {
                        $componente = Componente::find($item['prestable_id']);
                        if ($componente) {
                            $componente->marcarComoPrestado();
                        }
                    }
                }
            }

            if ($request->solicitud_id) {
                $solicitud = Solicitud::find($request->solicitud_id);
                if ($solicitud) {
                    if ($estado === 'entregado') {
                        $solicitud->update(['estado_solicitud' => 'entregada']);
                    } elseif ($estado === 'aprobado' && $solicitud->estado_solicitud !== 'aprobada') {
                        $solicitud->update(['estado_solicitud' => 'aprobada']);
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear préstamo: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el préstamo: ' . $e->getMessage(),
            ], 500);
        }

        $prestamo->load(['detalles.prestable', 'responsableReceptor:id,nombre']);

        return response()->json([
            'success' => true,
            'message' => 'Préstamo creado exitosamente - Código: ' . $prestamo->codigo,
            'data' => $prestamo
        ]);
    }

    // ===========================
    // API: APROBAR PRÉSTAMO
    // ===========================
    public function aprobar(Request $request, Prestamo $prestamo)
    {
        if (!auth()->user()->hasPermission('aprobar-prestamo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para aprobar préstamos'], 403);
        }

        if ($prestamo->estado !== 'pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden aprobar préstamos pendientes'
            ], 422);
        }

        $validated = $request->validate([
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $observaciones = $prestamo->observaciones ?? '';
        $nuevaObservacion = trim("Aprobación: " . ($validated['observaciones'] ?? 'Sin observaciones'));
        $prestamo->update([
            'estado' => 'aprobado',
            'observaciones' => $observaciones ? $observaciones . "\n\n" . $nuevaObservacion : $nuevaObservacion,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Préstamo aprobado exitosamente',
            'data' => $prestamo->fresh()
        ]);
    }

    // ===========================
    // API: RECHAZAR PRÉSTAMO
    // ===========================
    public function rechazar(Request $request, Prestamo $prestamo)
    {
        if (!auth()->user()->hasPermission('aprobar-prestamo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para rechazar préstamos'], 403);
        }

        if ($prestamo->estado !== 'pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden rechazar préstamos pendientes'
            ], 422);
        }

        $validated = $request->validate([
            'motivo' => 'required|string|max:1000',
        ]);

        $observaciones = $prestamo->observaciones ?? '';
        $nuevaObservacion = trim("Rechazo: " . $validated['motivo']);
        $prestamo->update([
            'estado' => 'rechazado',
            'observaciones' => $observaciones ? $observaciones . "\n\n" . $nuevaObservacion : $nuevaObservacion,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Préstamo rechazado exitosamente',
            'data' => $prestamo->fresh()
        ]);
    }

    // ===========================
    // API: ENTREGAR PRÉSTAMO
    // ===========================
    public function entregar(Request $request, Prestamo $prestamo)
    {
        if (!auth()->user()->hasPermission('editar-prestamo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para entregar préstamos'], 403);
        }

        if ($prestamo->estado !== 'aprobado') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden entregar préstamos aprobados'
            ], 422);
        }

        $validated = $request->validate([
            'fecha_prestamo' => 'required|date',
            'fecha_devolucion_esperada' => 'required|date|after_or_equal:fecha_prestamo',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $observaciones = $prestamo->observaciones ?? '';
            $nuevaObservacion = trim("Entrega: " . ($validated['observaciones'] ?? 'Sin observaciones'));

            $prestamo->update([
                'estado' => 'entregado',
                'fecha_prestamo' => $validated['fecha_prestamo'],
                'fecha_devolucion_esperada' => $validated['fecha_devolucion_esperada'],
                'observaciones' => $observaciones ? $observaciones . "\n\n" . $nuevaObservacion : $nuevaObservacion,
            ]);

            foreach ($prestamo->detalles as $detalle) {
                $prestable = $detalle->prestable;
                if ($prestable instanceof Activo) {
                    $prestable->marcarComoPrestado();
                } elseif ($prestable instanceof Componente) {
                    $prestable->marcarComoPrestado();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al entregar préstamo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al entregar el préstamo: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Préstamo entregado exitosamente',
            'data' => $prestamo->fresh()
        ]);
    }

    // ===========================
    // API: ACTUALIZAR PRÉSTAMO
    // ===========================
    public function update(Request $request, Prestamo $prestamo)
    {
        if (!auth()->user()->hasPermission('editar-prestamo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para editar préstamos'], 403);
        }

        $validated = $request->validate([
            'fecha_devolucion_esperada' => 'sometimes|date',
            'observaciones' => 'nullable|string|max:1000',
            'condiciones' => 'nullable|string|max:1000',
        ]);

        $prestamo->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Préstamo actualizado exitosamente',
            'data' => $prestamo
        ]);
    }

    // ===========================
    // API: REGISTRAR DEVOLUCIÓN
    // ===========================
    public function devolver(Request $request, Prestamo $prestamo)
    {
        if (!auth()->user()->hasPermission('editar-prestamo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        if (!in_array($prestamo->estado, ['entregado', 'extendido'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden devolver préstamos en estado entregado o extendido'
            ], 422);
        }

        $validated = $request->validate([
            'fecha_devolucion_real' => 'required|date',
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:prestamo_detalles,id',
            'items.*.devuelto' => 'nullable|in:1,true,on',
            'items.*.estado_devolucion' => 'nullable|string|max:500',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $itemsData = collect($validated['items'] ?? []);

        if ($itemsData->isEmpty()) {
            $itemsData = $prestamo->detalles->map(function ($detalle) {
                return ['id' => $detalle->id, 'devuelto' => '1'];
            });
        }

        $itemsADevolver = $itemsData->filter(fn ($item) => !empty($item['devuelto']))->values();
        if ($itemsADevolver->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Debe seleccionar al menos un item para devolver.',
            ], 422);
        }

        $totalDetalles = $prestamo->detalles()->count();
        $devolucionCompleta = $itemsADevolver->count() === $totalDetalles;

        DB::beginTransaction();
        try {
            $observaciones = $prestamo->observaciones ?? '';
            $nuevaObservacion = trim("Devolución: " . ($validated['observaciones'] ?? 'Sin observaciones'));

            $prestamo->update([
                'estado' => $devolucionCompleta ? 'devuelto' : 'entregado',
                'fecha_devolucion_real' => $devolucionCompleta ? $validated['fecha_devolucion_real'] : null,
                'observaciones' => $observaciones ? $observaciones . "\n\n" . $nuevaObservacion : $nuevaObservacion,
            ]);

            foreach ($prestamo->detalles as $detalle) {
                $itemData = $itemsData->firstWhere('id', $detalle->id);
                $devolverAhora = !empty($itemData['devuelto']);

                $detalle->update([
                    'estado_devolucion' => $devolverAhora
                        ? ($itemData['estado_devolucion'] ?? 'Devuelto en buen estado')
                        : 'Pendiente de devolución',
                ]);

                if ($devolverAhora) {
                    $prestable = $detalle->prestable;
                    if ($prestable instanceof Activo) {
                        $prestable->marcarComoDisponible();
                    } elseif ($prestable instanceof Componente) {
                        $prestable->marcarComoDevuelto();
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al registrar devolución: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar devolución: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $devolucionCompleta
                ? 'Devolución registrada exitosamente'
                : 'Devolución parcial registrada. Los items restantes permanecen pendientes de devolución.',
            'data' => $prestamo->fresh()->load('detalles.prestable')
        ]);
    }

    // ===========================
    // API: CANCELAR PRÉSTAMO
    // ===========================
    public function cancelar(Request $request, Prestamo $prestamo)
    {
        if (!auth()->user()->hasPermission('editar-prestamo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        if (!in_array($prestamo->estado, ['pendiente', 'aprobado'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden cancelar préstamos pendientes o aprobados'
            ], 422);
        }

        $validated = $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        $observaciones = $prestamo->observaciones ?? '';
        $nuevaObservacion = trim("Cancelación: " . $validated['motivo']);
        $prestamo->update([
            'estado' => 'cancelado',
            'observaciones' => $observaciones ? $observaciones . "\n\n" . $nuevaObservacion : $nuevaObservacion,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Préstamo cancelado exitosamente',
            'data' => $prestamo
        ]);
    }

    // ===========================
    // API: EXTENDER PRÉSTAMO
    // ===========================
    public function extender(Request $request, Prestamo $prestamo)
    {
        if (!auth()->user()->hasPermission('editar-prestamo')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        if (!in_array($prestamo->estado, ['entregado', 'extendido'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden extender préstamos entregados o ya extendidos'
            ], 422);
        }

        $validated = $request->validate([
            'tipo' => 'required|in:completa,parcial',
            'fecha_nueva' => 'required|date|after:fecha_devolucion_esperada',
            'motivo' => 'required|string|max:500',
            'items_ids' => 'nullable|array',
            'items_ids.*' => 'exists:prestamo_detalles,id',
        ]);

        if ($validated['tipo'] === 'parcial') {
            $validated['items_ids'] = array_values(array_unique($validated['items_ids'] ?? []));
            if (empty($validated['items_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar al menos un item para una extensión parcial.',
                ], 422);
            }

            $detallesValidos = $prestamo->detalles()->whereIn('id', $validated['items_ids'])->pluck('id')->all();
            if (count($detallesValidos) !== count($validated['items_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Algunos items seleccionados no pertenecen a este préstamo.',
                ], 422);
            }
        }

        $fechaAnterior = $prestamo->fecha_devolucion_esperada;

        PrestamoExtension::create([
            'prestamo_id' => $prestamo->id,
            'aprobado_por' => auth()->id(),
            'tipo' => $validated['tipo'],
            'fecha_anterior' => $fechaAnterior,
            'fecha_nueva' => $validated['fecha_nueva'],
            'motivo' => $validated['motivo'],
            'items_extendidos' => !empty($validated['items_ids']) ? json_encode($validated['items_ids']) : null,
        ]);

        $prestamo->update([
            'estado' => 'extendido',
            'fecha_devolucion_esperada' => $validated['fecha_nueva'],
            'tiene_extension' => true,
            'total_extensiones' => $prestamo->total_extensiones + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Préstamo extendido hasta ' . $validated['fecha_nueva'],
            'data' => $prestamo->fresh()->load('detalles.prestable')
        ]);
    }

    // ===========================
    // API: BUSCAR RESPONSABLE DE DEPARTAMENTO/INSTITUCIÓN
    // ===========================
    public function buscarResponsableDestino(Request $request)
    {
        $request->validate([
            'departamento_id' => 'nullable|exists:departamentos,id',
            'institucion_id' => 'nullable|exists:instituciones,id',
        ]);

        $responsable = null;

        if ($request->departamento_id) {
            $responsable = Responsable::where('departamento_id', $request->departamento_id)
                ->where('activo', true)
                ->first();
        } elseif ($request->institucion_id) {
            $responsable = Responsable::where('institucion_id', $request->institucion_id)
                ->whereNull('departamento_id')
                ->where('activo', true)
                ->first();
        }

        return response()->json([
            'success' => true,
            'responsable' => $responsable ? [
                'id' => $responsable->id,
                'nombre' => $responsable->nombre,
                'documento' => $responsable->documento,
                'cargo' => $responsable->cargo,
                'telefono' => $responsable->telefono,
                'email' => $responsable->email,
            ] : null
        ]);
    }

    // ===========================
    // API: BUSCAR ACTIVOS/COMPONENTES DISPONIBLES
    // ===========================
    public function buscarItems(Request $request)
    {
        $request->validate([
            'buscar' => 'nullable|string|max:100',
            'tipo' => 'nullable|in:activo,componente,ambos',
        ]);

        $resultados = [];
        $tipo = $request->tipo ?? 'ambos';
        $buscar = $request->buscar;

        if ($tipo === 'ambos' || $tipo === 'activo') {
            $activos = Activo::with(['modelo.marca', 'modelo.categoria'])
                ->when($buscar, function($q) use ($buscar) {
                    $q->where(function($q) use ($buscar) {
                        $q->where('serial', 'ILIKE', "%{$buscar}%")
                          ->orWhereHas('modelo', function($q) use ($buscar) {
                              $q->where('nombre', 'ILIKE', "%{$buscar}%");
                          })
                          ->orWhereHas('modelo.marca', function($q) use ($buscar) {
                              $q->where('nombre', 'ILIKE', "%{$buscar}%");
                          })
                          ->orWhereHas('modelo.categoria', function($q) use ($buscar) {
                              $q->where('nombre', 'ILIKE', "%{$buscar}%");
                          });
                    });
                })
                ->disponibles()
                ->limit(15)
                ->get();

            foreach ($activos as $activo) {
                $resultados[] = [
                    'id' => $activo->id,
                    'tipo' => 'activo',
                    'prestable_type' => Activo::class,
                    'nombre' => trim(($activo->modelo->marca->nombre ?? '') . ' ' . ($activo->modelo->nombre ?? '') . ' ' . $activo->serial),
                    'serial' => $activo->serial,
                    'marca' => $activo->modelo->marca->nombre ?? '',
                    'modelo' => $activo->modelo->nombre ?? '',
                    'categoria' => $activo->modelo->categoria->nombre ?? '',
                ];
            }
        }

        if ($tipo === 'ambos' || $tipo === 'componente') {
            $componentes = Componente::with('modeloComponente')
                ->when($buscar, function($q) use ($buscar) {
                    $q->where(function($q) use ($buscar) {
                        $q->where('serial', 'ILIKE', "%{$buscar}%")
                          ->orWhere('tipo', 'ILIKE', "%{$buscar}%")
                          ->orWhere('marca', 'ILIKE', "%{$buscar}%")
                          ->orWhere('modelo', 'ILIKE', "%{$buscar}%");
                    });
                })
                ->enBodega()
                ->limit(15)
                ->get();

            foreach ($componentes as $componente) {
                $resultados[] = [
                    'id' => $componente->id,
                    'tipo' => 'componente',
                    'prestable_type' => Componente::class,
                    'nombre' => trim($componente->tipo . ' ' . $componente->marca . ' ' . $componente->modelo . ' ' . $componente->serial),
                    'serial' => $componente->serial,
                    'marca' => $componente->marca,
                    'modelo' => $componente->modelo,
                    'categoria' => '',
                ];
            }
        }

        if ($buscar) {
            $buscarLower = strtolower($buscar);
            usort($resultados, function($a, $b) use ($buscarLower) {
                $aScore = 0;
                $bScore = 0;

                if (strpos(strtolower($a['serial'] ?? ''), $buscarLower) !== false) $aScore += 10;
                if (strpos(strtolower($b['serial'] ?? ''), $buscarLower) !== false) $bScore += 10;

                if (strpos(strtolower($a['modelo'] ?? ''), $buscarLower) !== false) $aScore += 5;
                if (strpos(strtolower($b['modelo'] ?? ''), $buscarLower) !== false) $bScore += 5;

                if (strpos(strtolower($a['marca'] ?? ''), $buscarLower) !== false) $aScore += 3;
                if (strpos(strtolower($b['marca'] ?? ''), $buscarLower) !== false) $bScore += 3;

                return $bScore - $aScore;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $resultados
        ]);
    }
}
