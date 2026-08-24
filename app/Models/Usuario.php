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
        'email', // ✅ AGREGAR EMAIL
        'password',
        'must_change_password',
        'status',
        'ultimo_login',
        'trabajador_id',
        'rol_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'must_change_password' => 'boolean',
        'ultimo_login' => 'datetime',
    ];

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
}