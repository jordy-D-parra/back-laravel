<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    use HasFactory;

    protected $table = 'institucion';

    protected $fillable = [
        'nombre',
        'informacion',
        'representante',
        'ubicacion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relaciones
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'institucion_id');
    }

    public function responsables()
    {
        return $this->hasMany(Responsable::class, 'institucion_id');
    }

    // Scope para instituciones activas
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
