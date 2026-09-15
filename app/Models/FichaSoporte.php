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
        'correo_id',
        'tecnico_id',
        'tecnico_nombre',
        'usuario_reporta_id',
        'usuario_reporta_nombre',
        'fecha_ingreso',
        'fecha_requerida_entrega',
        'fecha_salida',
        'diagnostico',
        'trabajo_realizado',
        'observaciones',
        'estado',
        'origen',
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'fecha_salida' => 'datetime',
        'fecha_requerida_entrega' => 'date',
    ];

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class);
    }

    public function correo(): BelongsTo
    {
        return $this->belongsTo(CorreoRecibido::class, 'correo_id');
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

    // Helper: días restantes para la entrega
    public function getDiasRestantesAttribute(): ?int
    {
        if (!$this->fecha_requerida_entrega) return null;
        if ($this->estado === 'finalizado') return 0;
        return now()->startOfDay()->diffInDays($this->fecha_requerida_entrega, false);
    }

    // Helper: ¿está vencida?
    public function getEstaVencidaAttribute(): bool
    {
        if (!$this->fecha_requerida_entrega) return false;
        if ($this->estado === 'finalizado') return false;
        return now()->startOfDay()->gt($this->fecha_requerida_entrega);
    }
}