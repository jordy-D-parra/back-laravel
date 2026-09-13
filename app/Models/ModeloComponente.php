<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModeloComponente extends Model
{
    protected $table = 'modelo_componente';

    protected $fillable = [
        'modelo_id',
        'tipo',
        'descripcion',
        'capacidad',
        'requerido',
    ];

    protected $casts = [
        'requerido' => 'boolean',
    ];

    public function modelo(): BelongsTo
    {
        return $this->belongsTo(Modelo::class);
    }

    public function componentes(): HasMany
    {
        return $this->hasMany(Componente::class, 'modelo_componente_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        $nombre = "{$this->tipo} - {$this->descripcion}";
        if ($this->capacidad) {
            $nombre .= " ({$this->capacidad})";
        }
        return $nombre;
    }
}
