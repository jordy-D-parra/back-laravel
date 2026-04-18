<?php
// app/Models/Activo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activo extends Model
{
    protected $table = 'activo';
    
    protected $fillable = [
        'serial',
        'tipo_equipo',
        'marca_modelo',
        'capacidad',
        'id_estatus',
        'id_tipo_activo',
        'cantidad',
        'ubicacion',
        'disponible_desde',
        'fecha_adquisicion',
        'valor_compra',
        'detalles_tecnicos',
        'observaciones'
    ];
    
    protected $casts = [
        'fecha_adquisicion' => 'date',
        'disponible_desde' => 'date',
        'valor_compra' => 'decimal:2'
    ];
    
    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'id_estatus');
    }
    
    public function tipoActivo()
    {
        return $this->belongsTo(TipoActivo::class, 'id_tipo_activo');
    }
    
 
    public function seriales()
    {
        return $this->hasMany(Seriales_activo::class, 'activo_id');
    }
}