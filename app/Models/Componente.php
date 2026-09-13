<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Componente extends Model
{
    protected $table = 'componentes';

    protected $fillable = [
        'tipo',
        'modelo_componente_id',
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
     * Tipo de componente según modelo (nullable si es genérico).
     */
    public function modeloComponente(): BelongsTo
    {
        return $this->belongsTo(ModeloComponente::class, 'modelo_componente_id');
    }

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
     * Verifica si el componente está disponible para préstamo.
     */
    public function estaDisponible(): bool
    {
        return $this->estado === 'en_bodega';
    }

    public function marcarComoPrestado(): bool
    {
        return $this->update([
            'estado' => 'prestado',
            'activo_id' => null,
            'fecha_retiro' => now(),
        ]);
    }

    public function marcarComoDevuelto(): bool
    {
        return $this->update([
            'estado' => 'en_bodega',
            'fecha_retiro' => now(),
        ]);
    }

    // Agregar relación
public function reservadoEnPrestamo()
{
    return $this->belongsTo(Prestamo::class, 'reservado_en_prestamo_id');
}

// Verificar si está reservado
public function estaReservado(): bool
{
    return !is_null($this->reservado_en_prestamo_id);
}

// Verificar si está disponible (NO reservado Y en bodega)
public function estaDisponibleParaPrestamo(): bool
{
    return !$this->estaReservado() && $this->estado === 'en_bodega';
}

// Métodos para reservar/liberar
public function reservar(int $prestamoId): bool
{
    return $this->update(['reservado_en_prestamo_id' => $prestamoId]);
}

public function liberarReserva(): bool
{
    return $this->update(['reservado_en_prestamo_id' => null]);
}

// Modificar el scope en_bodega para excluir reservados
public function scopeEnBodega($query)
{
    return $query->where('estado', 'en_bodega')
                 ->whereNull('reservado_en_prestamo_id');
}

// Scope para componentes en préstamos activos
public function scopeEnPrestamoActivo($query)
{
    return $query->whereHas('prestamos', function($q) {
        $q->whereIn('estado', ['entregado', 'extendido', 'aprobado']);
    });
}
}
