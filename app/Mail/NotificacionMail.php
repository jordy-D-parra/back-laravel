<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Notificacion;

class NotificacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $titulo;
    public string $mensaje;
    public string $nombreDestinatario;

    /**
     * Constructor flexible: acepta 3 argumentos (titulo, mensaje, nombre)
     * o 2 argumentos (Notificacion, nombre).
     *
     * @param string|Notificacion $titulo
     * @param string $mensaje
     * @param string $nombreDestinatario
     */
    public function __construct(
        $titulo,
        string $mensaje = '',
        string $nombreDestinatario = 'Usuario'
    ) {
        if ($titulo instanceof Notificacion) {
            // Caso: new NotificacionMail($notificacion, $nombre)
            $this->titulo = $titulo->titulo ?? 'Notificación';
            $this->mensaje = $titulo->mensaje ?? '';
            $this->nombreDestinatario = $mensaje !== '' ? $mensaje : 'Usuario';
        } else {
            // Caso: new NotificacionMail($titulo, $mensaje, $nombre)
            $this->titulo = (string) $titulo;
            $this->mensaje = $mensaje;
            $this->nombreDestinatario = $nombreDestinatario;
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->titulo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notificacion',
            with: [
                'titulo' => $this->titulo,
                'mensaje' => $this->mensaje,
                'nombreDestinatario' => $this->nombreDestinatario,
            ],
        );
    }
}