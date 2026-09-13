<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleSolicitud extends Model
{
    use HasFactory;

    protected $table = 'detalle_solicitudes';

    protected $fillable = [
        'solicitud_id',
        'activo_id',
        'componente_id',
        'tipo_item',
        'descripcion_personalizada',
        'cantidad_solicitada',
        'observaciones',
    ];

    protected $casts = [
        'cantidad_solicitada' => 'integer',
    ];

    // Relaciones
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function activo()
    {
        return $this->belongsTo(Activo::class);
    }

    public function componente()
    {
        return $this->belongsTo(Componente::class);
    }

    // Accesores
    public function getDescripcionItemAttribute()
    {
        if ($this->descripcion_personalizada) {
            return $this->descripcion_personalizada;
        }

        if ($this->tipo_item === 'activo' && $this->activo) {
            $modelo = $this->activo->modelo;
            $marca = $modelo ? $modelo->marca->nombre : '';
            return trim($marca . ' ' . $modelo->nombre . ' - Serial: ' . $this->activo->serial);
        }

        if ($this->tipo_item === 'componente' && $this->componente) {
            return $this->componente->tipo . ' - ' . ($this->componente->marca ?: 'N/A') . ' - Serial: ' . ($this->componente->serial ?: 'N/A');
        }

        return 'Item no disponible';
    }
}
