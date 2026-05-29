<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activo extends Model
{
    protected $table = 'activos';

    protected $fillable = [
        'serial',
        'modelo_id',
        'id_estatus',
        'institucion_id',
        'departamento_id',
        'responsable_id',
        'ubicacion',
        'fecha_adquisicion',
        'fecha_fin_garantia',
        'vida_util_anos',
        'especificaciones_tecnicas',
        'agrupacion',
        'observaciones',
    ];

    protected $casts = [
        'especificaciones_tecnicas' => 'json',
        'fecha_adquisicion' => 'date',
        'fecha_fin_garantia' => 'date',
        'vida_util_anos' => 'integer',
    ];

    /**
     * Modelo del activo (de aquí se obtiene categoría y marca).
     */
    public function modelo(): BelongsTo
    {
        return $this->belongsTo(Modelo::class);
    }

    /**
     * Estatus actual (Disponible, Prestado, En reparación, etc.).
     */
    public function estatus(): BelongsTo
    {
        return $this->belongsTo(Estatus::class, 'id_estatus');
    }

    /**
     * Institución donde se encuentra actualmente.
     */
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    /**
     * Departamento donde se encuentra (opcional).
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    /**
     * Responsable actual del activo.
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }

    /**
     * Componentes instalados en este activo.
     */
    public function componentes(): HasMany
    {
        return $this->hasMany(Componente::class, 'activo_id');
    }

    /**
     * Acceso rápido a la categoría a través del modelo.
     */
    public function getCategoriaAttribute()
    {
        return $this->modelo?->categoria;
    }

    /**
     * Acceso rápido a la marca a través del modelo.
     */
    public function getMarcaAttribute()
    {
        return $this->modelo?->marca;
    }

    /**
     * Scope: Solo activos disponibles para préstamo.
     */
    public function scopeDisponibles($query)
    {
        return $query->whereHas('estatus', function ($q) {
            $q->where('permite_prestamo', true);
        });
    }

    /**
     * Scope: Solo activos prestados.
     */
    public function scopePrestados($query)
    {
        return $query->where('id_estatus', function ($sub) {
            $sub->select('id')->from('estatus')->where('descripcion', 'Prestado')->limit(1);
        });
    }

    /**
     * Verifica si el activo está disponible para préstamo.
     */
    public function estaDisponible(): bool
    {
        return $this->estatus?->permite_prestamo ?? false;
    }

    /**
     * Verifica si la garantía está vencida.
     */
    public function garantiaVencida(): bool
    {
        return $this->fecha_fin_garantia && $this->fecha_fin_garantia->isPast();
    }
    
    public function fichasSoporte(): HasMany
{
    return $this->hasMany(FichaSoporte::class, 'activo_id');
}
}
