<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periferico extends Model
{
    use HasFactory;

    protected $table = 'periferico';

    protected $fillable = [
        'nombre',
        'tipo',
        'marca',
        'modelo',
        'serial',
        'cantidad_total',
        'cantidad_disponible',
        'ubicacion',
        'observaciones'
    ];

    protected $casts = [
        'cantidad_total' => 'integer',
        'cantidad_disponible' => 'integer'
    ];

    // Relaciones
    public function detalleSolicitudes()
    {
        return $this->hasMany(DetalleSolicitud::class, 'periferico_id');
    }

    public function detallePrestamos()
    {
        return $this->hasMany(DetallePrestamo::class, 'periferico_id');
    }

    public function detalleDevoluciones()
    {
        return $this->hasMany(DetalleDevolucion::class, 'periferico_id');
    }

    // Scope para obtener solo disponibles
    public function scopeDisponibles($query)
    {
        return $query->where('cantidad_disponible', '>', 0);
    }
}
