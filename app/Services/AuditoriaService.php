<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class AuditoriaService
{
    protected function getUsuario(): ?Usuario
    {
        return Auth::user();
    }

    protected function getRequestData(): array
    {
        $request = request();
        return [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }

    protected function getUsuarioNombre(): ?string
    {
        $usuario = $this->getUsuario();
        if (!$usuario) return 'Sistema';
        return $usuario->trabajador?->nombre . ' ' . $usuario->trabajador?->apellido ?: $usuario->usuario;
    }

    /**
     * Registrar una acción en la bitácora.
     */
    public function registrar(
        string $accion,
        string $modulo,
        ?string $tablaAfectada = null,
        ?int $registroId = null,
        ?array $datosOriginales = null,
        ?array $datosNuevos = null,
        ?string $descripcion = null
    ): Auditoria {
        $usuario = $this->getUsuario();
        $requestData = $this->getRequestData();

        return Auditoria::create([
            'uuid' => Str::uuid()->toString(),
            'usuario_id' => $usuario?->id,
            'usuario_nombre' => $this->getUsuarioNombre(),
            'accion' => $accion,
            'modulo' => $modulo,
            'tabla_afectada' => $tablaAfectada,
            'registro_id' => $registroId,
            'datos_originales' => $datosOriginales,
            'datos_nuevos' => $datosNuevos,
            'descripcion' => $descripcion,
            'ip_address' => $requestData['ip_address'],
            'user_agent' => $requestData['user_agent'],
        ]);
    }

    /**
     * Registrar una acción de creación.
     */
    public function registrarCreacion(string $modulo, string $tablaAfectada, int $registroId, array $datosNuevos, ?string $descripcion = null): Auditoria
    {
        return $this->registrar('crear', $modulo, $tablaAfectada, $registroId, null, $datosNuevos, $descripcion);
    }

    /**
     * Registrar una acción de actualización.
     */
    public function registrarActualizacion(string $modulo, string $tablaAfectada, int $registroId, array $datosOriginales, array $datosNuevos, ?string $descripcion = null): Auditoria
    {
        // Filtrar solo campos que realmente cambiaron
        $cambios = array_filter($datosNuevos, function ($value, $key) use ($datosOriginales) {
            if (in_array($key, ['created_at', 'updated_at', 'deleted_at', 'remember_token', '_token', '_method'])) {
                return false;
            }
            return !array_key_exists($key, $datosOriginales) || $datosOriginales[$key] != $value;
        }, ARRAY_FILTER_USE_BOTH);

        if (empty($cambios)) {
            $descripcion = ($descripcion ? $descripcion . ' - ' : '') . 'Sin cambios significativos en los campos trackeados.';
        }

        return $this->registrar('editar', $modulo, $tablaAfectada, $registroId, $datosOriginales, $datosNuevos, $descripcion);
    }

    /**
     * Registrar una acción de eliminación.
     */
    public function registrarEliminacion(string $modulo, string $tablaAfectada, int $registroId, array $datosOriginales, ?string $descripcion = null): Auditoria
    {
        return $this->registrar('eliminar', $modulo, $tablaAfectada, $registroId, $datosOriginales, null, $descripcion);
    }

    /**
     * Registrar un evento de login/logout.
     */
    public function registrarEvento(string $accion, string $modulo = 'auth', ?string $descripcion = null): Auditoria
    {
        return $this->registrar($accion, $modulo, null, null, null, null, $descripcion);
    }
}