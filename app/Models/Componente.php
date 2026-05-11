<?php
// app/Models/Componente.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Componente extends Model
{
    use HasFactory;

    protected $table = 'componentes';

    protected $fillable = [
        'nombre',
        'tipo',
        'serial',
        'estado',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relaciones
    public function modelos()
    {
        return $this->belongsToMany(Modelo::class, 'modelo_componente')
                    ->withPivot('cantidad', 'requerido')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeDisponible($query)
    {
        return $query->where('estado', 'disponible');
    }

    public function scopeAsignado($query)
    {
        return $query->where('estado', 'asignado');
    }
}
