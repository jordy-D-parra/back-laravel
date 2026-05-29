<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trabajador extends Model
{
    use HasFactory;

    protected $table = 'trabajadores';

    protected $fillable = [
        'cedula',
        'nombre',
        'apellido',
        'departamento',
        'cargo',
        'especialidad',
        'telefono'
    ];

    public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class, 'trabajador_id');
    }
}
