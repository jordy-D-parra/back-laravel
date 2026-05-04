<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichaSoporteDetalle extends Model
{
    use HasFactory;

    protected $table = 'ficha_soporte_detalle';

    protected $fillable = [
        'ficha_soporte_id',
        'componente_id',
        'estado_ingreso',
        'estado_salida',
        'observaciones'
    ];

    public function fichaSoporte()
    {
        return $this->belongsTo(FichaSoporte::class, 'ficha_soporte_id');
    }

    public function componente()
    {
        return $this->belongsTo(Componente::class, 'componente_id');
    }
}
