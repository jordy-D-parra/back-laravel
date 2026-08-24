<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestamoDetalle extends Model
{
    protected $fillable = [
        'prestamo_id',
        'prestable_type',
        'prestable_id',
        'cantidad',
        'estado_entrega',
        'estado_devolucion',
        'observaciones',
    ];

    protected $table = 'prestamo_detalles';

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class);
    }

    public function prestable()
    {
        return $this->morphTo();
    }

    public function isDevuelto(): bool
    {
        return !is_null($this->estado_devolucion);
    }

    public function getNombreItemAttribute(): string
    {
        if (! $this->prestable) {
            return 'Item #' . $this->prestable_id;
        }

        if ($this->prestable instanceof Activo) {
            return $this->prestable->serial;
        }

        if ($this->prestable instanceof Componente) {
            return trim($this->prestable->tipo . ' ' . $this->prestable->marca . ' ' . $this->prestable->modelo . ' ' . $this->prestable->serial);
        }

        return $this->prestable->nombre ?? $this->prestable->codigo ?? 'Item #' . $this->prestable_id;
    }
}
