<?php

namespace App\Http\Middleware;

use App\Services\AuditoriaService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditoriaMiddleware
{
    protected AuditoriaService $auditoriaService;

    public function __construct(AuditoriaService $auditoriaService)
    {
        $this->auditoriaService = $auditoriaService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Solo registrar acciones que modifican datos
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $next($request);
        }

        // No auditar rutas de login/logout
        if ($request->routeIs('login') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Determinar el módulo
        $modulo = $this->determinarModulo($request);
        if (!$modulo) {
            Log::warning('Auditoría: No se pudo determinar el módulo para la ruta ' . $request->path());
            return $next($request);
        }

        // Obtener ID de la ruta
        $registroId = $this->obtenerIdDeRuta($request);

        // Obtener datos originales para ediciones
        $datosOriginales = null;
        if (in_array($request->method(), ['PUT', 'PATCH']) && $registroId) {
            $tabla = $this->determinarTabla($request);
            if ($tabla) {
                try {
                    $datosOriginales = DB::table($tabla)->where('id', $registroId)->first();
                    if ($datosOriginales) {
                        $datosOriginales = (array) $datosOriginales;
                        unset($datosOriginales['password'], $datosOriginales['remember_token']);
                    }
                } catch (\Exception $e) {
                    Log::error('Auditoría: Error al obtener datos originales - ' . $e->getMessage());
                }
            }
        }

        // Procesar la solicitud
        $response = $next($request);

        // Registrar la acción
        $this->registrarAccion($request, $response, $modulo, $registroId, $datosOriginales);

        return $response;
    }

    protected function determinarModulo(Request $request): ?string
    {
        $path = $request->path();

        $map = [
            'admin/usuarios' => 'usuarios',
            'admin/trabajadores' => 'trabajadores',
            'admin/roles' => 'roles',
            'admin/instituciones' => 'instituciones',
            'admin/departamentos' => 'departamentos',
            'admin/responsables' => 'responsables',
            'admin/equipos/marcas' => 'marcas',
            'admin/equipos/categorias' => 'categorias_equipos',
            'admin/equipos/modelos' => 'modelos',
            'admin/equipos' => 'equipos',
            'admin/activos' => 'activos',
            'admin/componentes' => 'componentes',
            'admin/prestamos' => 'prestamos',
            'admin/solicitudes' => 'solicitudes',
            'admin/soporte' => 'soporte_tecnico',
            'admin/entidades' => 'entidades',
            'admin/actas' => 'actas',
            'admin/notificaciones' => 'notificaciones',
            'admin/calendario' => 'calendario',
        ];

        foreach ($map as $routePattern => $modulo) {
            if (str_starts_with($path, $routePattern)) {
                return $modulo;
            }
        }

        return null;
    }

    protected function determinarTabla(Request $request): ?string
    {
        $path = $request->path();

        $map = [
            'admin/usuarios' => 'usuarios',
            'admin/trabajadores' => 'trabajadores',
            'admin/roles' => 'roles',
            'admin/instituciones' => 'instituciones',
            'admin/departamentos' => 'departamentos',
            'admin/responsables' => 'responsables',
            'admin/equipos/marcas' => 'marcas',
            'admin/equipos/categorias' => 'categorias',
            'admin/equipos/modelos' => 'modelos',
            'admin/activos' => 'activos',
            'admin/componentes' => 'componentes',
            'admin/prestamos' => 'prestamos',
            'admin/solicitudes' => 'solicitudes',
            'admin/soporte' => 'fichas_soporte',
            'admin/notificaciones' => 'notificaciones',
        ];

        foreach ($map as $routePattern => $tabla) {
            if (str_starts_with($path, $routePattern)) {
                return $tabla;
            }
        }

        return null;
    }

    protected function obtenerIdDeRuta(Request $request): ?int
    {
        foreach ($request->route()->parameters() as $key => $value) {
            if (is_numeric($value) && in_array($key, [
                'id', 'activo', 'componente', 'prestamo', 'solicitud', 'trabajador',
                'usuario', 'institucion', 'departamento', 'responsable', 'soporte',
                'rol', 'modelo', 'marca', 'categoria', 'notificacion', 'institucione'
            ])) {
                return (int) $value;
            }
        }

        if ($request->has('id')) {
            return (int) $request->input('id');
        }

        return null;
    }

    protected function registrarAccion(Request $request, Response $response, string $modulo, ?int $registroId, ?array $datosOriginales): void
    {
        $accion = match ($request->method()) {
            'POST' => 'crear',
            'PUT', 'PATCH' => 'editar',
            'DELETE' => 'eliminar',
            default => null,
        };

        if (!$accion) return;

        // Solo registrar si la respuesta fue exitosa
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 400) {
            return;
        }

        $datosNuevos = $request->except(['_token', '_method', 'password', 'password_confirmation']);

        // Para creación, intentar obtener el ID del registro creado
        if ($accion === 'crear' && !$registroId) {
            $content = $response->getContent();
            $data = json_decode($content, true);

            if (isset($data['data']['id'])) {
                $registroId = (int) $data['data']['id'];
            } elseif (isset($data['id'])) {
                $registroId = (int) $data['id'];
            } elseif (isset($data['solicitud_id'])) {
                $registroId = (int) $data['solicitud_id'];
            }
        }

        $tabla = $this->determinarTabla($request);

        // Generar descripción
        if ($accion === 'editar' && $datosOriginales && $registroId) {
            $cambios = [];
            foreach ($datosNuevos as $key => $value) {
                if (array_key_exists($key, $datosOriginales) && $datosOriginales[$key] != $value) {
                    $old = is_null($datosOriginales[$key]) ? 'null' : $datosOriginales[$key];
                    $new = is_null($value) ? 'null' : $value;
                    $cambios[] = "{$key}: '{$old}' → '{$new}'";
                }
            }

            $descripcion = "Modificación en " . ($tabla ?? 'registro') . " ID {$registroId}: " . implode('; ', $cambios);
            if (empty($cambios)) {
                $descripcion = "Modificación en " . ($tabla ?? 'registro') . " ID {$registroId} (sin cambios detectados)";
            }

            $this->auditoriaService->registrarActualizacion(
                $modulo,
                $tabla ?? 'desconocida',
                $registroId,
                $datosOriginales,
                $datosNuevos,
                $descripcion
            );
            return;
        }

        $descripcion = match ($accion) {
            'crear' => "Creación en " . ($tabla ?? 'desconocida') . ($registroId ? " ID {$registroId}" : ''),
            'eliminar' => "Eliminación en " . ($tabla ?? 'desconocida') . ($registroId ? " ID {$registroId}" : ''),
            default => null,
        };

        if (!$descripcion) return;

        $this->auditoriaService->registrar(
            $accion,
            $modulo,
            $tabla ?? 'desconocida',
            $registroId,
            $accion === 'eliminar' ? $datosOriginales : null,
            $accion === 'crear' ? $datosNuevos : null,
            $descripcion
        );
    }
}