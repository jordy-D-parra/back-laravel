<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'auditoria' => \App\Http\Middleware\AuditoriaMiddleware::class,
            'prevent-back-history' => \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\PreventBackHistory::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        // ✅ Notificar préstamos vencidos todos los días a las 8 AM
        $schedule->job(new \App\Jobs\NotificarPrestamosVencidos)->dailyAt('08:00');

        // ✅ Leer correos IMAP cada 5 minutos
        $schedule->job(new \App\Jobs\LeerCorreosSolicitudes)->everyFiveMinutes();

        // ✅ Liberar jobs atascados cada 10 minutos
        $schedule->command('cola:limpiar --minutos=15')->everyTenMinutes();

        // ✅ Limpiar caché de configuración una vez al día (opcional)
        $schedule->command('config:clear')->dailyAt('03:00');
    })
    ->create();