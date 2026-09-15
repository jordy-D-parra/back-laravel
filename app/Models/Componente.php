<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Componente extends Model
{
    protected $table = 'componentes';

    protected $fillable = [
        'tipo',
        'marca',
        'modelo',
        'serial',
        'capacidad',
        'especificaciones',
        'estado',
        'activo_id',
        'institucion_id',
        'departamento_id',
        'responsable_id',
        'ubicacion',
        'fecha_instalacion',
        'fecha_retiro',
        'observaciones',
    ];

    protected $casts = [
        'especificaciones' => 'json',
        'fecha_instalacion' => 'datetime',
        'fecha_retiro' => 'datetime',
    ];

    /**
     * Activo donde está instalado (nullable si está en bodega o prestado solo).
     */
    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class);
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
     * Responsable actual del componente.
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }

    /**
     * Prestamo reservado actualmente (si está reservado).
     */
    public function reservadoEnPrestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class, 'reservado_en_prestamo_id');
    }

    /**
     * Scope: Solo componentes instalados en un activo.
     */
    public function scopeInstalados($query)
    {
        return $query->where('estado', 'instalado');
    }

    /**
     * Scope: Solo componentes prestados.
     */
    public function scopePrestados($query)
    {
        return $query->where('estado', 'prestado');
    }

    /**
     * Scope: Componentes en bodega (excluye reservados).
     */
    public function scopeEnBodega($query)
    {
        return $query->where('estado', 'en_bodega')
                     ->whereNull('reservado_en_prestamo_id');
    }

    /**
     * Scope: Componentes en préstamos activos.
     */
    public function scopeEnPrestamoActivo($query)
    {
        return $query->whereHas('prestamos', function($q) {
            $q->whereIn('estado', ['entregado', 'extendido', 'aprobado']);
        });
    }

    /**
     * Verifica si el componente está disponible para préstamo.
     */
    public function estaDisponible(): bool
    {
        return $this->estado === 'en_bodega';
    }

    /**
     * Verifica si está reservado.
     */
    public function estaReservado(): bool
    {
        return !is_null($this->reservado_en_prestamo_id);
    }

    /**
     * Verifica si está disponible para préstamo (no reservado Y en bodega).
     */
    public function estaDisponibleParaPrestamo(): bool
    {
        return !$this->estaReservado() && $this->estado === 'en_bodega';
    }

    /**
     * Marca el componente como prestado.
     */
    public function marcarComoPrestado(): bool
    {
        return $this->update([
            'estado' => 'prestado',
            'activo_id' => null,
            'fecha_retiro' => now(),
        ]);
    }

    /**
     * Marca el componente como devuelto.
     */
    public function marcarComoDevuelto(): bool
    {
        return $this->update([
            'estado' => 'en_bodega',
            'fecha_retiro' => now(),
        ]);
    }

    /**
     * Reserva el componente para un préstamo.
     */
    public function reservar(int $prestamoId): bool
    {
        return $this->update(['reservado_en_prestamo_id' => $prestamoId]);
    }

    /**
     * Libera la reserva del componente.
     */
    public function liberarReserva(): bool
    {
        return $this->update(['reservado_en_prestamo_id' => null]);
    }
}