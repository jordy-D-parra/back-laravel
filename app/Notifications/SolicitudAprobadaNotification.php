<?php
// app/Notifications/SolicitudAprobadaNotification.php

namespace App\Notifications;

use App\Models\Solicitud;
use App\Models\NotificacionSistema;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SolicitudAprobadaNotification extends Notification
{
    protected $solicitud;

    public function __construct(Solicitud $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("✅ ¡Solicitud Aprobada! - {$this->solicitud->prioridad}")
            ->greeting("¡Hola {$notifiable->nombre}!")
            ->line("¡Excelente noticia! Tu solicitud de préstamo ha sido **APROBADA**.")
            ->line("**Detalles de tu solicitud:**")
            ->line("- **Fecha de solicitud:** {$this->solicitud->fecha_solicitud->format('d/m/Y')}")
            ->line("- **Fecha requerida:** {$this->solicitud->fecha_requerida->format('d/m/Y')}")
            ->line("- **Items aprobados:** {$this->solicitud->detalles->sum('cantidad_solicitada')} unidades")
            ->line("**Próximos pasos:**")
            ->line("1. El área de préstamos se contactará contigo")
            ->line("2. Deberás firmar el acta de responsabilidad")
            ->line("3. Se coordinará la entrega de los equipos")
            ->action('Ver Detalles de tu Solicitud', url("/mis-solicitudes/{$this->solicitud->id}"))
            ->line("Gracias por usar nuestro sistema de préstamos.");
    }

    public function toDatabase($notifiable)
    {
        return NotificacionSistema::create([
            'usuario_id' => $notifiable->id,
            'tipo' => 'solicitud_aprobada',
            'titulo' => '✅ Solicitud aprobada',
            'mensaje' => "Tu solicitud #{$this->solicitud->id} ha sido aprobada. Pronto te contactarán para coordinar la entrega.",
            'datos_extra' => [
                'solicitud_id' => $this->solicitud->id,
                'fecha_aprobacion' => now()->format('Y-m-d H:i:s')
            ],
            'fecha_envio' => now()
        ]);
    }
}
