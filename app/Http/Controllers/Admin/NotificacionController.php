<?php
// app/Http/Controllers/Admin/NotificacionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    protected NotificacionService $notificacionService;

    public function __construct(NotificacionService $notificacionService)
    {
        $this->notificacionService = $notificacionService;
    }

    public function index()
    {
        $usuario = Auth::user();

        $noLeidas = $this->notificacionService->countNoLeidas($usuario);

        $notificaciones = Notificacion::porUsuario($usuario->id)
            ->orderBy('fecha_envio', 'desc')
            ->paginate(20);

        return view('admin.notificaciones.index', compact('notificaciones', 'noLeidas'));
    }

    public function marcarComoLeida($id)
    {
        $usuario = Auth::user();
        $result = $this->notificacionService->marcarComoLeida($id, $usuario);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Notificación marcada como leída' : 'Error al marcar como leída'
        ]);
    }

    public function marcarTodasComoLeidas()
    {
        $usuario = Auth::user();
        $count = $this->notificacionService->marcarTodasComoLeidas($usuario);

        return response()->json([
            'success' => true,
            'message' => "$count notificaciones marcadas como leídas"
        ]);
    }

    public function obtenerNoLeidas()
    {
        $usuario = Auth::user();
        $noLeidas = $this->notificacionService->getNoLeidas($usuario);

        return response()->json([
            'success' => true,
            'count' => $noLeidas->count(),
            'data' => $noLeidas
        ]);
    }
}