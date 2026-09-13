<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('ver-dashboard')) {
            abort(403, 'No tienes permiso para ver el dashboard');
        }

        $usuario = Auth::user();

        return view('dashboard', compact('usuario'));
    }
}
