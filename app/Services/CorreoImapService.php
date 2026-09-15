<?php
// app/Services/CorreoImapService.php

namespace App\Services;

use App\Models\CorreoRecibido;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Log;

class CorreoImapService
{
    protected ClasificadorCorreoService $clasificador;

    public function __construct(ClasificadorCorreoService $clasificador)
    {
        $this->clasificador = $clasificador;
    }

    public function leerCorreosNuevos(): int
    {
        try {
            $client = Client::account('default');
            $client->connect();
            $folder = $client->getFolder('INBOX');
            $messages = $folder->query()->unseen()->get();
            $count = 0;

            foreach ($messages as $message) {
                if ($this->procesarMensaje($message)) {
                    $count++;
                }
            }

            $client->disconnect();
            return $count;
        } catch (\Exception $e) {
            Log::error('Error al leer correos IMAP: ' . $e->getMessage());
            return 0;
        }
    }

    private function procesarMensaje($message): bool
    {
        try {
            $messageId = $message->getMessageId();

            if (CorreoRecibido::where('message_id', $messageId)->exists()) {
                $message->setFlag('Seen');
                return false;
            }

            $from = $message->getFrom()[0] ?? null;
            $fromEmail = $from ? $from->mail : 'desconocido@dominio.com';
            $fromName = $from ? ($from->personal ?? '') : '';
            $subject = $message->getSubject() ?? '(Sin asunto)';
            $bodyText = $message->getTextBody() ?? '';
            $bodyHtml = $message->getHTMLBody() ?? '';

            $attachments = [];
            foreach ($message->getAttachments() as $attachment) {
                $attachments[] = [
                    'name' => $attachment->getName(),
                    'size' => $attachment->getSize(),
                ];
            }

            // ========== CLASIFICACIÓN ==========
            $tipo = $this->clasificador->clasificar($subject, $bodyText);

            Log::info("Correo clasificado como: {$tipo}", [
                'subject' => $subject,
                'from' => $fromEmail
            ]);

            // Extraer datos según el tipo
            if ($tipo === 'soporte') {
                $datosExtraidos = $this->clasificador->extraerDatosSoporte($bodyText);
                $fechaRequerida = $datosExtraidos['fecha_requerida'] ?? null;
            } else {
                $datosExtraidos = $this->clasificador->extraerDatosSolicitud($bodyText);
                $fechaRequerida = $datosExtraidos['fecha_requerida'] ?? null;
            }

            CorreoRecibido::create([
                'message_id' => $messageId,
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'subject' => $subject,
                'tipo' => $tipo,
                'body_text' => $bodyText,
                'body_html' => $bodyHtml,
                'attachments' => $attachments,
                'received_at' => now(),
                'leido' => false,
                'procesado' => false,
                'datos_extraidos' => $datosExtraidos,
                'fecha_requerida_entrega' => $fechaRequerida,
            ]);

            $message->setFlag('Seen');

            Log::info("Correo procesado [{$tipo}]: {$subject} de {$fromEmail}");

            return true;
        } catch (\Exception $e) {
            Log::error('Error procesando mensaje: ' . $e->getMessage());
            return false;
        }
    }
}