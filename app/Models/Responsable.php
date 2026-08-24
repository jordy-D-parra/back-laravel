<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Responsable extends Model
{
    protected $table = 'responsables';

    protected $fillable = [
        'nombre',
        'documento',
        'telefono',
        'email',
        'direccion',
        'cargo',
        'activo',
        'institucion_id',
        'departamento_id'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relación: pertenece a una institución
    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
    }

    // Relación: pertenece a un departamento (opcional)
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    // Scope: responsables activos
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
                  ->orWhere('documento', 'ILIKE', "%{$termino}%")
                  ->orWhere('email', 'ILIKE', "%{$termino}%")
                  ->orWhere('cargo', 'ILIKE', "%{$termino}%");
            });
        }
        return $query;
    }

    // Scope: con departamento
    public function scopeConDepartamento($query)
    {
        return $query->whereNotNull('departamento_id');
    }

    // Scope: sin departamento (responsables directos de institución)
    public function scopeSinDepartamento($query)
    {
        return $query->whereNull('departamento_id');
    }

    // Scope: filtrar por institución
    public function scopePorInstitucion($query, $institucionId)
    {
        if ($institucionId) {
            return $query->where('institucion_id', $institucionId);
        }
        return $query;
    }

    // Scope: filtrar por departamento
    public function scopePorDepartamento($query, $departamentoId)
    {
        if ($departamentoId) {
            return $query->where('departamento_id', $departamentoId);
        }
        return $query;
    }
}
