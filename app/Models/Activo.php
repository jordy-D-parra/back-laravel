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
        'id_estatus',
        'id_tipo_activo',
        'cantidad',
        'ubicacion',
        'disponible_desde',
        'fecha_adquisicion',
        'vida_util_anos',
        'fecha_fin_garantia',
        'especificaciones_tecnicas',
        'observaciones'
    ];

    protected $casts = [
        'disponible_desde' => 'date',
        'fecha_adquisicion' => 'date',
        'fecha_fin_garantia' => 'date',
        'cantidad' => 'integer',
        'vida_util_anos' => 'integer',
        'especificaciones_tecnicas' => 'array' // Automáticamente JSON -> Array
    ];

    protected $appends = [
        'estado_vida_util',
        'fecha_fin_vida_util'
    ];

    // ========== RELACIONES ==========
    
    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'id_estatus');
    }

    public function tipoActivo()
    {
        return $this->belongsTo(TipoActivo::class, 'id_tipo_activo');
    }

    public function detallesSolicitud()
    {
        return $this->hasMany(DetalleSolicitud::class, 'id_activo');
    }

    public function fichasSoporte()
    {
        return $this->hasMany(FichaSoporte::class, 'activo_id');
    }

    public function componentes()
    {
        return $this->belongsToMany(Componente::class, 'activo_componente')
            ->withPivot('cantidad', 'fecha_instalacion', 'fecha_retiro', 'observaciones')
            ->withTimestamps();
    }

    // ========== ATTRIBUTES (MUTATORS & ACCESSORS) ==========

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

    // ========== SCOPES ==========

    /**
     * Scope para activos disponibles
     */
    public function scopeDisponible($query)
    {
        return $query->where('id_estatus', 1);
    }

    /**
     * Scope para activos con vida útil próxima a vencer (6 meses o menos)
     */
    public function scopeVidaUtilProximaAVencer($query)
    {
        return $query->whereNotNull('fecha_adquisicion')
            ->whereNotNull('vida_util_anos')
            ->whereRaw("DATE_ADD(fecha_adquisicion, INTERVAL vida_util_anos YEAR) <= DATE_ADD(NOW(), INTERVAL 6 MONTH)")
            ->whereRaw("DATE_ADD(fecha_adquisicion, INTERVAL vida_util_anos YEAR) >= NOW()");
    }

    /**
     * Scope para activos con vida útil vencida
     */
    public function scopeVidaUtilVencida($query)
    {
        return $query->whereNotNull('fecha_adquisicion')
            ->whereNotNull('vida_util_anos')
            ->whereRaw("DATE_ADD(fecha_adquisicion, INTERVAL vida_util_anos YEAR) < NOW()");
    }

    /**
     * Scope para activos en garantía
     */
    public function scopeEnGarantia($query)
    {
        return $query->whereNotNull('fecha_fin_garantia')
            ->where('fecha_fin_garantia', '>=', now());
    }

    // ========== MÉTODOS ÚTILES ==========

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
}