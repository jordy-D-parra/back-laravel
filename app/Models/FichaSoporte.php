<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichaSoporte extends Model
{
    use HasFactory;

    protected $table = 'ficha_soporte';
    
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'activo_id',
        'tecnico_id',
        'usuario_reporta_id',
        'serial_asignado',        // 🔥 AGREGADO
        'equipo_externo_nombre',  // 🔥 AGREGADO
        'fecha_ingreso',
        'fecha_entrega',          // 🔥 CAMBIADO (antes fecha_salida)
        'diagnostico',
        'observaciones',
        'trabajo_realizado',
        'costo_reparacion',       // 🔥 AGREGADO
        'estado',
        'creado_por'              // 🔥 AGREGADO
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'fecha_entrega' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function activo()
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function usuarioReporta()
    {
        return $this->belongsTo(User::class, 'usuario_reporta_id');
    }

    public function componentes()
    {
        return $this->hasMany(FichaSoporteDetalle::class, 'ficha_soporte_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}