<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleSolicitud extends Model
{
    use HasFactory;

    protected $table = 'detalle_solicitud';

    protected $fillable = [
        'id_solicitud',
        'id_activo',
        'periferico_id',
        'tipo_item',
        'cantidad_solicitada',
        'observaciones'
    ];

    protected $casts = [
        'cantidad_solicitada' => 'integer'
    ];

    // Relaciones
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'id_solicitud');
    }

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'id_activo');
    }

    public function periferico(): BelongsTo
    {
        return $this->belongsTo(Periferico::class, 'periferico_id');
    }

    // Verificar disponibilidad
    public function verificarDisponibilidad(): bool
    {
        if ($this->tipo_item === 'activo') {
            return $this->activo && $this->activo->cantidad >= $this->cantidad_solicitada;
        } else {
            return $this->periferico && $this->periferico->cantidad_disponible >= $this->cantidad_solicitada;
        }
    }
}
