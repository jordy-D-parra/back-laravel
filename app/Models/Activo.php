<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activo extends Model
{
    protected $table = 'activos';

    protected $fillable = [
        'serial',
        'modelo_id',
        'id_estatus',
        'institucion_id',
        'departamento_id',
        'responsable_id',
        'ubicacion',
        'fecha_adquisicion',
        'fecha_fin_garantia',
        'vida_util_anos',
        'especificaciones_tecnicas',
        'agrupacion',
        'observaciones',
        'reservado_en_prestamo_id',   // ✅ AGREGADO
    ];

    protected $casts = [
        'especificaciones_tecnicas' => 'json',
        'fecha_adquisicion' => 'date',
        'fecha_fin_garantia' => 'date',
        'vida_util_anos' => 'integer',
    ];

    // ============================================================
    // RELACIONES
    // ============================================================

    /**
     * Modelo del activo (de aquí se obtiene categoría y marca).
     */
    public function modelo(): BelongsTo
    {
        return $this->belongsTo(Modelo::class);
    }

    /**
     * Estatus actual (Disponible, Prestado, En reparación, etc.).
     */
    public function estatus(): BelongsTo
    {
        return $this->belongsTo(Estatus::class, 'id_estatus');
    }

    /**
     * Institución donde se encuentra actualmente.
     */
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    /**
     * Departamento donde se encuentra (opcional).
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    /**
     * Responsable actual del activo.
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }

    /**
     * Componentes instalados en este activo.
     */
    public function componentes(): HasMany
    {
        return $this->hasMany(Componente::class, 'activo_id');
    }

    /**
     * Fichas de soporte del activo.
     */
    public function fichasSoporte(): HasMany
    {
        return $this->hasMany(FichaSoporte::class, 'activo_id');
    }

    /**
     * Detalles de préstamos donde aparece este activo (polimórfico).
     */
    public function prestamoDetalles(): HasMany
    {
        return $this->hasMany(PrestamoDetalle::class, 'prestable_id')
            ->where('prestable_type', Activo::class);
    }

    /**
     * Préstamo en el que está reservado (si aplica).
     */
    public function reservadoEnPrestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class, 'reservado_en_prestamo_id');
    }

    // ============================================================
    // ACCESORES
    // ============================================================

    /**
     * Acceso rápido a la categoría a través del modelo.
     */
    public function getCategoriaAttribute()
    {
        return $this->modelo?->categoria;
    }

    /**
     * Acceso rápido a la marca a través del modelo.
     */
    public function getMarcaAttribute()
    {
        return $this->modelo?->marca;
    }

    // ============================================================
    // SCOPES
    // ============================================================

    /**
     * Scope: Solo activos prestados.
     */
    public function scopePrestados($query)
    {
        return $query->whereHas('estatus', function ($q) {
            $q->where('descripcion', 'Prestado');
        });
    }

    /**
     * Scope: Activos disponibles (no reservados, permiten préstamo).
     */
    public function scopeDisponibles($query)
    {
        return $query->whereHas('estatus', function ($q) {
            $q->where('permite_prestamo', true);
        })->whereNull('reservado_en_prestamo_id');
    }

    /**
     * Scope: Activos en préstamos activos.
     */
    public function scopeEnPrestamoActivo($query)
    {
        return $query->whereHas('prestamoDetalles.prestamo', function ($q) {
            $q->whereIn('estado', ['entregado', 'extendido', 'aprobado']);
        });
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Verifica si el activo está disponible para préstamo.
     */
    public function estaDisponible(): bool
    {
        return $this->estatus?->permite_prestamo ?? false;
    }

    /**
     * Verifica si está reservado.
     */
    public function estaReservado(): bool
    {
        return !is_null($this->reservado_en_prestamo_id);
    }

    /**
     * Verifica si está disponible para préstamo (no reservado Y no prestado).
     */
    public function estaDisponibleParaPrestamo(): bool
    {
        return !$this->estaReservado() && $this->estaDisponible();
    }

    /**
     * Marca el activo como prestado.
     */
    public function marcarComoPrestado(): bool
    {
        $estatus = Estatus::where('descripcion', 'Prestado')->first();

        if (!$estatus) {
            return false;
        }

        return $this->update(['id_estatus' => $estatus->id]);
    }

    /**
     * Marca el activo como disponible.
     */
    public function marcarComoDisponible(): bool
    {
        $estatus = Estatus::where('descripcion', 'Disponible')->first()
            ?? Estatus::permitenPrestamo()->orderBy('id')->first();

        if (!$estatus) {
            return false;
        }

        return $this->update(['id_estatus' => $estatus->id]);
    }

    /**
     * Reserva el activo para un préstamo.
     */
    public function reservar(int $prestamoId): bool
    {
        return $this->update(['reservado_en_prestamo_id' => $prestamoId]);
    }

    /**
     * Libera la reserva del activo.
     */
    public function liberarReserva(): bool
    {
        return $this->update(['reservado_en_prestamo_id' => null]);
    }

    /**
     * Verifica si la garantía está vencida.
     */
    public function garantiaVencida(): bool
    {
        return $this->fecha_fin_garantia && $this->fecha_fin_garantia->isPast();
    }
}