<?php
// app/Models/TipoActivo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoActivo extends Model
{
    use HasFactory;

    // Especificar el nombre exacto de la tabla
    protected $table = 'tipo_activo';

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    // Relación con activos
    public function activos()
    {
        return $this->hasMany(Activo::class, 'id_tipo_activo');
    }
}