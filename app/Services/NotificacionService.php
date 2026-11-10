<?php

namespace App\Services;

use App\Mail\NotificacionMail;
use App\Models\Usuario;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
    /**
     * Envía una notificación a un responsable (correo externo).
     */
    public function enviarAResponsable(
        string $email,
        string $nombre,
        string $titulo,
        string $mensaje,
        string $tipo = 'sistema',
        ?string $url = null,
        bool $enviarEmail = true
    ): bool {
        // Validar email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning("📧 [NotificacionService] Email inválido o vacío", [
                'email' => $email,
                'nombre' => $nombre,
                'titulo' => $titulo,
            ]);
            return false;
        }

        Log::info("📧 [NotificacionService] Intentando enviar correo", [
            'email' => $email,
            'nombre' => $nombre,
            'titulo' => $titulo,
            'tipo' => $tipo,
        ]);

        try {
            // Verificar configuración de correo
            $mailer = config('mail.default');
            $fromAddress = config('mail.from.address');
            $smtpHost = config('mail.mailers.smtp.host');
            $smtpPort = config('mail.mailers.smtp.port');
            $smtpEncryption = config('mail.mailers.smtp.encryption');
            $smtpUsername = config('mail.mailers.smtp.username');
            $smtpPasswordLength = strlen(config('mail.mailers.smtp.password') ?? '');

            Log::debug("📧 [NotificacionService] Configuración de correo", [
                'mailer' => $mailer,
                'from_address' => $fromAddress,
                'smtp_host' => $smtpHost,
                'smtp_port' => $smtpPort,
                'smtp_encryption' => $smtpEncryption,
                'smtp_username' => $smtpUsername,
                'smtp_password_length' => $smtpPasswordLength,
            ]);

            if (empty($smtpUsername) || $smtpPasswordLength === 0) {
                Log::error("❌ [NotificacionService] Credenciales SMTP vacías", [
                    'smtp_username' => $smtpUsername,
                    'smtp_password_length' => $smtpPasswordLength,
                ]);
                return false;
            }

            // Enviar el correo
            Mail::to($email)->send(new NotificacionMail(
                $titulo,
                $mensaje,
                $nombre
            ));

            Log::info("✅ [NotificacionService] Correo enviado exitosamente", [
                'email' => $email,
                'titulo' => $titulo,
            ]);

            return true;

        } catch (\Throwable $e) {
            Log::error("❌ [NotificacionService] Error al enviar correo", [
                'email' => $email,
                'nombre' => $nombre,
                'titulo' => $titulo,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'mailer' => config('mail.default'),
                'smtp_host' => config('mail.mailers.smtp.host'),
                'smtp_port' => config('mail.mailers.smtp.port'),
                'smtp_encryption' => config('mail.mailers.smtp.encryption'),
                'smtp_username' => config('mail.mailers.smtp.username'),
            ]);

            // No lanzar la excepción para que no detenga el proceso
            return false;
        }
    }

    /**
     * Envía una notificación interna (campanita) y opcionalmente por email.
     */
    public function enviarAUsuario(
        Usuario $usuario,
        string $titulo,
        string $mensaje,
        string $tipo = 'sistema',
        ?string $url = null,
        bool $enviarEmail = true
    ): bool {
        // ✅ FILTRO: si el destinatario es admin o super_admin, NO se le notifica
        if ($usuario->isAdmin() || $usuario->isSuperAdmin()) {
            Log::info("🚫 [NotificacionService] Omitida notificación a administrador", [
                'usuario_id' => $usuario->id,
                'usuario'    => $usuario->usuario,
                'rol'        => $usuario->rol?->nombre,
                'titulo'     => $titulo,
            ]);
            return false;
        }

        // Crear notificación en el sistema (campanita)
        try {
            $this->crearNotificacionSistema($usuario, $titulo, $mensaje, $tipo, $url);
        } catch (\Throwable $e) {
            Log::error("Error al crear notificación en sistema", [
                'usuario_id' => $usuario->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Enviar por email si se solicita
        if ($enviarEmail) {
            $email = $this->obtenerEmailUsuario($usuario);

            if ($email) {
                return $this->enviarAResponsable(
                    $email,
                    $usuario->nombre_completo ?? $usuario->usuario,
                    $titulo,
                    $mensaje,
                    $tipo,
                    $url,
                    true
                );
            } else {
                Log::warning("⚠️ [NotificacionService] Usuario sin email", [
                    'usuario_id' => $usuario->id,
                    'usuario' => $usuario->usuario,
                ]);
            }
        }

        return true;
    }

    /**
     * Obtiene el email de un usuario (directo o del trabajador).
     */
    protected function obtenerEmailUsuario(Usuario $usuario): ?string
    {
        // 1. Email directo del usuario
        if (!empty($usuario->email)) {
            return $usuario->email;
        }

        // 2. Email del trabajador asociado
        if ($usuario->trabajador && !empty($usuario->trabajador->email)) {
            return $usuario->trabajador->email;
        }

        return null;
    }

    /**
     * Crea una notificación en el sistema (campanita).
     */
    protected function crearNotificacionSistema(
        Usuario $usuario,
        string $titulo,
        string $mensaje,
        string $tipo,
        ?string $url
    ): void {
        try {
            $usuario->notificaciones()->create([
                'tipo' => $tipo,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'url' => $url,
                'leida' => false,
                'fecha_envio' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Error al crear notificación en sistema", [
                'usuario_id' => $usuario->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cuenta las notificaciones no leídas de un usuario.
     */
    public function countNoLeidas(Usuario $usuario): int
    {
        return $usuario->notificacionesNoLeidas()->count();
    }

    /**
     * Obtiene las notificaciones no leídas de un usuario.
     */
    public function getNoLeidas(Usuario $usuario)
    {
        return $usuario->notificacionesNoLeidas()
            ->orderBy('fecha_envio', 'desc')
            ->get();
    }

    /**
     * Marca una notificación como leída.
     */
    public function marcarComoLeida(int $id, Usuario $usuario): bool
    {
        $notificacion = $usuario->notificaciones()->find($id);

        if ($notificacion) {
            $notificacion->update(['leida' => true]);
            return true;
        }

        return false;
    }

    /**
     * Marca todas las notificaciones como leídas.
     */
    public function marcarTodasComoLeidas(Usuario $usuario): int
    {
        return $usuario->notificacionesNoLeidas()
            ->update(['leida' => true]);
    }
}