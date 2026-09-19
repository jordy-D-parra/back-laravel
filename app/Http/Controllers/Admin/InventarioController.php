<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Categoria;
use App\Models\Estatus;
use App\Models\TipoActivo;   // ✅ IMPORT NECESARIO

class InventarioController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('ver-activos') &&
            !auth()->user()->hasPermission('ver-componentes')) {
            abort(403, 'No tienes permiso para ver el inventario');
        }

        // ============================================================
        // ESTADÍSTICAS
        // ============================================================
        $totalActivos      = Activo::count();
        $totalComponentes  = Componente::count();
        $componentesBodega = Componente::enBodega()->count();
        $activosPrestados  = Activo::whereHas('estatus', fn($q) =>
            $q->where('descripcion', 'Prestado'))->count();

        // ============================================================
        // DATOS PARA LOS FILTROS
        // ============================================================

        // ✅ Categorías (para el select de filtros y del modal)
        $categorias = Categoria::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        // ✅ Estatus (para los selects)
        $estatusList = Estatus::orderBy('descripcion')
            ->get(['id', 'descripcion', 'color_badge']);

        // ✅ NUEVO: Tipos de activo (los que la vista espera como $tiposActivo)
        $tiposActivo = TipoActivo::orderBy('nombre')
            ->get(['id', 'nombre', 'categoria']);

        // Lista de tipos de componentes (valores únicos)
        $tiposComponentes = Componente::select('tipo')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo')
            ->filter()
            ->values();

        // Estados de componentes (fijos, según el modelo)
        $estadosComponentes = [
            ['valor' => 'en_bodega',    'label' => 'En Bodega'],
            ['valor' => 'instalado',    'label' => 'Instalado'],
            ['valor' => 'prestado',     'label' => 'Prestado'],
            ['valor' => 'en_reparacion','label' => 'En Reparación'],
            ['valor' => 'desechado',    'label' => 'Desechado'],
        ];

        return view('admin.inventario.index', compact(
            'totalActivos',
            'totalComponentes',
            'componentesBodega',
            'activosPrestados',
            'categorias',
            'estatusList',
            'tiposActivo',        // ✅ AHORA SÍ SE PASA A LA VISTA
            'tiposComponentes',
            'estadosComponentes'
        ));
    }
}