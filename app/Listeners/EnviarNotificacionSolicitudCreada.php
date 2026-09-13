<?php

namespace App\Listeners;

use App\Models\Usuario;
use App\Notifications\SolicitudCreadaNotification;
use Illuminate\Support\Facades\Notification;

class EnviarNotificacionSolicitudCreada
{
    public function handle($event)
    {
        // Obtener administradores y técnicos
        $admins = Usuario::whereHas('rol', function($query) {
            $query->whereIn('nombre', ['admin', 'super_admin', 'tecnico']);
        })->get();

        // Enviar notificación a todos
        Notification::send($admins, new SolicitudCreadaNotification($event->solicitud));
    }
}
