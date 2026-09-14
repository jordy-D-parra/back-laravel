<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Solicitud extends Model
{
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
        'estado_id',
        'municipio_id',
        'parroquia_id',
        'lugar_evento',
        'justificacion',
        'prioridad',
        'estado_solicitud',
        'observaciones',
        'aprobado_por',
        'fecha_aprobacion',
        'leida_por_admin',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_requerida' => 'date',
        'fecha_fin_estimada' => 'date',
        'fecha_aprobacion' => 'datetime',
        'leida_por_admin' => 'boolean',
    ];

    // ============ RELACIONES ============

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function parroquia(): BelongsTo
    {
        return $this->belongsTo(Parroquia::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleSolicitud::class, 'solicitud_id');
    }

    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class, 'solicitud_id');
    }

    // ============ ACCESORES ============

    public function getUbicacionEventoAttribute(): string
    {
        $partes = [];

        if ($this->lugar_evento) {
            $partes[] = $this->lugar_evento;
        }
        if ($this->parroquia) {
            $partes[] = $this->parroquia->nombre;
        }
        if ($this->municipio) {
            $partes[] = $this->municipio->nombre;
        }
        if ($this->estado) {
            $partes[] = $this->estado->nombre;
        }

        return implode(', ', $partes) ?: 'No especificada';
    }

    public function getNombreEntidadAttribute(): string
    {
        if ($this->tipo_solicitante === 'interno' && $this->departamento) {
            return $this->departamento->nombre;
        }
        if ($this->tipo_solicitante === 'externo' && $this->institucion) {
            return $this->institucion->nombre;
        }
        return 'No especificado';
    }

    // ============ SCOPES ============

    public function scopeNoLeidasPorAdmin($query)
    {
        return $query->where('leida_por_admin', false);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado_solicitud', 'pendiente');
    }
}