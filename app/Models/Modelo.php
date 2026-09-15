<?php
// app/Models/Modelo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modelo extends Model
{
    use HasFactory;

    protected $table = 'modelos';

    protected $fillable = [
        'marca_id',
        'categoria_id',
        'nombre',
        'descripcion',
        'especificaciones',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function modeloComponentes(): HasMany
    {
        return $this->hasMany(ModeloComponente::class, 'modelo_id');
    }

    public function activos(): HasMany
    {
        return $this->hasMany(Activo::class, 'modelo_id');
    }

    public function getActivosCountAttribute(): int
    {
        return $this->activos()->count();
    }

    public function getComponentesCountAttribute(): int
    {
        return $this->modeloComponentes()->count();
    }

    public function getNombreCompletoAttribute(): string
    {
        return ($this->marca?->nombre ?? '') . ' ' . $this->nombre;
    }
}