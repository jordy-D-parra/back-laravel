<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            // Si es una petición AJAX/JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado'
                ], 401);
            }
            return redirect()->route('login');
        }

        // Verificar si tiene el rol requerido
        if (!Auth::user()->isRole($role)) {
            // Si es una petición AJAX/JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para acceder a esta sección. Se requiere rol: ' . $role
                ], 403);
            }

            abort(403, 'No tienes permiso para acceder a esta sección. Se requiere rol: ' . $role);
        }

        return $next($request);
    }
}
