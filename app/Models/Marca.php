<?php
// app/Models/Marca.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;

    protected $table = 'marcas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relaciones
    public function modelos()
    {
        return $this->hasMany(Modelo::class);
    }

    public function activos()
    {
        return $this->hasMany(Activo::class, 'id_marca');
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
