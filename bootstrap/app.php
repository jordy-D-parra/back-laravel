<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'auditoria' => \App\Http\Middleware\AuditoriaMiddleware::class,
            'prevent-back-history' => \App\Http\Middleware\PreventBackHistory::class,
        ]);

        // Middleware global en el grupo 'web'
        $middleware->appendToGroup('web', \App\Http\Middleware\PreventBackHistory::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();