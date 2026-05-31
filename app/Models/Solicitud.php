<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';

    protected $fillable = [
        'usuario_id',
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
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function aprobador()
    {
        return $this->belongsTo(Usuario::class, 'aprobado_por');
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function responsable()
    {
        return $this->belongsTo(Responsable::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleSolicitud::class, 'solicitud_id');
    }

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'solicitud_id');
    }

    // Accesores
    public function getNombreEntidadAttribute()
    {
        if ($this->tipo_solicitante === 'interno' && $this->departamento) {
            return $this->departamento->nombre;
        } elseif ($this->tipo_solicitante === 'externo' && $this->institucion) {
            return $this->institucion->nombre;
        }
        return 'No especificado';
    }

    public function getResponsableEntidadAttribute()
    {
        if ($this->tipo_solicitante === 'interno' && $this->departamento) {
            return $this->departamento->representante;
        } elseif ($this->tipo_solicitante === 'externo' && $this->institucion) {
            return $this->institucion->representante;
        }
        return null;
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado_solicitud', 'pendiente');
    }

    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    public function scopeDelUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }
}
