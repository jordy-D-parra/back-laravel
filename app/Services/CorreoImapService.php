<?php

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

            // Extraer datos según el tipo
            if ($tipo === 'soporte') {
                $datosExtraidos = $this->clasificador->extraerDatosSoporte($bodyText);
                $fechaRequerida = $datosExtraidos['fecha_requerida'] ?? null;
            } else {
                // Extracción genérica para solicitudes (la que ya tenías)
                $datosExtraidos = $this->extraerDatosSolicitud($bodyText);
                $fechaRequerida = null;
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

    /**
     * Extracción para solicitudes de préstamo (tu lógica original)
     */
    private function extraerDatosSolicitud(string $body): array
    {
        $datos = [
            'prioridad' => 'normal',
            'fecha_requerida' => null,
            'fecha_fin_estimada' => null,
            'justificacion' => null,
            'items' => [],
            'entidad' => null,
        ];

        if (preg_match('/prioridad[:\s]+(baja|normal|alta|urgente)/i', $body, $m)) {
            $datos['prioridad'] = strtolower($m[1]);
        } elseif (preg_match('/(urgente|urge)/i', $body)) {
            $datos['prioridad'] = 'urgente';
        }

        if (preg_match('/(?:fecha\s+requerida|necesito\s+para|para\s+el)[:\s]+(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/i', $body, $m)) {
            $datos['fecha_requerida'] = $this->normalizarFecha($m[1]);
        }

        if (preg_match('/(?:justificaci[oó]n|motivo|raz[oó]n)[:\s]+(.+?)(?:\n\n|$)/is', $body, $m)) {
            $datos['justificacion'] = trim($m[1]);
        } else {
            $datos['justificacion'] = substr(trim($body), 0, 500);
        }

        if (preg_match_all('/(\d+)\s+(?:x\s+)?([a-záéíóúñ\s]+?)(?:\n|,|\.|;|$)/iu', $body, $matches)) {
            foreach ($matches[1] as $i => $cantidad) {
                $descripcion = trim($matches[2][$i]);
                if (strlen($descripcion) > 3 && strlen($descripcion) < 100) {
                    $datos['items'][] = [
                        'cantidad' => (int) $cantidad,
                        'descripcion' => $descripcion,
                        'tipo_item' => $this->detectarTipoItem($descripcion),
                    ];
                }
            }
        }

        return $datos;
    }

    private function normalizarFecha(string $fecha): ?string
    {
        try {
            $fecha = str_replace('/', '-', $fecha);
            $partes = explode('-', $fecha);
            if (count($partes) === 3) {
                if (strlen($partes[0]) === 4) {
                    return sprintf('%04d-%02d-%02d', $partes[0], $partes[1], $partes[2]);
                }
                return sprintf('%04d-%02d-%02d', $partes[2], $partes[1], $partes[0]);
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }

    private function detectarTipoItem(string $descripcion): string
    {
        $descripcion = strtolower($descripcion);
        $activos = ['laptop', 'computadora', 'pc', 'desktop', 'monitor', 'proyector', 'impresora', 'tablet', 'servidor', 'router', 'switch'];
        $componentes = ['mouse', 'teclado', 'cable', 'cargador', 'ram', 'disco', 'batería', 'bateria', 'adaptador', 'usb', 'audífonos', 'audifonos'];

        foreach ($activos as $a) {
            if (strpos($descripcion, $a) !== false) return 'activo';
        }
        foreach ($componentes as $c) {
            if (strpos($descripcion, $c) !== false) return 'componente';
        }
        return 'activo';
    }
}