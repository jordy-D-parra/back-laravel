<?php
// app/Http/Controllers/Admin/UbicacionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Estado;
use App\Models\Municipio;
use App\Models\Parroquia;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    public function getEstados()
    {
        return response()->json([
            'success' => true,
            'data' => Estado::activos()->orderBy('nombre')->get()
        ]);
    }

    public function getMunicipios($estadoId)
    {
        return response()->json([
            'success' => true,
            'data' => Municipio::where('estado_id', $estadoId)
                ->activos()
                ->orderBy('nombre')
                ->get()
        ]);
    }

    public function getParroquias($municipioId)
    {
        return response()->json([
            'success' => true,
            'data' => Parroquia::where('municipio_id', $municipioId)
                ->activos()
                ->orderBy('nombre')
                ->get()
        ]);
    }
}
