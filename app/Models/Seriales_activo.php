<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seriales_activo extends Model
{
    protected $table = 'seriales_activo';
    
    protected $fillable = [
        'activo_id',
        'serial',
        'estado',
        'asignado_a',
        'fecha_asignacion'
    ];
    
    protected $casts = [
        'fecha_asignacion' => 'date'
    ];
    
    public function activo()
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }
    
    public function asignadoA()
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }
}