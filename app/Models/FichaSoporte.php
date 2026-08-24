<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FichaSoporte extends Model
{
    protected $table = 'fichas_soporte';

    protected $fillable = [
        'activo_id',
        'tecnico_id',
        'tecnico_nombre',
        'usuario_reporta_id',
        'usuario_reporta_nombre',
        'fecha_ingreso',
        'fecha_salida',
        'diagnostico',
        'trabajo_realizado',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'fecha_salida' => 'datetime',
    ];

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class);
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'tecnico_id');
    }

    public function usuarioReporta(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_reporta_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(FichaSoporteDetalle::class, 'ficha_soporte_id');
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estado', 'en_proceso');
    }

    public function scopeFinalizados($query)
    {
        return $query->where('estado', 'finalizado');
    }
}