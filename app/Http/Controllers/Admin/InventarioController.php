<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Estatus;

class InventarioController extends Controller
{
    /**
     * Vista principal del módulo de inventario.
     */
    public function index()
    {
        $totalActivos = Activo::count();
        $totalComponentes = Componente::count();
        $componentesBodega = Componente::enBodega()->count();
        $activosPrestados = Activo::whereHas('estatus', fn($q) => $q->where('descripcion', 'Prestado'))->count();

        return view('admin.inventario.index', compact(
            'totalActivos',
            'totalComponentes',
            'componentesBodega',
            'activosPrestados'
        ));
    }
}
