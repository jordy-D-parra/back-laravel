<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestamo;
use App\Models\Activo;
use App\Models\Componente;
use Illuminate\Http\Request;

class ActaEntregaController extends Controller
{
    /**
     * Generar acta de entrega desde un préstamo
     */
    public function generarDesdePrestamo(Request $request)
    {
        $prestamoId = $request->input('prestamo_id');

        if (!$prestamoId) {
            abort(404, 'Préstamo no especificado');
        }

        // Obtener el préstamo con todas sus relaciones
        $prestamo = Prestamo::with([
            'responsableReceptor',
            'responsableEmisor',
            'departamento',
            'institucion',
            'detalles.prestable'
        ])->findOrFail($prestamoId);

        // Verificar que el préstamo esté entregado
        if (!in_array($prestamo->estado, ['entregado', 'extendido'])) {
            abort(403, 'Solo se pueden generar actas de préstamos entregados');
        }

        // Obtener el primer item del préstamo (el equipo principal)
        $detalle = $prestamo->detalles->first();
        $activo = null;

        if ($detalle && $detalle->prestable_type === 'App\\Models\\Activo') {
            $activo = Activo::with(['modelo.marca', 'modelo.categoria'])->find($detalle->prestable_id);
        }

        // Obtener accesorios (componentes)
        $accesorios = [];
        foreach ($prestamo->detalles as $d) {
            if ($d->prestable_type === 'App\\Models\\Componente') {
                $comp = Componente::find($d->prestable_id);
                if ($comp) {
                    $accesorios[] = $comp->tipo . ($comp->marca ? ' ' . $comp->marca : '');
                }
            }
        }

        // Construir el texto de accesorios
        $textoAccesorios = '';
        if (count($accesorios) > 0) {
            if (count($accesorios) === 1) {
                $textoAccesorios = 'Con su respectivo ' . $accesorios[0];
            } else if (count($accesorios) === 2) {
                $textoAccesorios = 'Con su respectivo ' . implode(' y ', $accesorios);
            } else {
                $textoAccesorios = 'Con su respectivo ' . implode(', ', array_slice($accesorios, 0, 2)) . ' y otros componentes';
            }
        } else {
            $textoAccesorios = 'Sin accesorios adicionales';
        }

        // Datos para el acta
        $data = [
            'codigo' => $prestamo->codigo,
            'numero_acta' => 'ACTA-' . date('Ym') . '-' . str_pad($prestamo->id, 4, '0', STR_PAD_LEFT),
            'fecha' => $prestamo->fecha_prestamo ? $prestamo->fecha_prestamo->format('d/m/Y') : date('d/m/Y'),
            'fecha_original' => $prestamo->fecha_prestamo ?? now(),
            'responsable_entrega' => $prestamo->responsableEmisor ? $prestamo->responsableEmisor->nombre : 'Departamento de Informática',
            'responsable_entrega_cargo' => $prestamo->responsableEmisor ? $prestamo->responsableEmisor->cargo : 'Director de Informática',
            'responsable_recibe' => $prestamo->responsableReceptor ? $prestamo->responsableReceptor->nombre : 'No especificado',
            'responsable_recibe_cargo' => $prestamo->responsableReceptor ? $prestamo->responsableReceptor->cargo : '',
            'institucion' => $prestamo->destino_nombre ?? 'No especificada',
            'serial' => $activo ? $activo->serial : 'N/A',
            'marca' => $activo && $activo->modelo && $activo->modelo->marca ? $activo->modelo->marca->nombre : 'N/A',
            'modelo' => $activo && $activo->modelo ? $activo->modelo->nombre : 'N/A',
            'accesorios' => $textoAccesorios,
            'trabajo_realizado' => $prestamo->observaciones ?? 'Se realizó la entrega del equipo en buen estado.',
            'observaciones' => '',
            'items' => $prestamo->detalles->map(function($detalle) {
                return [
                    'nombre' => $detalle->nombre_item ?? 'Item',
                    'cantidad' => $detalle->cantidad ?? 1,
                    'estado' => $detalle->estado_entrega ?? 'Buen estado'
                ];
            })
        ];

        // Retornar la vista del acta
        return view('admin.actas.entrega-pdf', compact('data', 'prestamo'));
    }

    /**
     * Imprimir acta (vista de impresión)
     */
    public function imprimir($id)
    {
        $prestamo = Prestamo::with([
            'responsableReceptor',
            'responsableEmisor',
            'departamento',
            'institucion',
            'detalles.prestable'
        ])->findOrFail($id);

        if (!in_array($prestamo->estado, ['entregado', 'extendido'])) {
            abort(403, 'Solo se pueden imprimir actas de préstamos entregados');
        }

        $detalle = $prestamo->detalles->first();
        $activo = null;

        if ($detalle && $detalle->prestable_type === 'App\\Models\\Activo') {
            $activo = Activo::with(['modelo.marca', 'modelo.categoria'])->find($detalle->prestable_id);
        }

        $accesorios = [];
        foreach ($prestamo->detalles as $d) {
            if ($d->prestable_type === 'App\\Models\\Componente') {
                $comp = Componente::find($d->prestable_id);
                if ($comp) {
                    $accesorios[] = $comp->tipo . ($comp->marca ? ' ' . $comp->marca : '');
                }
            }
        }

        $textoAccesorios = '';
        if (count($accesorios) > 0) {
            if (count($accesorios) === 1) {
                $textoAccesorios = 'Con su respectivo ' . $accesorios[0];
            } else if (count($accesorios) === 2) {
                $textoAccesorios = 'Con su respectivo ' . implode(' y ', $accesorios);
            } else {
                $textoAccesorios = 'Con su respectivo ' . implode(', ', array_slice($accesorios, 0, 2)) . ' y otros componentes';
            }
        } else {
            $textoAccesorios = 'Sin accesorios adicionales';
        }

        $data = [
            'codigo' => $prestamo->codigo,
            'numero_acta' => 'ACTA-' . date('Ym') . '-' . str_pad($prestamo->id, 4, '0', STR_PAD_LEFT),
            'fecha' => $prestamo->fecha_prestamo ? $prestamo->fecha_prestamo->format('d/m/Y') : date('d/m/Y'),
            'fecha_original' => $prestamo->fecha_prestamo ?? now(),
            'responsable_entrega' => $prestamo->responsableEmisor ? $prestamo->responsableEmisor->nombre : 'Departamento de Informática',
            'responsable_entrega_cargo' => $prestamo->responsableEmisor ? $prestamo->responsableEmisor->cargo : 'Director de Informática',
            'responsable_recibe' => $prestamo->responsableReceptor ? $prestamo->responsableReceptor->nombre : 'No especificado',
            'responsable_recibe_cargo' => $prestamo->responsableReceptor ? $prestamo->responsableReceptor->cargo : '',
            'institucion' => $prestamo->destino_nombre ?? 'No especificada',
            'serial' => $activo ? $activo->serial : 'N/A',
            'marca' => $activo && $activo->modelo && $activo->modelo->marca ? $activo->modelo->marca->nombre : 'N/A',
            'modelo' => $activo && $activo->modelo ? $activo->modelo->nombre : 'N/A',
            'accesorios' => $textoAccesorios,
            'trabajo_realizado' => $prestamo->observaciones ?? 'Se realizó la entrega del equipo en buen estado.',
            'observaciones' => '',
            'items' => $prestamo->detalles->map(function($detalle) {
                return [
                    'nombre' => $detalle->nombre_item ?? 'Item',
                    'cantidad' => $detalle->cantidad ?? 1,
                    'estado' => $detalle->estado_entrega ?? 'Buen estado'
                ];
            })
        ];

        return view('admin.actas.entrega-pdf', compact('data', 'prestamo'));
    }
}
