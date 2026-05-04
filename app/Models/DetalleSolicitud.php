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
        'descripcion_personalizada',  // 👈 AGREGADO: para items escritos manualmente
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

    // Obtener la descripción del item (prioriza la descripción personalizada)
    public function getDescripcionCompletaAttribute()
    {
        // Si hay descripción personalizada (escrita a mano), usarla
        if ($this->descripcion_personalizada) {
            return $this->descripcion_personalizada;
        }

        // Si no, obtener del inventario
        if ($this->tipo_item === 'activo' && $this->activo) {
            return $this->activo->serial . ' - ' . ($this->activo->marca_modelo ?? 'Activo');
        } elseif ($this->tipo_item === 'periferico' && $this->periferico) {
            return $this->periferico->nombre;
        }

        // Si hay observaciones, mostrarlas
        if ($this->observaciones) {
            return $this->observaciones;
        }

        return 'Item sin descripción';
    }

    // Obtener el tipo de item en texto legible
    public function getTipoItemTextoAttribute()
    {
        return $this->tipo_item === 'activo' ? 'Activo' : 'Periférico';
    }

    // Verificar disponibilidad
    public function verificarDisponibilidad(): bool
    {
        // Si tiene descripción personalizada, no verificar disponibilidad (es un item libre)
        if ($this->descripcion_personalizada) {
            return true;
        }

        if ($this->tipo_item === 'activo') {
            return $this->activo && $this->activo->cantidad >= $this->cantidad_solicitada;
        } elseif ($this->tipo_item === 'periferico') {
            return $this->periferico && $this->periferico->cantidad_disponible >= $this->cantidad_solicitada;
        }

        return false;
    }

    // Reducir stock al aprobar (solo para items de inventario)
    public function reducirStock(): bool
    {
        if ($this->descripcion_personalizada) {
            return true; // Items libres no afectan stock
        }

        if ($this->tipo_item === 'activo' && $this->activo) {
            if ($this->activo->cantidad >= $this->cantidad_solicitada) {
                $this->activo->decrement('cantidad', $this->cantidad_solicitada);
                return true;
            }
        } elseif ($this->tipo_item === 'periferico' && $this->periferico) {
            if ($this->periferico->cantidad_disponible >= $this->cantidad_solicitada) {
                $this->periferico->decrement('cantidad_disponible', $this->cantidad_solicitada);
                return true;
            }
        }

        return false;
    }

    // Restaurar stock al cancelar préstamo
    public function restaurarStock(): bool
    {
        if ($this->descripcion_personalizada) {
            return true;
        }

        if ($this->tipo_item === 'activo' && $this->activo) {
            $this->activo->increment('cantidad', $this->cantidad_solicitada);
            return true;
        } elseif ($this->tipo_item === 'periferico' && $this->periferico) {
            $this->periferico->increment('cantidad_disponible', $this->cantidad_solicitada);
            return true;
        }

        return false;
    }
}
