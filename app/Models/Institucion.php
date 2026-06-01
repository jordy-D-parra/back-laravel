<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    protected $table = 'instituciones';

    protected $fillable = [
        'nombre',
        'informacion',
        // ELIMINADO: 'representante',
        'ubicacion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relación: una institución tiene muchos departamentos
    public function departamentos()
    {
        return $this->hasMany(Departamento::class, 'institucion_id');
    }

    // Relación: una institución tiene muchos responsables
    public function responsables()
    {
        return $this->hasMany(Responsable::class, 'institucion_id');
    }

    // Responsables directos de la institución (sin departamento)
    public function responsablesDirectos()
    {
        return $this->hasMany(Responsable::class, 'institucion_id')
                    ->whereNull('departamento_id');
    }

    // Scope: instituciones activas
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    // Scope: búsqueda
    public function scopeBuscar($query, $termino)
    {
        if ($termino) {
            return $query->where(function($q) use ($termino) {
                $q->where('nombre', 'ILIKE', "%{$termino}%")
                  ->orWhere('ubicacion', 'ILIKE', "%{$termino}%");
            });
        }
        return $query;
    }

    // Accesor para obtener el representante principal (helper)
    public function getRepresentantePrincipalAttribute()
    {
        return $this->responsablesDirectos()->first();
    }
}
