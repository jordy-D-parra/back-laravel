<?php
// app/Providers/EventServiceProvider.php

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
