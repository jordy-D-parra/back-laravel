<?php
// app/Mail/NotificacionMail.php

namespace App\Mail;

use App\Models\Notificacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Notificacion $notificacion,
        public string $destinatario
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notificacion->titulo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notificacion',
            with: [
                'notificacion' => $this->notificacion,
                'destinatario' => $this->destinatario,
            ]
        );
    }
}