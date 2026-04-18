<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoActivo extends Model
{
    protected $table = 'tipo_activo';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'icono',
        'color'
    ];
    
    public function activos()
    {
        return $this->hasMany(Activo::class, 'id_tipo_activo');
    }
    
    public function getColorBadgeAttribute()
    {
        return $this->color ?? 'secondary';
    }
}