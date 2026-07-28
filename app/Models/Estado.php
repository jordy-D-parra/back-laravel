<?php
// app/Models/Estado.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estado extends Model
{
    protected $table = 'estados';

    protected $fillable = [
        'nombre',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function municipios(): HasMany
    {
        return $this->hasMany(Municipio::class);
    }

    public function instituciones(): HasMany
    {
        return $this->hasMany(Institucion::class);
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
