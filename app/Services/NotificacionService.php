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
     * Enviar notificación a un usuario específico
     */
    public function enviarAUsuario(
        Usuario $usuario,
        string $titulo,
        string $mensaje,
        string $tipo = 'sistema',
        ?string $url = null,
        bool $enviarCorreo = true
    ): Notificacion {
        // Verificar si ya existe una notificación idéntica para este usuario en los últimos 5 minutos
        $notificacionExistente = Notificacion::where('usuario_id', $usuario->id)
            ->where('titulo', $titulo)
            ->where('mensaje', $mensaje)
            ->where('fecha_envio', '>=', now()->subMinutes(5))
            ->first();

        if ($notificacionExistente) {
            return $notificacionExistente;
        }

        // Crear notificación en base de datos
        $notificacion = Notificacion::create([
            'usuario_id' => $usuario->id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url' => $url,
            'fecha_envio' => now(),
        ]);

        // Enviar correo electrónico
        if ($enviarCorreo && $usuario->email) {
            try {
                $nombre = $usuario->trabajador?->nombre ?? $usuario->usuario;
                Mail::to($usuario->email)->send(new NotificacionMail($notificacion, $nombre));
            } catch (\Exception $e) {
                Log::error('Error al enviar correo de notificación: ' . $e->getMessage());
            }
        }

        return $notificacion;
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