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

    // Scope: responsables directos de institución (sin departamento)
    public function scopeDirectosDeInstitucion($query)
    {
        return $query->whereNull('departamento_id');
    }

    // Scope: responsables de un departamento específico
    public function scopeDeDepartamento($query, $departamentoId)
    {
        return $query->where('departamento_id', $departamentoId);
    }

    // Scope: responsables activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
