<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichaSoporte extends Model
{
    use HasFactory;

    protected $table = 'ficha_soporte';

    protected $fillable = [
        'activo_id',
        'tecnico_id',
        'usuario_reporta_id',
        'fecha_ingreso',
        'fecha_salida',
        'diagnostico',
        'trabajo_realizado',
        'observaciones',
        'estado'
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'fecha_salida' => 'datetime'
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function usuarioReporta()
    {
        return $this->belongsTo(User::class, 'usuario_reporta_id');
    }

    public function detalles()
    {
        return $this->hasMany(FichaSoporteDetalle::class, 'ficha_soporte_id');
    }
}
