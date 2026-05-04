<?php
// app/Http/Controllers/NotificacionController.php

namespace App\Http\Controllers;

use App\Models\NotificacionSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    // Obtener notificaciones del usuario autenticado
    public function index(Request $request)
    {
        $query = NotificacionSistema::where('usuario_id', Auth::id());

        // Filtrar no leídas
        if ($request->no_leidas) {
            $query->where('leida', false);
        }

        $notificaciones = $query->orderBy('fecha_envio', 'desc')->paginate(20);

        return response()->json($notificaciones);
    }

    // Contar notificaciones no leídas
    public function contadorNoLeidas()
    {
        $contador = NotificacionSistema::where('usuario_id', Auth::id())
                                        ->where('leida', false)
                                        ->count();

        return response()->json(['no_leidas' => $contador]);
    }

    // Marcar una notificación como leída
    public function marcarComoLeida($id)
    {
        $notificacion = NotificacionSistema::where('usuario_id', Auth::id())
                                           ->findOrFail($id);
        $notificacion->marcarComoLeida();

        return response()->json(['message' => 'Notificación marcada como leída']);
    }

    // Marcar todas como leídas
    public function marcarTodasLeidas()
    {
        NotificacionSistema::where('usuario_id', Auth::id())
                          ->where('leida', false)
                          ->update(['leida' => true]);

        return response()->json(['message' => 'Todas las notificaciones marcadas como leídas']);
    }

    // Eliminar notificación
    public function destroy($id)
    {
        $notificacion = NotificacionSistema::where('usuario_id', Auth::id())
                                           ->findOrFail($id);
        $notificacion->delete();

        return response()->json(['message' => 'Notificación eliminada']);
    }
}
