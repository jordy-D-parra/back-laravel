<?php

namespace App\Mail;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class ActaRetiroMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Solicitud $solicitud,
        public $pdf,
        public string $mensaje
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📄 Acta de Retiro - Solicitud Aprobada #' . $this->solicitud->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.acta-retiro',
            with: [
                'solicitud' => $this->solicitud,
                'mensaje' => $this->mensaje,
            ]
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => $this->pdf->output(),
                'Acta_Retiro_' . $this->solicitud->id . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}