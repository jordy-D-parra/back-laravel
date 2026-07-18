<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Estatus;

class InventarioController extends Controller
{
    public function index()
    {
        // Verificar al menos un permiso de inventario
        if (!auth()->user()->hasPermission('ver-activos') &&
            !auth()->user()->hasPermission('ver-componentes')) {
            abort(403, 'No tienes permiso para ver el inventario');
        }

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
