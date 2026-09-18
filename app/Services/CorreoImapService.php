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

    /**
     * Lee correos nuevos del buzón IMAP.
     *
     * ✅ OPTIMIZACIONES:
     * - Evita reprocesar correos ya existentes (por message_id)
     * - Clasifica correctamente solicitud vs soporte
     * - Marca como leído SOLO si se procesó correctamente
     * - No genera duplicados en correos_recibidos
     */
    public function leerCorreosNuevos(): int
    {
        try {
            $client = Client::account('default');
            $client->connect();

            $folder = $client->getFolder('INBOX');

            // ✅ Solo correos NO vistos (unseen)
            $messages = $folder->query()->unseen()->get();

            $count = 0;
            $errores = 0;

            foreach ($messages as $message) {
                try {
                    if ($this->procesarMensaje($message)) {
                        $count++;
                    }
                } catch (\Throwable $e) {
                    $errores++;
                    Log::error('Error procesando mensaje individual: ' . $e->getMessage());
                }
            }

            $client->disconnect();

            Log::info("📬 Correos procesados: {$count} nuevos, {$errores} errores");

            return $count;
        } catch (\Exception $e) {
            Log::error('Error al leer correos IMAP: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Procesa un mensaje individual.
     *
     * ✅ NO duplica si el message_id ya existe en correos_recibidos
     */
    private function procesarMensaje($message): bool
    {
        try {
            $messageId = $message->getMessageId();

            // ✅ VERIFICACIÓN 1: Si ya existe, marcar como leído y salir
            if (CorreoRecibido::where('message_id', $messageId)->exists()) {
                $message->setFlag('Seen');
                Log::info("⏭️ Correo ya procesado (message_id: {$messageId})");
                return false;
            }

            $from = $message->getFrom()[0] ?? null;
            $fromEmail = $from ? $from->mail : 'desconocido@dominio.com';
            $fromName = $from ? ($from->personal ?? '') : '';
            $subject = $message->getSubject() ?? '(Sin asunto)';
            $bodyText = $message->getTextBody() ?? '';
            $bodyHtml = $message->getHTMLBody() ?? '';

            // ✅ Adjuntos con límite de tamaño (evitar base64 gigante)
            $attachments = [];
            foreach ($message->getAttachments() as $attachment) {
                $size = $attachment->getSize();
                $attachments[] = [
                    'name' => $attachment->getName(),
                    'size' => $size,
                    'size_human' => $this->formatBytes($size),
                ];
            }

            // ✅ CLASIFICACIÓN INTELIGENTE
            $tipo = $this->clasificador->clasificar($subject, $bodyText);

            Log::info("📧 Correo clasificado como: {$tipo}", [
                'subject' => $subject,
                'from' => $fromEmail,
            ]);

            // ✅ EXTRAER DATOS SEGÚN EL TIPO
            if ($tipo === 'soporte') {
                $datosExtraidos = $this->clasificador->extraerDatosSoporte($bodyText);
                $fechaRequerida = $datosExtraidos['fecha_requerida'] ?? null;
            } else {
                $datosExtraidos = $this->clasificador->extraerDatosSolicitud($bodyText);
                $fechaRequerida = $datosExtraidos['fecha_requerida'] ?? null;
            }

            // ✅ CREAR REGISTRO EN correos_recibidos
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

            // ✅ Marcar como leído SOLO tras crear el registro
            $message->setFlag('Seen');

            Log::info("✅ Correo procesado [{$tipo}]: {$subject} de {$fromEmail}");

            return true;
        } catch (\Exception $e) {
            Log::error('Error procesando mensaje: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Formatea bytes a formato legible
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}