<?php
// app/Notifications/SolicitudCreadaNotification.php

namespace App\Notifications;

use App\Models\Solicitud;
use App\Models\NotificacionSistema;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SolicitudCreadaNotification extends Notification
{
    use Queueable;

    protected $solicitud;

    public function __construct(Solicitud $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    // Canales: mail (email), database (sistema)
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    // Email para el administrador
    public function toMail($notifiable)
    {
        $detallesHtml = $this->generarTablaDetalles();

        return (new MailMessage)
            ->subject("📋 Nueva Solicitud de Préstamo - Prioridad: " . strtoupper($this->solicitud->prioridad))
            ->greeting("Hola {$notifiable->nombre},")
            ->line("Se ha creado una nueva solicitud de préstamo en el sistema.")
            ->line("**Detalles de la solicitud:**")
            ->line("- **Solicitante:** {$this->solicitud->solicitante->nombre} {$this->solicitud->solicitante->apellido}")
            ->line("- **Fecha requerida:** " . $this->solicitud->fecha_requerida->format('d/m/Y'))
            ->line("- **Prioridad:** " . $this->getPrioridadBadge())
            ->line("- **Justificación:** {$this->solicitud->justificacion}")
            ->line("**Items solicitados:**")
            ->line($detallesHtml)
            ->action('Ver Solicitud', url("/admin/solicitudes/{$this->solicitud->id}"))
            ->line("Por favor, revise y procese esta solicitud a la brevedad posible.");
    }

    // Notificación en el sistema (base de datos)
    public function toDatabase($notifiable)
    {
        return NotificacionSistema::create([
            'usuario_id' => $notifiable->id,
            'tipo' => 'solicitud_creada',
            'titulo' => 'Nueva solicitud de préstamo',
            'mensaje' => "{$this->solicitud->solicitante->nombre} ha creado una nueva solicitud (Prioridad: {$this->solicitud->prioridad})",
            'datos_extra' => [
                'solicitud_id' => $this->solicitud->id,
                'solicitante' => $this->solicitud->solicitante->nombre,
                'prioridad' => $this->solicitud->prioridad,
                'fecha_requerida' => $this->solicitud->fecha_requerida->format('Y-m-d')
            ],
            'fecha_envio' => now()
        ]);
    }

    private function getPrioridadBadge()
    {
        return match($this->solicitud->prioridad) {
            'urgente' => '🔴 URGENTE',
            'alta' => '🟠 Alta',
            'normal' => '🟡 Normal',
            'baja' => '🟢 Baja',
            default => $this->solicitud->prioridad
        };
    }

    private function generarTablaDetalles()
    {
        $html = "<table style='width:100%; border-collapse:collapse;'>";
        $html .= "<tr><th style='border:1px solid #ddd; padding:8px;'>Item</th><th style='border:1px solid #ddd; padding:8px;'>Cantidad</th></tr>";

        foreach ($this->solicitud->detalles as $detalle) {
            $nombre = $detalle->tipo_item === 'activo'
                ? $detalle->activo->serial
                : $detalle->periferico->nombre;
            $html .= "<tr>";
            $html .= "<td style='border:1px solid #ddd; padding:8px;'>{$nombre}</td>";
            $html .= "<td style='border:1px solid #ddd; padding:8px; text-align:center;'>{$detalle->cantidad_solicitada}</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";
        return $html;
    }
}
