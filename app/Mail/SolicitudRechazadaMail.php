<?php

namespace App\Mail;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudRechazadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Solicitud $solicitud,
        public string $motivo
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Solicitud Rechazada - #' . $this->solicitud->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud-rechazada',
            with: [
                'solicitud' => $this->solicitud,
                'motivo' => $this->motivo,
            ],
        );
    }
}