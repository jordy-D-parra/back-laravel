<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\Usuario;
use App\Mail\NotificacionMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
    /**
     * Enviar notificación a un usuario del sistema
     */
    public function enviarAUsuario(
        Usuario $usuario,
        string $titulo,
        string $mensaje,
        string $tipo = 'sistema',
        ?string $url = null,
        bool $enviarCorreo = true
    ): Notificacion {
        $notificacionExistente = Notificacion::where('usuario_id', $usuario->id)
            ->where('titulo', $titulo)
            ->where('mensaje', $mensaje)
            ->where('fecha_envio', '>=', now()->subMinutes(5))
            ->first();

        if ($notificacionExistente) {
            return $notificacionExistente;
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
                $nombre = $usuario->trabajador?->nombre ?? $usuario->usuario;
                Mail::to($usuario->email)->send(new NotificacionMail($notificacion, $nombre));
            } catch (\Exception $e) {
                Log::error('Error al enviar correo a usuario: ' . $e->getMessage());
            }
        }

        return $notificacion;
    }

    /**
     * Enviar notificación a un responsable EXTERNO (no usuario del sistema)
     */
    public function enviarAResponsable(
        string $email,
        string $nombre,
        string $titulo,
        string $mensaje,
        string $tipo = 'solicitud',
        ?string $url = null
    ): ?Notificacion {
        try {
            $notificacionExistente = Notificacion::whereNull('usuario_id')
                ->where('titulo', $titulo)
                ->where('mensaje', $mensaje)
                ->where('fecha_envio', '>=', now()->subMinutes(5))
                ->first();

            if ($notificacionExistente) {
                return $notificacionExistente;
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

            Mail::to($email)->send(new NotificacionMail($notificacion, $nombre));

            Log::info('Correo enviado a responsable externo', [
                'email' => $email,
                'nombre' => $nombre,
                'titulo' => $titulo
            ]);

            return $notificacion;

        } catch (\Exception $e) {
            Log::error('Error al enviar correo a responsable externo: ' . $e->getMessage(), [
                'email' => $email,
                'nombre' => $nombre
            ]);
            return null;
        }
    }

    /**
     * Enviar notificación a múltiples usuarios
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
            $notificaciones[] = $this->enviarAUsuario(
                $usuario,
                $titulo,
                $mensaje,
                $tipo,
                $url,
                $enviarCorreo
            );
        }

        return $notificaciones;
    }

    /**
     * Enviar notificación a todos los usuarios con un rol específico
     */
    public function enviarARol(
        string $rolNombre,
        string $titulo,
        string $mensaje,
        string $tipo = 'sistema',
        ?string $url = null,
        bool $enviarCorreo = true
    ): array {
        $usuarios = Usuario::whereHas('rol', function($query) use ($rolNombre) {
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

    /**
     * Obtener notificaciones no leídas de un usuario
     */
    public function getNoLeidas(Usuario $usuario): \Illuminate\Database\Eloquent\Collection
    {
        return Notificacion::porUsuario($usuario->id)
            ->noLeidas()
            ->orderBy('fecha_envio', 'desc')
            ->get();
    }

    /**
     * Contar notificaciones no leídas de un usuario
     */
    public function countNoLeidas(Usuario $usuario): int
    {
        return Notificacion::porUsuario($usuario->id)
            ->noLeidas()
            ->count();
    }

    /**
     * Marcar notificación como leída
     */
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

    /**
     * Marcar todas las notificaciones de un usuario como leídas
     */
    public function marcarTodasComoLeidas(Usuario $usuario): int
    {
        return Notificacion::porUsuario($usuario->id)
            ->noLeidas()
            ->update(['leida' => true]);
    }
}