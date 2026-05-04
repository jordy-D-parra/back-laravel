<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Responsable extends Model
{
    use HasFactory;

    protected $table = 'responsable';

    protected $fillable = [
        'nombre',
        'departamento',
        'tipo',
        'institucion_id',
        'documento',
        'telefono',
        'email',
        'direccion'
    ];

    // Relaciones
    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
    }

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'id_responsable');
    }

    // Scope por tipo
    public function scopeInternos($query)
    {
        return $query->where('tipo', 'interno');
    }

    public function scopeExternos($query)
    {
        return $query->where('tipo', 'externo');
    }
}
