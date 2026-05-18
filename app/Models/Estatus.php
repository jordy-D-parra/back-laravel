<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estatus extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'estatus';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'descripcion',
        'color_badge',
        'permite_prestamo',
        'permite_solicitud',
        'es_terminal',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permite_prestamo' => 'boolean',
        'permite_solicitud' => 'boolean',
        'es_terminal' => 'boolean',
    ];

    /**
     * Relación: Un estatus puede tener muchos activos.
     */
    public function activos(): HasMany
    {
        return $this->hasMany(Activo::class, 'id_estatus');
    }

    /**
     * Relación: Un estatus puede tener muchos componentes.
     */
    public function componentes(): HasMany
    {
        return $this->hasMany(Componente::class, 'id_estatus');
    }

    /**
     * Verifica si este estatus permite préstamos.
     */
    public function permitePrestamo(): bool
    {
        return $this->permite_prestamo;
    }

    /**
     * Verifica si este estatus permite solicitudes.
     */
    public function permiteSolicitud(): bool
    {
        return $this->permite_solicitud;
    }

    /**
     * Verifica si es un estado terminal (no se puede cambiar).
     */
    public function esTerminal(): bool
    {
        return $this->es_terminal;
    }

    /**
     * Scope: Solo estatus que permiten préstamo.
     */
    public function scopePermitenPrestamo($query)
    {
        return $query->where('permite_prestamo', true);
    }

    /**
     * Scope: Solo estatus que permiten solicitud.
     */
    public function scopePermitenSolicitud($query)
    {
        return $query->where('permite_solicitud', true);
    }

    /**
     * Scope: Solo estatus activos (no terminales).
     */
    public function scopeNoTerminales($query)
    {
        return $query->where('es_terminal', false);
    }
}