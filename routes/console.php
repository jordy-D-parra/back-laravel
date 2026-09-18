<?php
// routes/console.php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\LeerCorreosSolicitudes;
use App\Jobs\NotificarPrestamosVencidos;
use App\Services\NotificacionService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============================================================
// SCHEDULES
// ============================================================

// ✅ Leer correos cada 5 minutos (sin solapamiento)
Schedule::job(new LeerCorreosSolicitudes)
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// ✅ Notificar préstamos vencidos todos los días a las 8:00 AM
Schedule::job(new NotificarPrestamosVencidos)
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();

// ============================================================
// COMANDOS ARTISAN
// ============================================================

Artisan::command('correos:leer', function () {
    $service = app(\App\Services\CorreoImapService::class);
    $count = $service->leerCorreosNuevos();
    $this->info("✅ {$count} correos nuevos procesados");
})->purpose('Leer correos de solicitudes manualmente');

Artisan::command('prestamos:notificar-vencidos', function () {
    $this->info('🔍 Buscando préstamos vencidos...');
    $job = new NotificarPrestamosVencidos();
    $job->handle(app(NotificacionService::class));
    $this->info('✅ Notificaciones de préstamos vencidos enviadas');
})->purpose('Notificar préstamos vencidos a los responsables');