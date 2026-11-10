<?php
// app/Models/Trabajador.php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trabajador extends Model
{
    use HasFactory;

    protected $table = 'trabajadores';

    protected $fillable = [
        'cedula',
        'nombre',
        'apellido',
        'departamento',
        'cargo',
        'especialidad',
        'telefono',
        'email',
    ];

    // ============================================================
    // RELACIONES
    // ============================================================

    public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class, 'trabajador_id');
    }

    // ============================================================
    // CÉDULA: normalización automática (BD guarda SOLO dígitos)
    // ============================================================

    /**
     * Al guardar: quita V-, E-, espacios, puntos y guiones.
     * En BD se guarda SOLO el número (ej: "12345678").
     *
     * Usa el helper de Usuario::normalizarCedula() para no duplicar lógica.
     */
    protected function cedula(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Usuario::normalizarCedula($value),
        );
    }

    /**
     * Devuelve la cédula SIN prefijo V-, lista para mostrar en vistas.
     * Ej: "12345678" => "12345678"
     *
     * Uso en Blade: {{ $trabajador->cedula_formateada }}
     */
    public function getCedulaFormateadaAttribute(): string
    {
        return $this->attributes['cedula'] ?? '';
    }

    /**
     * Scope: buscar trabajadores por cédula ignorando el prefijo.
     * Acepta "V-123", "v 123", "123", "123.456", etc.
     *
     * Uso: Trabajador::buscarPorCedula('V-12345678')->get();
     */
    public function scopeBuscarPorCedula($query, ?string $cedula)
    {
        $numero = Usuario::normalizarCedula($cedula);

        if ($numero === null) {
            return $query;
        }

        return $query->where('cedula', 'LIKE', "%{$numero}%");
    }

    // ============================================================
    // ACCESORES ÚTILES
    // ============================================================

    /**
     * Nombre completo listo para mostrar.
     * Uso: {{ $trabajador->nombre_completo }}
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim(($this->nombre ?? '') . ' ' . ($this->apellido ?? ''));
    }

    /**
     * Iniciales para avatares.
     * Uso: {{ $trabajador->iniciales }} → "JP"
     */
    public function getInicialesAttribute(): string
    {
        $n = $this->nombre ? mb_substr($this->nombre, 0, 1) : '';
        $a = $this->apellido ? mb_substr($this->apellido, 0, 1) : '';

        return strtoupper($n . $a);
    }
}