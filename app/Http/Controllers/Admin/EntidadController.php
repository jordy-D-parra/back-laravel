<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Departamento;
use App\Models\Responsable;
use Illuminate\Http\Request;

class EntidadController extends Controller
{
public function index(Request $request)
{
    $instituciones = Institucion::activas()->orderBy('nombre')->get();

    $totalInstituciones = Institucion::count();
    $totalActivas = Institucion::where('activo', true)->count();
    $totalInactivas = Institucion::where('activo', false)->count();
    $totalDepartamentos = Departamento::count();
    $totalResponsables = Responsable::count();

    return view('admin.entidades.index', compact(
        'instituciones',
        'totalInstituciones',
        'totalActivas',
        'totalInactivas',
        'totalDepartamentos',
        'totalResponsables'
    ));
}
}
