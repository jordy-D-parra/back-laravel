<?php

// app/Services/NotificacionService.php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\Usuario;
use App\Mail\NotificacionMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
    /**
     * Enviar notificación a un usuario del sistema.
     */
    public function enviarAUsuario(
        Usuario $usuario,
        string $titulo,
        string $mensaje,
        string $tipo = 'sistema',
        ?string $url = null,
        bool $enviarCorreo = true
    ): Notificacion {
        // Anti-duplicados: mismo usuario + mismo título en 5 min
        $existente = Notificacion::where('usuario_id', $usuario->id)
            ->where('titulo', $titulo)
            ->where('fecha_envio', '>=', now()->subMinutes(5))
            ->first();

        if ($existente) {
            Log::info('⏭️ Notificación duplicada omitida (usuario)', [
                'usuario_id' => $usuario->id,
                'titulo' => $titulo,
            ]);
            return $existente;
        }

        $notificacion = Notificacion::create([
            'usuario_id' => $usuario->id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url' => $url,
            'fecha_envio' => now(),
            'leida' => false,
        ]);

        if ($enviarCorreo && $usuario->email) {
            try {
                Mail::to($usuario->email)->send(
                    new NotificacionMail(
                        $notificacion,
                        $usuario->trabajador?->nombre ?? $usuario->usuario
                    )
                );

                Log::info('📧 ✅ Correo enviado a usuario', [
                    'email' => $usuario->email,
                    'titulo' => $titulo,
                    'tipo' => $tipo,
                ]);
            } catch (\Throwable $e) {
                Log::error('❌ Error al enviar correo a usuario: ' . $e->getMessage(), [
                    'email' => $usuario->email,
                    'titulo' => $titulo,
                    'error_clase' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }

        return $notificacion;
    }

    /**
     * Enviar notificación a un responsable EXTERNO.
     */
    public function enviarAResponsable(
        string $email,
        string $nombre,
        string $titulo,
        string $mensaje,
        string $tipo = 'solicitud',
        ?string $url = null
    ): ?Notificacion {
        Log::info('📤 [NotificacionService] Enviando a responsable externo', [
            'email' => $email,
            'nombre' => $nombre,
            'titulo' => $titulo,
            'tipo' => $tipo,
            'longitud_mensaje' => strlen($mensaje),
        ]);

        // Anti-duplicados: mismo título en 5 min
        $existente = Notificacion::whereNull('usuario_id')
            ->where('titulo', $titulo)
            ->where('fecha_envio', '>=', now()->subMinutes(5))
            ->first();

        if ($existente) {
            Log::info('⏭️ Correo duplicado omitido a responsable externo', [
                'email' => $email,
                'titulo' => $titulo,
            ]);
            return $existente;
        }

        $notificacion = Notificacion::create([
            'usuario_id' => null,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url' => $url,
            'fecha_envio' => now(),
            'leida' => false,
        ]);

        try {
            Mail::to($email)->send(new NotificacionMail($notificacion, $nombre));

            Log::info('📧 ✅ Correo enviado a responsable externo', [
                'email' => $email,
                'nombre' => $nombre,
                'titulo' => $titulo,
                'tipo' => $tipo,
            ]);

            return $notificacion;
        } catch (\Throwable $e) {
            Log::error('❌ Error CRÍTICO al enviar correo a responsable externo: ' . $e->getMessage(), [
                'email' => $email,
                'nombre' => $nombre,
                'titulo' => $titulo,
                'error_clase' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Enviar notificación a múltiples usuarios.
     */
    public function enviarAMultiples(
        array $usuarioIds,
        string $titulo,
        string $mensaje,
        string $tipo = 'sistema',
        ?string $url = null,
        bool $enviarCorreo = true
    ): array {
        $notificaciones = [];
        $usuarios = Usuario::whereIn('id', $usuarioIds)->with('trabajador')->get();

        foreach ($usuarios as $usuario) {
            try {
                $notificaciones[] = $this->enviarAUsuario(
                    $usuario,
                    $titulo,
                    $mensaje,
                    $tipo,
                    $url,
                    $enviarCorreo
                );
            } catch (\Throwable $e) {
                Log::error('Error en envío múltiple: ' . $e->getMessage(), [
                    'usuario_id' => $usuario->id,
                ]);
            }
        }

        return $notificaciones;
    }

    /**
     * Enviar notificación a todos los usuarios con un rol específico.
     */
    public function enviarARol(
        string $rolNombre,
        string $titulo,
        string $mensaje,
        string $tipo = 'sistema',
        ?string $url = null,
        bool $enviarCorreo = true
    ): array {
        $usuarios = Usuario::whereHas('rol', function ($query) use ($rolNombre) {
            $query->where('nombre', $rolNombre);
        })->with('trabajador')->get();

        return $this->enviarAMultiples(
            $usuarios->pluck('id')->toArray(),
            $titulo,
            $mensaje,
            $tipo,
            $url,
            $enviarCorreo
        );
    }

    public function getNoLeidas(Usuario $usuario): \Illuminate\Database\Eloquent\Collection
    {
        return Notificacion::porUsuario($usuario->id)
            ->noLeidas()
            ->orderBy('fecha_envio', 'desc')
            ->get();
    }

    public function countNoLeidas(Usuario $usuario): int
    {
        return Notificacion::porUsuario($usuario->id)
            ->noLeidas()
            ->count();
    }

    public function marcarComoLeida(int $notificacionId, Usuario $usuario): bool
    {
        $notificacion = Notificacion::porUsuario($usuario->id)
            ->where('id', $notificacionId)
            ->first();

        if (!$notificacion) {
            return false;
        }

        return $notificacion->update(['leida' => true]);
    }

    public function marcarTodasComoLeidas(Usuario $usuario): int
    {
        return Notificacion::porUsuario($usuario->id)
            ->noLeidas()
            ->update(['leida' => true]);
    }
}