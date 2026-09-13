<?php
// app/Models/Categoria.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
        'marca_id' // 👈 AGREGAR ESTE CAMPO
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // 👇 RELACIÓN CON MARCA
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class);
    }

    public function modelos(): HasMany
    {
        return $this->hasMany(Modelo::class);
    }

    public function getModelosCountAttribute(): int
    {
        return $this->modelos()->count();
    }
}