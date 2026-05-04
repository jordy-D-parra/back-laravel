<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class ActivityHelper
{
    /**
     * Registrar actividad del usuario
     */
    public static function log($action, $description = null, $oldData = null, $newData = null)
    {
        try {
            $userId = auth()->id();

            // Si no hay usuario autenticado y es registro, no registramos actividad
            if (!$userId && $action !== 'register') {
                Log::warning('Intento de registrar actividad sin usuario autenticado', [
                    'action' => $action,
                    'ip' => Request::ip()
                ]);
                return false;
            }

            $log = ActivityLog::create([
                'user_id' => $userId,
                'action' => $action,
                'description' => $description ?? self::getDefaultDescription($action),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'old_data' => $oldData ? json_encode($oldData) : null,
                'new_data' => $newData ? json_encode($newData) : null,
            ]);

            // Log de depuración
            Log::info('Actividad registrada exitosamente', [
                'id' => $log->id,
                'user_id' => $userId,
                'action' => $action,
                'description' => $description
            ]);

            return $log;

        } catch (\Exception $e) {
            Log::error('Error al registrar actividad', [
                'error' => $e->getMessage(),
                'action' => $action,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Descripción por defecto para acciones comunes
     */
    private static function getDefaultDescription($action)
    {
        $descriptions = [
            'login' => 'Inicio de sesión',
            'logout' => 'Cierre de sesión',
            'register' => 'Registro de nuevo usuario',
            'change_role' => 'Cambio de rol de usuario',
            'change_password' => 'Cambio de contraseña',
            'password_reset' => 'Recuperación de contraseña',
            'password_recovery' => 'Verificación de preguntas de seguridad',
            'update_profile' => 'Actualización de perfil',
            'update_security_questions' => 'Actualización de preguntas de seguridad',
        ];

        return $descriptions[$action] ?? 'Acción del sistema';
    }
}
