<?php
// app/Models/Municipio.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipio extends Model
{
    protected $table = 'municipios';

    protected $fillable = [
        'estado_id',
        'nombre',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relaciones
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }

    public function parroquias(): HasMany
    {
        return $this->hasMany(Parroquia::class);
    }

    public function instituciones(): HasMany
    {
        return $this->hasMany(Institucion::class);
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorEstado($query, $estadoId)
    {
        return $query->where('estado_id', $estadoId);
    }
}
