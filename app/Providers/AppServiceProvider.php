<?php

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Super Admin / Admin bypass: SIEMPRE pasa cualquier verificación de Gate/Policy
        Gate::before(function (Usuario $user, string $ability) {
            if ($user->isSuperAdmin() || $user->isAdmin()) {
                return true;
            }
            return null;
        });

        // Gate genérico por permisos
        Gate::define('permission', function (Usuario $user, string $permission) {
            return $user->hasPermission($permission);
        });
    }
}