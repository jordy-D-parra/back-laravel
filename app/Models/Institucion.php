<?php
// app/Models/Institucion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institucion extends Model
{
    protected $table = 'instituciones';

    protected $fillable = [
        'nombre',
        'informacion',
        'representante',
        'estado_id',
        'municipio_id',
        'parroquia_id',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relaciones de geolocalización
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function parroquia(): BelongsTo
    {
        return $this->belongsTo(Parroquia::class);
    }

    // Relaciones existentes
    public function departamentos(): HasMany
    {
        return $this->hasMany(Departamento::class, 'institucion_id');
    }

    public function responsables(): HasMany
    {
        return $this->hasMany(Responsable::class, 'institucion_id');
    }

    public function responsablesDirectos(): HasMany
    {
        return $this->hasMany(Responsable::class, 'institucion_id')
                    ->whereNull('departamento_id');
    }

    // Accesor para ubicación completa
    public function getUbicacionCompletaAttribute(): string
    {
        $partes = [];

        if ($this->parroquia) {
            $partes[] = $this->parroquia->nombre;
        }
        if ($this->municipio) {
            $partes[] = $this->municipio->nombre;
        }
        if ($this->estado) {
            $partes[] = $this->estado->nombre;
        }

        return implode(', ', $partes) ?: 'No especificada';
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeBuscar($query, $termino)
    {
        if ($termino) {
            return $query->where(function($q) use ($termino) {
                $q->where('nombre', 'ILIKE', "%{$termino}%")
                  ->orWhere('representante', 'ILIKE', "%{$termino}%")
                  ->orWhereHas('estado', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$termino}%"))
                  ->orWhereHas('municipio', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$termino}%"))
                  ->orWhereHas('parroquia', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$termino}%"));
            });
        }
        return $query;
    }

    public function scopePorEstado($query, $estadoId)
    {
        if ($estadoId) {
            return $query->where('estado_id', $estadoId);
        }
        return $query;
    }

    public function scopePorMunicipio($query, $municipioId)
    {
        if ($municipioId) {
            return $query->where('municipio_id', $municipioId);
        }
        return $query;
    }
}
