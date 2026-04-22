<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitud';

    protected $fillable = [
        'id_solicitante',
        'tipo_solicitante',
        'institucion_id',
        'responsable_id',
        'oficio_adjunto',
        'fecha_solicitud',
        'fecha_requerida',
        'fecha_fin_estimada',
        'justificacion',
        'prioridad',
        'estado_solicitud',
        'observaciones',
        'aprobado_por',
        'fecha_aprobacion',
        'fecha_prestamo',           // 👈 AGREGADO
        'prestamo_id',              // 👈 AGREGADO
        'observaciones_aprobacion', // 👈 AGREGADO
        'observaciones_rechazo',    // 👈 AGREGADO
        'observaciones_espera',     // 👈 AGREGADO
        'fecha_espera'              // 👈 AGREGADO
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_requerida' => 'date',
        'fecha_fin_estimada' => 'date',
        'fecha_aprobacion' => 'datetime',
        'fecha_prestamo' => 'datetime',  // 👈 AGREGADO
        'fecha_espera' => 'datetime'      // 👈 AGREGADO
    ];

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'id_solicitante');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
    }

    public function responsable()
    {
        return $this->belongsTo(Responsable::class, 'responsable_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleSolicitud::class, 'id_solicitud');
    }

    public function prestamo()
    {
        return $this->hasOne(Prestamo::class, 'id_solicitud');
    }

    // 👈 RELACIÓN PARA OBTENER EL PRÉSTAMO ACTIVO (opcional)
    public function prestamoActivo()
    {
        return $this->hasOne(Prestamo::class, 'id_solicitud')->where('estado_prestamo', 'activo');
    }
}
