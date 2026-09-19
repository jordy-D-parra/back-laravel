<?php
// app/Models/Usuario.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'usuario',
        'password',
        'must_change_password',
        'status',
        'ultimo_login',
        'trabajador_id',
        'rol_id',
        'foto_perfil',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'must_change_password' => 'boolean',
        'ultimo_login' => 'datetime',
    ];

    // ============ ACCESOR DE FOTO DE PERFIL ============
public function getFotoPerfilUrlAttribute(): string
{
    if ($this->foto_perfil && file_exists(storage_path('app/public/' . $this->foto_perfil))) {
        return asset('storage/' . $this->foto_perfil);
    }
    return $this->generarAvatarIniciales();
}

public function generarAvatarIniciales(): string
{
    $nombre = $this->trabajador?->nombre ?? $this->usuario;
    $apellido = $this->trabajador?->apellido ?? '';
    $iniciales = strtoupper(substr($nombre, 0, 1) . substr($apellido, 0, 1));
    
    // Generar color consistente basado en el ID
    $colores = ['1e3c72', '2a5298', '0d6efd', '198754', 'dc3545', '6f42c1', 'fd7e14', '20c997'];
    $color = $colores[$this->id % count($colores)];
    
    return "data:image/svg+xml;base64," . base64_encode(
        '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">
            <rect width="80" height="80" fill="#' . $color . '"/>
            <text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" 
                  font-family="Segoe UI, sans-serif" font-size="32" font-weight="700" 
                  fill="#ffffff">' . $iniciales . '</text>
        </svg>'
    );
}

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    public function notificacionesNoLeidas(): HasMany
    {
        return $this->notificaciones()->where('leida', false);
    }

    // Verificar si tiene un permiso específico
    public function hasPermission(string $permisoNombre): bool
    {
        return $this->rol?->permisos()->where('nombre', $permisoNombre)->exists() ?? false;
    }

    // Verificar si es un rol específico
    public function isRole(string $rolNombre): bool
    {
        return $this->rol?->nombre === $rolNombre;
    }

 public function getEmailAttribute($value)
{
    // Si el usuario tiene email directamente, usarlo
    if ($value) {
        return $value;
    }

    // Si no, buscar en el trabajador
    if ($this->trabajador && $this->trabajador->email) {
        return $this->trabajador->email;
    }

    return null;
}
public function getNombreCompletoAttribute(): string
{
    if ($this->trabajador) {
        return trim($this->trabajador->nombre . ' ' . $this->trabajador->apellido);
    }
    return $this->usuario;
}
}