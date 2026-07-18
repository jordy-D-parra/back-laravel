<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FichaSoporteDetalle extends Model
{
    protected $table = 'fichas_soporte_detalle';

    protected $fillable = [
        'ficha_soporte_id',
        'componente_id',
        'componente_nombre',
        'estado_ingreso',
        'estado_salida',
        'observaciones',
    ];

    public function fichaSoporte(): BelongsTo
    {
        return $this->belongsTo(FichaSoporte::class);
    }

    public function componente(): BelongsTo
    {
        return $this->belongsTo(Componente::class);
    }
}