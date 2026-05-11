<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Activo extends Model
{
    use HasFactory;

    protected $table = 'activo';

    protected $fillable = [
        'serial',
        'tipo_equipo',
        'marca_modelo',
        'id_categoria',
        'id_marca',
        'id_modelo',
        'id_tipo_activo',
        'id_estatus',
        'cantidad',
        'ubicacion',
        'disponible_desde',
        'fecha_adquisicion',
        'vida_util_anos',
        'fecha_fin_garantia',
        'valor_compra',
        'observaciones',
        'especificaciones_tecnicas'
    ];

    protected $casts = [
        'disponible_desde' => 'date',
        'fecha_adquisicion' => 'date',
        'fecha_fin_garantia' => 'date',
        'vida_util_anos' => 'integer',
        'cantidad' => 'integer',
        'valor_compra' => 'decimal:2',
        'especificaciones_tecnicas' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = [
        'estado_vida_util',
        'fecha_fin_vida_util',
        'nombre_completo'
    ];

    // ========== RELACIONES ==========

    /**
     * Relación con el estatus del activo
     */
    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'id_estatus');
    }

    /**
     * Relación con la categoría del activo
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    /**
     * Relación con la marca del activo
     */
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca');
    }

    /**
     * Relación con el modelo del activo
     */
    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'id_modelo');
    }

    /**
     * Relación con tipo_activo (para compatibilidad con código existente)
     */
    public function tipoActivo()
    {
        return $this->belongsTo(TipoActivo::class, 'id_tipo_activo');
    }

    /**
     * Relación con detalles de solicitud
     */
    public function detallesSolicitud()
    {
        return $this->hasMany(DetalleSolicitud::class, 'id_activo');
    }

    /**
     * Relación con fichas de soporte
     */
    public function fichasSoporte()
    {
        return $this->hasMany(FichaSoporte::class, 'activo_id');
    }

    /**
     * Relación con componentes
     */
    public function componentes()
    {
        return $this->belongsToMany(Componente::class, 'activo_componente')
            ->withPivot('cantidad', 'fecha_instalacion', 'fecha_retiro', 'observaciones')
            ->withTimestamps();
    }

    /**
     * Relación con préstamos
     */
    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'id_activo');
    }

    // ========== EVENTOS ==========

    protected static function boot()
    {
        parent::boot();

        // Antes de crear, asignar marca_modelo automáticamente si está vacío
        static::creating(function ($activo) {
            if (empty($activo->marca_modelo) && $activo->id_marca && $activo->id_modelo) {
                $marca = Marca::find($activo->id_marca);
                $modelo = Modelo::find($activo->id_modelo);
                if ($marca && $modelo) {
                    $activo->marca_modelo = $marca->nombre . ' ' . $modelo->nombre;
                } else {
                    $activo->marca_modelo = '';
                }
            }

            // Valor por defecto para tipo_equipo
            if (empty($activo->tipo_equipo)) {
                $activo->tipo_equipo = 'principal';
            }

            // Valor por defecto para cantidad
            if (empty($activo->cantidad)) {
                $activo->cantidad = 1;
            }
        });

        // Antes de actualizar, actualizar marca_modelo si cambia marca o modelo
        static::updating(function ($activo) {
            if (($activo->isDirty('id_marca') || $activo->isDirty('id_modelo')) &&
                $activo->id_marca && $activo->id_modelo) {
                $marca = Marca::find($activo->id_marca);
                $modelo = Modelo::find($activo->id_modelo);
                if ($marca && $modelo) {
                    $activo->marca_modelo = $marca->nombre . ' ' . $modelo->nombre;
                }
            }
        });
    }

    // ========== ACCESSORS & MUTATORS ==========

    /**
     * Get the formatted vida útil status
     */
    protected function estadoVidaUtil(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->fecha_adquisicion || !$this->vida_util_anos) {
                    return [
                        'estado' => 'desconocido',
                        'clase' => 'secondary',
                        'mensaje' => 'No definida'
                    ];
                }

                $fechaFin = $this->fecha_adquisicion->copy()->addYears($this->vida_util_anos);
                $hoy = now();
                $diasRestantes = $hoy->diffInDays($fechaFin, false);
                $mesesRestantes = floor($diasRestantes / 30);

                if ($diasRestantes < 0) {
                    return [
                        'estado' => 'vencida',
                        'clase' => 'danger',
                        'mensaje' => 'Vida útil vencida',
                        'dias_restantes' => abs($diasRestantes),
                        'meses_restantes' => abs($mesesRestantes)
                    ];
                } elseif ($mesesRestantes <= 6) {
                    return [
                        'estado' => 'proximo_a_vencer',
                        'clase' => 'warning',
                        'mensaje' => "Próximo a vencer ({$mesesRestantes} meses)",
                        'dias_restantes' => $diasRestantes,
                        'meses_restantes' => $mesesRestantes
                    ];
                } elseif ($mesesRestantes <= 12) {
                    return [
                        'estado' => 'media_vida',
                        'clase' => 'info',
                        'mensaje' => "{$mesesRestantes} meses restantes",
                        'dias_restantes' => $diasRestantes,
                        'meses_restantes' => $mesesRestantes
                    ];
                } else {
                    $añosRestantes = round($mesesRestantes / 12, 1);
                    return [
                        'estado' => 'buena',
                        'clase' => 'success',
                        'mensaje' => "{$añosRestantes} años restantes",
                        'dias_restantes' => $diasRestantes,
                        'meses_restantes' => $mesesRestantes
                    ];
                }
            }
        );
    }

    /**
     * Get the estimated end of life date
     */
    protected function fechaFinVidaUtil(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->fecha_adquisicion || !$this->vida_util_anos) {
                    return null;
                }
                return $this->fecha_adquisicion->copy()->addYears($this->vida_util_anos);
            }
        );
    }

    /**
     * Get garantía status
     */
    protected function estadoGarantia(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->fecha_fin_garantia) {
                    return [
                        'estado' => 'sin_garantia',
                        'clase' => 'secondary',
                        'mensaje' => 'Sin garantía registrada'
                    ];
                }

                $hoy = now();
                if ($this->fecha_fin_garantia < $hoy) {
                    return [
                        'estado' => 'vencida',
                        'clase' => 'danger',
                        'mensaje' => 'Garantía vencida'
                    ];
                }

                $diasRestantes = $hoy->diffInDays($this->fecha_fin_garantia);
                return [
                    'estado' => 'vigente',
                    'clase' => 'success',
                    'mensaje' => "Garantía vigente ({$diasRestantes} días restantes)"
                ];
            }
        );
    }

    /**
     * Get nombre completo (marca + modelo)
     */
    protected function nombreCompleto(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->marca && $this->modelo) {
                    return $this->marca->nombre . ' ' . $this->modelo->nombre;
                }
                return $this->marca_modelo ?? 'No especificado';
            }
        );
    }

    // ========== SCOPES ==========

    /**
     * Scope para activos disponibles
     */
    public function scopeDisponible($query)
    {
        return $query->where('id_estatus', 1);
    }

    /**
     * Scope para activos en préstamo
     */
    public function scopeEnPrestamo($query)
    {
        return $query->where('id_estatus', 2);
    }

    /**
     * Scope para activos en mantenimiento
     */
    public function scopeEnMantenimiento($query)
    {
        return $query->where('id_estatus', 3);
    }

    /**
     * Scope para activos dados de baja
     */
    public function scopeDadoDeBaja($query)
    {
        return $query->where('id_estatus', 4);
    }

    /**
     * Scope para activos con vida útil próxima a vencer (6 meses o menos)
     */
    public function scopeVidaUtilProximaAVencer($query)
    {
        return $query->whereNotNull('fecha_adquisicion')
            ->whereNotNull('vida_util_anos')
            ->whereRaw("fecha_adquisicion + (vida_util_anos || ' years')::interval <= NOW() + INTERVAL '6 months'")
            ->whereRaw("fecha_adquisicion + (vida_util_anos || ' years')::interval >= NOW()");
    }

    /**
     * Scope para activos con vida útil vencida
     */
    public function scopeVidaUtilVencida($query)
    {
        return $query->whereNotNull('fecha_adquisicion')
            ->whereNotNull('vida_util_anos')
            ->whereRaw("fecha_adquisicion + (vida_util_anos || ' years')::interval < NOW()");
    }

    /**
     * Scope para activos en garantía
     */
    public function scopeEnGarantia($query)
    {
        return $query->whereNotNull('fecha_fin_garantia')
            ->where('fecha_fin_garantia', '>=', now());
    }

    /**
     * Scope para activos por categoría
     */
    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('id_categoria', $categoriaId);
    }

    /**
     * Scope para activos por marca
     */
    public function scopePorMarca($query, $marcaId)
    {
        return $query->where('id_marca', $marcaId);
    }

    /**
     * Scope para activos por modelo
     */
    public function scopePorModelo($query, $modeloId)
    {
        return $query->where('id_modelo', $modeloId);
    }

    // ========== MÉTODOS ÚTILES ==========

    /**
     * Verificar si el activo está disponible
     */
    public function isDisponible(): bool
    {
        return $this->id_estatus === 1;
    }

    /**
     * Verificar si el activo está en préstamo
     */
    public function isEnPrestamo(): bool
    {
        return $this->id_estatus === 2;
    }

    /**
     * Verificar si el activo está en mantenimiento
     */
    public function isEnMantenimiento(): bool
    {
        return $this->id_estatus === 3;
    }

    /**
     * Verificar si el activo está dado de baja
     */
    public function isDadoDeBaja(): bool
    {
        return $this->id_estatus === 4;
    }

    /**
     * Verificar si el activo tiene garantía vigente
     */
    public function tieneGarantiaVigente(): bool
    {
        $estado = $this->estado_garantia;
        return $estado['estado'] === 'vigente';
    }

    /**
     * Verificar si el activo está en buen estado de vida útil
     */
    public function tieneVidaUtilBuena(): bool
    {
        $estado = $this->estado_vida_util;
        return $estado['estado'] === 'buena';
    }

    /**
     * Verificar si el activo está próximo a vencer su vida útil
     */
    public function estaProximoAVencer(): bool
    {
        $estado = $this->estado_vida_util;
        return $estado['estado'] === 'proximo_a_vencer';
    }

    /**
     * Verificar si la vida útil ya venció
     */
    public function vidaUtilVencida(): bool
    {
        $estado = $this->estado_vida_util;
        return $estado['estado'] === 'vencida';
    }

    /**
     * Obtener la fecha estimada de fin de vida útil
     */
    public function getFechaFinVidaUtil()
    {
        if (!$this->fecha_adquisicion || !$this->vida_util_anos) {
            return null;
        }
        return $this->fecha_adquisicion->copy()->addYears($this->vida_util_anos);
    }

    /**
     * Obtener el porcentaje de vida útil restante
     */
    public function getPorcentajeVidaUtil(): ?float
    {
        if (!$this->fecha_adquisicion || !$this->vida_util_anos) {
            return null;
        }

        $fechaFin = $this->getFechaFinVidaUtil();
        $totalDias = $this->fecha_adquisicion->diffInDays($fechaFin);
        $diasTranscurridos = $this->fecha_adquisicion->diffInDays(now());

        if ($totalDias <= 0 || $diasTranscurridos >= $totalDias) {
            return 0;
        }

        return round((($totalDias - $diasTranscurridos) / $totalDias) * 100, 2);
    }
}
