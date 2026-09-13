<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';

    protected $fillable = [
    'nombre',
    'informacion',
    'representante',
    'ubicacion',
    'activo',
    'institucion_id'
];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relación: pertenece a una institución
    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
    }

    // Relación: tiene muchos responsables
    public function responsables()
    {
        return $this->hasMany(Responsable::class, 'departamento_id');
    }

    // Scope: departamentos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope: búsqueda
    public function scopeBuscar($query, $termino)
    {
        if ($termino) {
            return $query->where(function($q) use ($termino) {
                $q->where('nombre', 'ILIKE', "%{$termino}%")
                  ->orWhere('representante', 'ILIKE', "%{$termino}%")
                  ->orWhere('ubicacion', 'ILIKE', "%{$termino}%");
            });
        }
        return $query;
    }

    // Scope: filtrar por institución
    public function scopePorInstitucion($query, $institucionId)
    {
        if ($institucionId) {
            return $query->where('institucion_id', $institucionId);
        }
        return $query;
    }
}
