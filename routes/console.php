<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\LeerCorreosSolicitudes;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new LeerCorreosSolicitudes)->everyFiveMinutes();

Artisan::command('correos:leer', function () {
    $service = app(\App\Services\CorreoImapService::class);
    $count = $service->leerCorreosNuevos();
    $this->info("✅ {$count} correos nuevos procesados");
})->purpose('Leer correos de solicitudes manualmente');