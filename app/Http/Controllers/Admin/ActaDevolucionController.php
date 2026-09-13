<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestamo;
use App\Models\Activo;
use App\Models\Componente;
use Illuminate\Http\Request;

class ActaDevolucionController extends Controller
{
    /**
     * Generar acta de devolución desde un préstamo
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

        // Verificar que el préstamo esté devuelto
        if ($prestamo->estado !== 'devuelto') {
            abort(403, 'Solo se pueden generar actas de préstamos devueltos');
        }

        // Obtener el primer item del préstamo (el equipo principal)
        $detalle = $prestamo->detalles->first();
        $activo = null;

        if ($detalle && $detalle->prestable_type === 'App\\Models\\Activo') {
            $activo = Activo::with(['modelo.marca', 'modelo.categoria'])->find($detalle->prestable_id);
        }

        // Agrupar accesorios
        $accesoriosAgrupados = [];
        foreach ($prestamo->detalles as $d) {
            if ($d->prestable_type === 'App\\Models\\Componente') {
                $comp = Componente::find($d->prestable_id);
                if ($comp) {
                    $clave = $comp->tipo . ($comp->marca ? ' ' . $comp->marca : '');
                    if (!isset($accesoriosAgrupados[$clave])) {
                        $accesoriosAgrupados[$clave] = 0;
                    }
                    $accesoriosAgrupados[$clave] += $d->cantidad ?? 1;
                }
            }
        }

        // Construir el texto de accesorios
        $textoAccesorios = '';
        if (count($accesoriosAgrupados) > 0) {
            $lista = [];
            foreach ($accesoriosAgrupados as $nombre => $cantidad) {
                if ($cantidad > 1) {
                    $lista[] = $nombre . ' (' . $cantidad . ')';
                } else {
                    $lista[] = $nombre;
                }
            }
            
            if (count($lista) === 1) {
                $textoAccesorios = 'Con su respectivo ' . $lista[0];
            } else if (count($lista) === 2) {
                $textoAccesorios = 'Con su respectivo ' . implode(' y ', $lista);
            } else {
                $ultimo = array_pop($lista);
                $textoAccesorios = 'Con su respectivo ' . implode(', ', $lista) . ' y ' . $ultimo;
            }
        } else {
            $textoAccesorios = 'Sin accesorios adicionales';
        }

        // Determinar estado de devolución
        $estadoDevolucion = 'Buen estado';
        foreach ($prestamo->detalles as $d) {
            if ($d->estado_devolucion && strpos(strtolower($d->estado_devolucion), 'daño') !== false) {
                $estadoDevolucion = 'Con daños reportados';
                break;
            }
        }

        // Preparar datos para la vista
        $data = [
            'codigo' => $prestamo->codigo,
            'numero_acta' => 'ACTA-DEV-' . date('Ym') . '-' . str_pad($prestamo->id, 4, '0', STR_PAD_LEFT),
            'fecha' => $prestamo->fecha_devolucion_real ? $prestamo->fecha_devolucion_real->format('d/m/Y') : date('d/m/Y'),
            'fecha_prestamo' => $prestamo->fecha_prestamo ? $prestamo->fecha_prestamo->format('d/m/Y') : 'N/A',
            'fecha_devolucion' => $prestamo->fecha_devolucion_real ? $prestamo->fecha_devolucion_real->format('d/m/Y') : date('d/m/Y'),
            'responsable_entrega' => $prestamo->responsableEmisor ? $prestamo->responsableEmisor->nombre : 'Departamento de Informática',
            'responsable_entrega_cargo' => $prestamo->responsableEmisor ? $prestamo->responsableEmisor->cargo : 'Director de Informática',
            'responsable_devuelve' => $prestamo->responsableReceptor ? $prestamo->responsableReceptor->nombre : 'No especificado',
            'responsable_devuelve_cargo' => $prestamo->responsableReceptor ? $prestamo->responsableReceptor->cargo : '',
            'institucion' => $prestamo->destino_nombre ?? 'No especificada',
            'serial' => $activo ? $activo->serial : 'N/A',
            'marca' => $activo && $activo->modelo && $activo->modelo->marca ? $activo->modelo->marca->nombre : 'N/A',
            'modelo' => $activo && $activo->modelo ? $activo->modelo->nombre : 'N/A',
            'accesorios' => $textoAccesorios,
            'estado_devolucion' => $estadoDevolucion,
            'observaciones' => $prestamo->observaciones ?? 'Sin observaciones adicionales.',
            'items' => $prestamo->detalles->map(function($detalle) {
                return [
                    'nombre' => $detalle->nombre_item ?? 'Item',
                    'cantidad' => $detalle->cantidad ?? 1,
                    'estado_entrega' => $detalle->estado_entrega ?? 'Buen estado',
                    'estado_devolucion' => $detalle->estado_devolucion ?? 'Buen estado'
                ];
            })
        ];

        // Retornar la vista del acta
        return view('admin.actas.devolucion-pdf', compact('data', 'prestamo'));
    }

    /**
     * Imprimir acta de devolución
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

        if ($prestamo->estado !== 'devuelto') {
            abort(403, 'Solo se pueden imprimir actas de préstamos devueltos');
        }

        // Obtener el primer item del préstamo (el equipo principal)
        $detalle = $prestamo->detalles->first();
        $activo = null;

        if ($detalle && $detalle->prestable_type === 'App\\Models\\Activo') {
            $activo = Activo::with(['modelo.marca', 'modelo.categoria'])->find($detalle->prestable_id);
        }

        // Agrupar accesorios
        $accesoriosAgrupados = [];
        foreach ($prestamo->detalles as $d) {
            if ($d->prestable_type === 'App\\Models\\Componente') {
                $comp = Componente::find($d->prestable_id);
                if ($comp) {
                    $clave = $comp->tipo . ($comp->marca ? ' ' . $comp->marca : '');
                    if (!isset($accesoriosAgrupados[$clave])) {
                        $accesoriosAgrupados[$clave] = 0;
                    }
                    $accesoriosAgrupados[$clave] += $d->cantidad ?? 1;
                }
            }
        }

        $textoAccesorios = '';
        if (count($accesoriosAgrupados) > 0) {
            $lista = [];
            foreach ($accesoriosAgrupados as $nombre => $cantidad) {
                if ($cantidad > 1) {
                    $lista[] = $nombre . ' (' . $cantidad . ')';
                } else {
                    $lista[] = $nombre;
                }
            }
            
            if (count($lista) === 1) {
                $textoAccesorios = 'Con su respectivo ' . $lista[0];
            } else if (count($lista) === 2) {
                $textoAccesorios = 'Con su respectivo ' . implode(' y ', $lista);
            } else {
                $ultimo = array_pop($lista);
                $textoAccesorios = 'Con su respectivo ' . implode(', ', $lista) . ' y ' . $ultimo;
            }
        } else {
            $textoAccesorios = 'Sin accesorios adicionales';
        }

        $estadoDevolucion = 'Buen estado';
        foreach ($prestamo->detalles as $d) {
            if ($d->estado_devolucion && strpos(strtolower($d->estado_devolucion), 'daño') !== false) {
                $estadoDevolucion = 'Con daños reportados';
                break;
            }
        }

        $data = [
            'codigo' => $prestamo->codigo,
            'numero_acta' => 'ACTA-DEV-' . date('Ym') . '-' . str_pad($prestamo->id, 4, '0', STR_PAD_LEFT),
            'fecha' => $prestamo->fecha_devolucion_real ? $prestamo->fecha_devolucion_real->format('d/m/Y') : date('d/m/Y'),
            'fecha_prestamo' => $prestamo->fecha_prestamo ? $prestamo->fecha_prestamo->format('d/m/Y') : 'N/A',
            'fecha_devolucion' => $prestamo->fecha_devolucion_real ? $prestamo->fecha_devolucion_real->format('d/m/Y') : date('d/m/Y'),
            'responsable_entrega' => $prestamo->responsableEmisor ? $prestamo->responsableEmisor->nombre : 'Departamento de Informática',
            'responsable_entrega_cargo' => $prestamo->responsableEmisor ? $prestamo->responsableEmisor->cargo : 'Director de Informática',
            'responsable_devuelve' => $prestamo->responsableReceptor ? $prestamo->responsableReceptor->nombre : 'No especificado',
            'responsable_devuelve_cargo' => $prestamo->responsableReceptor ? $prestamo->responsableReceptor->cargo : '',
            'institucion' => $prestamo->destino_nombre ?? 'No especificada',
            'serial' => $activo ? $activo->serial : 'N/A',
            'marca' => $activo && $activo->modelo && $activo->modelo->marca ? $activo->modelo->marca->nombre : 'N/A',
            'modelo' => $activo && $activo->modelo ? $activo->modelo->nombre : 'N/A',
            'accesorios' => $textoAccesorios,
            'estado_devolucion' => $estadoDevolucion,
            'observaciones' => $prestamo->observaciones ?? 'Sin observaciones adicionales.',
            'items' => $prestamo->detalles->map(function($detalle) {
                return [
                    'nombre' => $detalle->nombre_item ?? 'Item',
                    'cantidad' => $detalle->cantidad ?? 1,
                    'estado_entrega' => $detalle->estado_entrega ?? 'Buen estado',
                    'estado_devolucion' => $detalle->estado_devolucion ?? 'Buen estado'
                ];
            })
        ];

        return view('admin.actas.devolucion-pdf', compact('data', 'prestamo'));
    }
}