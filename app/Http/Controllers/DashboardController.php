<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Prestamo;
use App\Models\NotificacionSistema;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $stats = [
            'mis_solicitudes' => Solicitud::where('id_solicitante', $user->id)->count(),
            'solicitudes_pendientes' => Solicitud::where('id_solicitante', $user->id)->where('estado_solicitud', 'pendiente')->count(),
            'solicitudes_aprobadas' => Solicitud::where('id_solicitante', $user->id)->where('estado_solicitud', 'aprobada')->count(),
            'prestamos_activos' => Prestamo::whereHas('solicitud', function($q) use ($user) {
                $q->where('id_solicitante', $user->id);
            })->where('estado', 'activo')->count(),
        ];

        $notificaciones = NotificacionSistema::where('usuario_id', $user->id)
            ->where('leida', false)
            ->orderBy('fecha_envio', 'desc')
            ->take(10)
            ->get();

        $notificacionesCount = $notificaciones->count();

        return view('dashboard', compact('stats', 'notificaciones', 'notificacionesCount'));
    }
}
