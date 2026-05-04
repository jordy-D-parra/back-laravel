<?php

namespace App\Policies;

use App\Models\Usuario;
use App\Models\Solicitud;

class SolicitudPolicy
{
    /**
     * Determinar si el usuario puede ver la solicitud
     */
    public function view(Usuario $user, Solicitud $solicitud)
    {
        // Super admin y admin pueden ver todas
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return true;
        }

        // Usuarios normales solo ven sus propias solicitudes
        return $user->id === $solicitud->id_solicitante;
    }

    /**
     * Determinar si el usuario puede aprobar/rechazar solicitudes
     */
    public function approve(Usuario $user, Solicitud $solicitud)
    {
        // Solo super_admin y admin pueden aprobar
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    /**
     * Determinar si el usuario puede cancelar la solicitud
     */
    public function cancel(Usuario $user, Solicitud $solicitud)
    {
        // Solo el creador puede cancelar y solo si está pendiente o aprobada
        return $user->id === $solicitud->id_solicitante &&
               in_array($solicitud->estado_solicitud, ['pendiente', 'aprobada']);
    }

    /**
     * Determinar si el usuario puede crear solicitudes
     */
    public function create(Usuario $user)
    {
        // Todos los usuarios autenticados pueden crear solicitudes
        return true;
    }

    /**
     * Determinar si el usuario puede actualizar la solicitud
     */
    public function update(Usuario $user, Solicitud $solicitud)
    {
        // Solo el creador puede actualizar si está pendiente
        return $user->id === $solicitud->id_solicitante &&
               $solicitud->estado_solicitud === 'pendiente';
    }

    /**
     * Determinar si el usuario puede eliminar la solicitud
     */
    public function delete(Usuario $user, Solicitud $solicitud)
    {
        // Solo super_admin puede eliminar
        return $user->hasRole('super_admin');
    }
}
