<?php

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Compartir permisos y rol del usuario en TODAS las vistas
        View::composer('*', function ($view) {
            $user = Auth::user();

            if ($user instanceof Usuario) {
                $view->with('authPermissions', $user->getPermisosNombres());
                $view->with('authRol', $user->rol?->nombre);
                $view->with('authIsAdmin', $user->isAdmin() || $user->isSuperAdmin());
            } else {
                $view->with('authPermissions', []);
                $view->with('authRol', null);
                $view->with('authIsAdmin', false);
            }
        });

        // Directivas Blade personalizadas
        Blade::if('canperm', function (string $permission) {
            $user = Auth::user();
            return $user instanceof Usuario && $user->hasPermission($permission);
        });

        Blade::if('cananyperm', function (array $permissions) {
            $user = Auth::user();
            return $user instanceof Usuario && $user->hasAnyPermission($permissions);
        });

        Blade::if('role', function (string ...$roles) {
            $user = Auth::user();
            return $user instanceof Usuario && $user->hasAnyRole($roles);
        });
    }

    public function register(): void
    {
        //
    }
}