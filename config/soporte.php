<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto-creación de ficha de soporte desde cambio de estatus de activo
    |--------------------------------------------------------------------------
    */
    'auto_crear_ficha_desde_activo' => env('SOPORTE_AUTO_FICHA_ACTIVO', true),

    /*
    |--------------------------------------------------------------------------
    | Estados de ficha considerados "abiertos"
    |--------------------------------------------------------------------------
    */
    'estados_ficha_abierta' => ['en_proceso', 'aceptada', 'rechazada'],

    'estatus_que_requieren_ficha' => ['En reparación'],
];