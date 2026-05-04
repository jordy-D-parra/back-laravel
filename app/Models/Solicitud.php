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
        'departamento_id',
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
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_requerida' => 'date',
        'fecha_fin_estimada' => 'date',
        'fecha_aprobacion' => 'datetime',
    ];

    // Relaciones
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

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
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

    public function prestamoActivo()
    {
        return $this->hasOne(Prestamo::class, 'id_solicitud')->where('estado_prestamo', 'activo');
    }

    // Obtener el nombre de la entidad (departamento o institución)
    public function getNombreEntidadAttribute()
    {
        if ($this->tipo_solicitante === 'interno' && $this->departamento) {
            return $this->departamento->nombre;
        } elseif ($this->tipo_solicitante === 'externo' && $this->institucion) {
            return $this->institucion->nombre;
        }
        return 'No especificado';
    }

    // Obtener el nombre del responsable
    public function getNombreResponsableAttribute()
    {
        return $this->responsable ? $this->responsable->nombre : 'No especificado';
    }

    // Scope para filtros
    public function scopePendientes($query)
    {
        return $query->where('estado_solicitud', 'pendiente');
    }

    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

     public function getResponsableAttribute()
{
    if ($this->tipo_solicitante === 'interno' && $this->departamento) {
        return $this->departamento->responsable;
    } elseif ($this->tipo_solicitante === 'externo' && $this->institucion) {
        return $this->institucion->responsable;
    }
    return null;
}


}
