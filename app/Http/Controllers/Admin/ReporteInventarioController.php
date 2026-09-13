<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Componente;
use Illuminate\Http\Request;

class ReporteInventarioController extends Controller
{
    /**
     * Obtener datos del reporte de inventario (JSON)
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-activos') && !auth()->user()->hasPermission('ver-componentes')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $tipo = $request->get('tipo', 'activos');

        if ($tipo === 'componentes') {
            return $this->getComponentesReporte($request);
        }

        return $this->getActivosReporte($request);
    }

    /**
     * Reporte de Activos
     */
    private function getActivosReporte(Request $request)
    {
        $query = Activo::with([
            'modelo.marca',
            'modelo.categoria',
            'estatus',
            'institucion',
            'responsable'
        ]);

        // Filtros
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('serial', 'ILIKE', "%{$buscar}%")
                  ->orWhereHas('modelo', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$buscar}%"))
                  ->orWhereHas('modelo.marca', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$buscar}%"))
                  ->orWhereHas('modelo.categoria', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$buscar}%"));
            });
        }

        if ($request->filled('estado')) {
            $query->whereHas('estatus', fn($q) => $q->where('descripcion', $request->estado));
        }

        if ($request->filled('categoria')) {
            $query->whereHas('modelo', fn($q) => $q->where('categoria_id', $request->categoria));
        }

        $activos = $query->orderBy('serial')->get();

        // Estadísticas
        $total = $activos->count();
        $disponibles = $activos->filter(fn($a) => $a->estatus?->descripcion === 'Disponible')->count();
        $prestados = $activos->filter(fn($a) => $a->estatus?->descripcion === 'Prestado')->count();
        $reparacion = $activos->filter(fn($a) => $a->estatus?->descripcion === 'En reparación')->count();

        // Transformar datos para la tabla
        $data = $activos->map(function($a) {
            return [
                'serial' => $a->serial,
                'modelo' => $a->modelo?->nombre ?? 'N/A',
                'marca' => $a->modelo?->marca?->nombre ?? 'N/A',
                'categoria' => $a->modelo?->categoria?->nombre ?? 'N/A',
                'estado' => $a->estatus?->descripcion ?? 'N/A',
                'ubicacion' => $a->ubicacion ?? 'No especificada',
                'responsable' => $a->responsable?->nombre ?? 'No asignado',
                'tipo' => 'activo'
            ];
        });

        return response()->json([
            'success' => true,
            'tipo' => 'activos',
            'total' => $total,
            'disponibles' => $disponibles,
            'prestados' => $prestados,
            'reparacion' => $reparacion,
            'data' => $data
        ]);
    }

    /**
     * Reporte de Componentes
     */
    private function getComponentesReporte(Request $request)
    {
        $query = Componente::with([
            'modeloComponente',
            'activo',
            'institucion',
            'responsable'
        ]);

        // Filtros
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('serial', 'ILIKE', "%{$buscar}%")
                  ->orWhere('tipo', 'ILIKE', "%{$buscar}%")
                  ->orWhere('marca', 'ILIKE', "%{$buscar}%")
                  ->orWhere('modelo', 'ILIKE', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('categoria')) {
            $query->whereHas('modeloComponente', fn($q) => $q->where('tipo', 'ILIKE', "%{$request->categoria}%"));
        }

        $componentes = $query->orderBy('tipo')->get();

        // Estadísticas
        $total = $componentes->count();
        $disponibles = $componentes->filter(fn($c) => $c->estado === 'en_bodega')->count();
        $prestados = $componentes->filter(fn($c) => $c->estado === 'prestado')->count();
        $reparacion = $componentes->filter(fn($c) => $c->estado === 'instalado' || $c->estado === 'en_reparacion')->count();

        // Transformar datos para la tabla
        $data = $componentes->map(function($c) {
            return [
                'serial' => $c->serial ?? 'N/A',
                'tipo' => $c->tipo,
                'marca' => $c->marca ?? 'N/A',
                'modelo' => $c->modelo ?? 'N/A',
                'categoria' => $c->modeloComponente?->tipo ?? 'N/A',
                'estado' => $this->getEstadoComponenteLabel($c->estado),
                'ubicacion' => $c->ubicacion ?? ($c->activo?->ubicacion ?? 'No especificada'),
                'responsable' => $c->responsable?->nombre ?? 'No asignado',
                'tipo_item' => 'componente',
                'capacidad' => $c->capacidad ?? 'N/A',
                'activo_serial' => $c->activo?->serial ?? 'No instalado'
            ];
        });

        return response()->json([
            'success' => true,
            'tipo' => 'componentes',
            'total' => $total,
            'disponibles' => $disponibles,
            'prestados' => $prestados,
            'reparacion' => $reparacion,
            'data' => $data
        ]);
    }

    /**
     * Obtener etiqueta legible del estado del componente
     */
    private function getEstadoComponenteLabel($estado)
    {
        $map = [
            'en_bodega' => 'En Bodega',
            'instalado' => 'Instalado',
            'prestado' => 'Prestado',
            'en_reparacion' => 'En Reparación',
            'desechado' => 'Desechado'
        ];
        return $map[$estado] ?? $estado;
    }

    /**
     * Obtener color para el estado del componente
     */
    private function getEstadoColorComponente($estado)
    {
        $map = [
            'en_bodega' => 'secondary',
            'instalado' => 'primary',
            'prestado' => 'warning',
            'en_reparacion' => 'danger',
            'desechado' => 'dark'
        ];
        return $map[$estado] ?? 'secondary';
    }

    /**
     * Exportar reporte a PDF
     */
    public function exportarPdf(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-activos') && !auth()->user()->hasPermission('ver-componentes')) {
            abort(403, 'No autorizado');
        }

        $tipo = $request->get('tipo', 'activos');

        // Obtener datos según el tipo
        if ($tipo === 'componentes') {
            $data = $this->getComponentesData($request);
            $data['titulo'] = 'Reporte de Componentes';
            $data['tipo'] = 'componentes';
        } else {
            $data = $this->getActivosData($request);
            $data['titulo'] = 'Reporte de Inventario';
            $data['tipo'] = 'activos';
        }

        return view('admin.reportes.inventario-pdf', $data);
    }

    /**
     * Obtener datos de activos para PDF
     */
    private function getActivosData(Request $request)
    {
        $query = Activo::with([
            'modelo.marca',
            'modelo.categoria',
            'estatus',
            'institucion',
            'responsable'
        ]);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('serial', 'ILIKE', "%{$buscar}%")
                  ->orWhereHas('modelo', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$buscar}%"))
                  ->orWhereHas('modelo.marca', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$buscar}%"))
                  ->orWhereHas('modelo.categoria', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$buscar}%"));
            });
        }

        if ($request->filled('estado')) {
            $query->whereHas('estatus', fn($q) => $q->where('descripcion', $request->estado));
        }

        if ($request->filled('categoria')) {
            $query->whereHas('modelo', fn($q) => $q->where('categoria_id', $request->categoria));
        }

        $activos = $query->orderBy('serial')->get();

        // Estadísticas
        $disponibles = $activos->filter(fn($a) => $a->estatus?->descripcion === 'Disponible')->count();
        $prestados = $activos->filter(fn($a) => $a->estatus?->descripcion === 'Prestado')->count();
        $reparacion = $activos->filter(fn($a) => $a->estatus?->descripcion === 'En reparación')->count();

        return [
            'tipo' => 'activos',
            'total' => $activos->count(),
            'activos' => $activos,
            'disponibles' => $disponibles,
            'prestados' => $prestados,
            'reparacion' => $reparacion,
            'filtros' => $request->all(),
            'fecha_generacion' => now()->format('d/m/Y H:i')
        ];
    }

    /**
     * Obtener datos de componentes para PDF
     */
    private function getComponentesData(Request $request)
    {
        $query = Componente::with([
            'modeloComponente',
            'activo',
            'institucion',
            'responsable'
        ]);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('serial', 'ILIKE', "%{$buscar}%")
                  ->orWhere('tipo', 'ILIKE', "%{$buscar}%")
                  ->orWhere('marca', 'ILIKE', "%{$buscar}%")
                  ->orWhere('modelo', 'ILIKE', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('categoria')) {
            $query->whereHas('modeloComponente', fn($q) => $q->where('tipo', 'ILIKE', "%{$request->categoria}%"));
        }

        $componentes = $query->orderBy('tipo')->get();

        // Estadísticas
        $disponibles = $componentes->filter(fn($c) => $c->estado === 'en_bodega')->count();
        $prestados = $componentes->filter(fn($c) => $c->estado === 'prestado')->count();
        $reparacion = $componentes->filter(fn($c) => $c->estado === 'instalado' || $c->estado === 'en_reparacion')->count();

        return [
            'tipo' => 'componentes',
            'total' => $componentes->count(),
            'componentes' => $componentes,
            'disponibles' => $disponibles,
            'prestados' => $prestados,
            'reparacion' => $reparacion,
            'filtros' => $request->all(),
            'fecha_generacion' => now()->format('d/m/Y H:i')
        ];
    }

    /**
     * Exportar reporte a Excel (CSV)
     */
    public function exportarExcel(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-activos') && !auth()->user()->hasPermission('ver-componentes')) {
            abort(403, 'No autorizado');
        }

        $tipo = $request->get('tipo', 'activos');

        if ($tipo === 'componentes') {
            return $this->exportarComponentesExcel($request);
        }

        return $this->exportarActivosExcel($request);
    }

    /**
     * Exportar activos a Excel
     */
    private function exportarActivosExcel(Request $request)
    {
        $query = Activo::with([
            'modelo.marca',
            'modelo.categoria',
            'estatus',
            'institucion',
            'responsable'
        ]);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('serial', 'ILIKE', "%{$buscar}%")
                  ->orWhereHas('modelo', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$buscar}%"))
                  ->orWhereHas('modelo.marca', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$buscar}%"))
                  ->orWhereHas('modelo.categoria', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$buscar}%"));
            });
        }

        if ($request->filled('estado')) {
            $query->whereHas('estatus', fn($q) => $q->where('descripcion', $request->estado));
        }

        if ($request->filled('categoria')) {
            $query->whereHas('modelo', fn($q) => $q->where('categoria_id', $request->categoria));
        }

        $activos = $query->orderBy('serial')->get();

        return $this->generarCsv($activos, 'activos');
    }

    /**
     * Exportar componentes a Excel
     */
    private function exportarComponentesExcel(Request $request)
    {
        $query = Componente::with([
            'modeloComponente',
            'activo',
            'institucion',
            'responsable'
        ]);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('serial', 'ILIKE', "%{$buscar}%")
                  ->orWhere('tipo', 'ILIKE', "%{$buscar}%")
                  ->orWhere('marca', 'ILIKE', "%{$buscar}%")
                  ->orWhere('modelo', 'ILIKE', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('categoria')) {
            $query->whereHas('modeloComponente', fn($q) => $q->where('tipo', 'ILIKE', "%{$request->categoria}%"));
        }

        $componentes = $query->orderBy('tipo')->get();

        return $this->generarCsv($componentes, 'componentes');
    }

    /**
     * Generar archivo CSV
     */
    private function generarCsv($items, $tipo)
    {
        $filename = 'reporte-' . $tipo . '-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($items, $tipo) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($tipo === 'componentes') {
                fputcsv($file, ['Tipo', 'Marca', 'Modelo', 'Serial', 'Capacidad', 'Estado', 'Ubicación', 'Activo', 'Responsable'], ';');
                foreach ($items as $item) {
                    fputcsv($file, [
                        $item->tipo,
                        $item->marca ?? 'N/A',
                        $item->modelo ?? 'N/A',
                        $item->serial ?? 'N/A',
                        $item->capacidad ?? 'N/A',
                        $this->getEstadoComponenteLabel($item->estado),
                        $item->ubicacion ?? ($item->activo?->ubicacion ?? 'No especificada'),
                        $item->activo?->serial ?? 'No instalado',
                        $item->responsable?->nombre ?? 'No asignado',
                    ], ';');
                }
            } else {
                fputcsv($file, ['Serial', 'Modelo', 'Marca', 'Categoría', 'Estado', 'Ubicación', 'Responsable'], ';');
                foreach ($items as $item) {
                    fputcsv($file, [
                        $item->serial,
                        $item->modelo?->nombre ?? 'N/A',
                        $item->modelo?->marca?->nombre ?? 'N/A',
                        $item->modelo?->categoria?->nombre ?? 'N/A',
                        $item->estatus?->descripcion ?? 'N/A',
                        $item->ubicacion ?? 'No especificada',
                        $item->responsable?->nombre ?? 'No asignado',
                    ], ';');
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}