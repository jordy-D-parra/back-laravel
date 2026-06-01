<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'usuario',
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
        'id' => 'integer',
    ];

    // Para la autenticación usar 'usuario'
    public function getAuthIdentifierName()
    {
        return 'usuario';
    }

    // Para autenticación, devuelve el valor del campo 'usuario'
    public function getAuthIdentifier()
    {
        return $this->usuario;
    }

    // Para la sesión, devuelve el ID numérico
    public function getAuthIdentifierForStorage()
    {
        return (int) $this->id;
    }

    // Para que las relaciones usen el ID numérico
    public function getKey()
    {
        return (int) $this->id;
    }

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function hasPermission(string $permisoNombre): bool
    {
        return $this->rol?->permisos()->where('nombre', $permisoNombre)->exists() ?? false;
    }

    public function isRole(string $rolNombre): bool
    {
        return $this->rol?->nombre === $rolNombre;
    }
}
