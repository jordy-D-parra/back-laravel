<?php

namespace App\Notifications;

use App\Models\Solicitud;
use App\Models\NotificacionSistema;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SolicitudRechazadaNotification extends Notification
{
    use Queueable;

    protected $solicitud;
    protected $motivo;

    public function __construct(Solicitud $solicitud, $motivo = null)
    {
        $this->solicitud = $solicitud;
        $this->motivo = $motivo;
    }

    // Canales: mail y database
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    // Email para el solicitante
    public function toMail($notifiable)
    {
        $motivoTexto = $this->motivo ?? $this->solicitud->observaciones ?? 'No se especificó un motivo';

        return (new MailMessage)
            ->subject("❌ Solicitud Rechazada - #{$this->solicitud->id}")
            ->greeting("Hola {$notifiable->nombre},")
            ->line("Lamentamos informarte que tu solicitud de préstamo ha sido **RECHAZADA**.")
            ->line("")
            ->line("**Detalles de tu solicitud:**")
            ->line("- **Número de solicitud:** {$this->solicitud->id}")
            ->line("- **Fecha de solicitud:** " . $this->solicitud->fecha_solicitud->format('d/m/Y'))
            ->line("- **Fecha requerida:** " . $this->solicitud->fecha_requerida->format('d/m/Y'))
            ->line("- **Prioridad:** " . strtoupper($this->solicitud->prioridad))
            ->line("")
            ->line("**Motivo del rechazo:**")
            ->line("> {$motivoTexto}")
            ->line("")
            ->line("**¿Qué puedes hacer?**")
            ->line("1. Revisa el motivo del rechazo")
            ->line("2. Corrige los aspectos mencionados")
            ->line("3. Realiza una nueva solicitud con la información corregida")
            ->line("")
            ->action('Ver Detalles de tu Solicitud', url("/solicitudes/{$this->solicitud->id}"))
            ->line("")
            ->line("Si tienes dudas, contacta al área de préstamos.")
            ->salutation("Saludos, Sistema de Préstamos");
    }

    // Notificación en el sistema (base de datos)
    public function toDatabase($notifiable)
    {
        $motivoTexto = $this->motivo ?? $this->solicitud->observaciones ?? 'No se especificó un motivo';

        return NotificacionSistema::create([
            'usuario_id' => $notifiable->id,
            'tipo' => 'solicitud_rechazada',
            'titulo' => '❌ Solicitud Rechazada',
            'mensaje' => "Tu solicitud #{$this->solicitud->id} ha sido rechazada. Motivo: {$motivoTexto}",
            'datos_extra' => [
                'solicitud_id' => $this->solicitud->id,
                'motivo' => $motivoTexto,
                'fecha_rechazo' => now()->format('Y-m-d H:i:s')
            ],
            'fecha_envio' => now()
        ]);
    }

    // Para array (API)
    public function toArray($notifiable)
    {
        return [
            'solicitud_id' => $this->solicitud->id,
            'estado' => 'rechazada',
            'motivo' => $this->motivo ?? $this->solicitud->observaciones,
            'fecha_rechazo' => $this->solicitud->fecha_aprobacion
        ];
    }
}
