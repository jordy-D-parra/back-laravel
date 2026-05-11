<?php
// app/Models/Modelo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modelo extends Model
{
    use HasFactory;

    protected $table = 'modelos';

    protected $fillable = [
        'marca_id',
        'categoria_id',
        'nombre',
        'descripcion',
        'especificaciones',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relaciones
    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function componentes()
    {
        return $this->belongsToMany(Componente::class, 'modelo_componente')
                    ->withPivot('cantidad', 'requerido')
                    ->withTimestamps();
    }

    public function activos()
    {
        return $this->hasMany(Activo::class, 'id_modelo');
    }

    // Accesor para nombre completo
    public function getNombreCompletoAttribute()
    {
        return $this->marca->nombre . ' ' . $this->nombre;
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
