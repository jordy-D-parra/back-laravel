<?php

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

    /**
     * Marca del modelo (Dell, HP, Lenovo...).
     */
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class);
    }

    /**
     * Categoría del modelo (Laptop, Impresora, Monitor...).
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Componentes que DEBE tener este modelo (plantilla teórica).
     */
    public function modeloComponentes(): HasMany
    {
        return $this->hasMany(ModeloComponente::class, 'modelo_id');
    }

    /**
     * Activos físicos registrados de este modelo.
     */
    public function activos(): HasMany
    {
        return $this->hasMany(Activo::class, 'modelo_id');
    }

    /**
     * Cantidad de activos registrados de este modelo.
     */
    public function getActivosCountAttribute(): int
    {
        return $this->activos()->count();
    }

    /**
     * Cantidad de componentes definidos para este modelo.
     */
    public function getComponentesCountAttribute(): int
    {
        return $this->modeloComponentes()->count();
    }

    /**
     * Nombre completo: Marca + Modelo.
     */
    public function getNombreCompletoAttribute(): string
    {
        return $this->marca?->nombre . ' ' . $this->nombre;
    }
}
