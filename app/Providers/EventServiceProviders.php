<?php
// app/Providers/EventServiceProvider.php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\SolicitudCreada::class => [
            \App\Listeners\EnviarNotificacionSolicitudCreada::class,
        ],
        \App\Events\SolicitudAprobada::class => [
            \App\Listeners\EnviarNotificacionSolicitudAprobada::class,
        ],
        \App\Events\SolicitudRechazada::class => [
            \App\Listeners\EnviarNotificacionSolicitudRechazada::class,
        ],
    ];
}