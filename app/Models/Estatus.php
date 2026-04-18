<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estatus extends Model
{
    use HasFactory;

    protected $table = 'estatus';
    
    protected $fillable = [
        'descripcion',
        'color_badge',
        'permite_prestamo',
        'permite_solicitud',
        'es_terminal'
    ];

    protected $casts = [
        'permite_prestamo' => 'boolean',
        'permite_solicitud' => 'boolean',
        'es_terminal' => 'boolean',
    ];

    // Relación con activos
    public function activos()
    {
        return $this->hasMany(Activo::class, 'id_estatus');
    }
    
    // Accesor para obtener el nombre (para compatibilidad)
    public function getNombreAttribute()
    {
        return $this->descripcion;
    }
    
    // Accesor para obtener el color (para compatibilidad)
    public function getColorAttribute()
    {
        return $this->color_badge;
    }
}