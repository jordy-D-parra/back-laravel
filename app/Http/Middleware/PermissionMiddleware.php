<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Uso: ->middleware('permission:ver-usuarios')
     *      ->middleware('permission:ver-usuarios,crear-usuario')  // Cualquiera de los dos
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Si no se especifican permisos, solo se requiere autenticación
        if (empty($permissions)) {
            return $next($request);
        }

        // Admin/super_admin siempre pasa
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return $next($request);
        }

        // Verificar si tiene AL MENOS UNO de los permisos
        if ($user->hasAnyPermission($permissions)) {
            return $next($request);
        }

        // Denegar acceso
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción.',
                'required_permissions' => $permissions,
            ], 403);
        }

        abort(403, 'No tienes permiso para acceder a esta sección.');
    }
}