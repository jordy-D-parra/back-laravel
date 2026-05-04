<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'departamento';

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
        return $this->hasMany(Solicitud::class, 'departamento_id');
    }

    // Scope para departamentos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
