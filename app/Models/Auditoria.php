<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    protected $fillable = [
        'uuid', 'usuario_id', 'usuario_nombre', 'accion', 'modulo',
        'tabla_afectada', 'registro_id', 'datos_originales', 'datos_nuevos',
        'descripcion', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'datos_originales' => 'array',
        'datos_nuevos' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    // Scopes para facilitar búsquedas
    public function scopeModulo($query, $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    public function scopeAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopeFechaDesde($query, $fecha)
    {
        return $query->whereDate('created_at', '>=', $fecha);
    }

    public function scopeFechaHasta($query, $fecha)
    {
        return $query->whereDate('created_at', '<=', $fecha);
    }
}