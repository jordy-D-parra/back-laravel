<?php

namespace App\Http\Controllers\Admin;  // ← CAMBIA ESTA LÍNEA

use App\Http\Controllers\Controller;    // ← AÑADE ESTA LÍNEA
use App\Models\Prestamo;
use App\Models\Responsable;
use App\Models\Activo;
use Illuminate\Http\Request;

class PrestamoController extends Controller
{
    /**
     * Muestra la vista principal de préstamos.
     */
    public function index()
    {
        // Opcional: puedes pasar datos necesarios si los solicita el blade (Responsables/Activos para modales/filtros, etc)
        return view('admin.prestamo.index');
    }

    /**
     * Retorna los préstamos en formato JSON para poblar la tabla dinámicamente.
     */
    public function listar(Request $request)
    {
        $query = Prestamo::with(['responsable', 'activo']);

        // Búsqueda y filtros
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('responsable', function($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%$buscar%");
            })->orWhereHas('activo', function($q) use ($buscar) {
                $q->where('serial', 'LIKE', "%$buscar%");
            });
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $prestamos = $query->latest()->get();
        return response()->json($prestamos);
    }

    /**
     * Guarda un nuevo préstamo o actualiza si trae ID.
     */
    public function store(Request $request)
    {
        $request->validate([
            'responsable_id' => 'required|exists:responsables,id',
            'activo_id' => 'required|exists:activos,id',
            'fecha_salida' => 'nullable|date',
            'fecha_devolucion' => 'nullable|date|after_or_equal:fecha_salida',
            'estado' => 'required|in:pendiente,entregado,vencido,devuelto',
            'observaciones' => 'nullable|string|max:500',
        ]);
        
        $prestamo = Prestamo::updateOrCreate(
            ['id' => $request->id],
            $request->only([
                'responsable_id', 
                'activo_id', 
                'fecha_salida', 
                'fecha_devolucion', 
                'estado', 
                'observaciones'
            ])
        );

        return response()->json(['success' => true, 'prestamo' => $prestamo]);
    }

    /**
     * Muestra el detalle de un préstamo.
     */
    public function show($id)
    {
        $prestamo = Prestamo::with(['responsable', 'activo'])->findOrFail($id);
        return response()->json($prestamo);
    }

    /**
     * Elimina un préstamo.
     */
    public function destroy($id)
    {
        $prestamo = Prestamo::findOrFail($id);
        $prestamo->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Devuelve responsables y activos para rellenar los selects del modal.
     */
    public function datosForm()
    {
        $responsables = Responsable::orderBy('nombre')->get();
        $activos = Activo::orderBy('serial')->get();
        return response()->json([
            'responsables' => $responsables,
            'activos' => $activos,
        ]);
    }
}