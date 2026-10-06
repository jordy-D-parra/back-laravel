<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

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

    /**
     * Caché en memoria de los permisos del rol.
     * Evita ejecutar N consultas SQL por cada verificación.
     */
    protected ?array $permisosCache = null;

    // ============================================================
    // RELACIONES
    // ============================================================

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

    // ============================================================
    // SISTEMA DE PERMISOS
    // ============================================================

    /**
     * Devuelve el array de nombres de permisos del usuario (con caché en memoria).
     */
    public function getPermisosNombres(): array
    {
        if ($this->permisosCache === null) {
            try {
                $this->permisosCache = $this->rol
                    ? $this->rol->permisos()->pluck('nombre')->toArray()
                    : [];
            } catch (\Throwable $e) {
                Log::error('Error al cargar permisos del usuario: ' . $e->getMessage(), [
                    'usuario_id' => $this->id,
                ]);
                $this->permisosCache = [];
            }
        }

        return $this->permisosCache;
    }

    /**
     * Verifica si el usuario tiene un permiso específico.
     * Admin y super_admin siempre pasan (bypass).
     */
    public function hasPermission(string $permisoNombre): bool
    {
        if ($this->isRole('admin') || $this->isRole('super_admin')) {
            return true;
        }

        return in_array($permisoNombre, $this->getPermisosNombres(), true);
    }

    /**
     * Verifica si el usuario tiene AL MENOS UNO de los permisos dados.
     */
    public function hasAnyPermission(array $permisos): bool
    {
        if ($this->isRole('admin') || $this->isRole('super_admin')) {
            return true;
        }

        return count(array_intersect($permisos, $this->getPermisosNombres())) > 0;
    }

    /**
     * Verifica si el usuario tiene TODOS los permisos dados.
     */
    public function hasAllPermissions(array $permisos): bool
    {
        if ($this->isRole('admin') || $this->isRole('super_admin')) {
            return true;
        }

        return count(array_diff($permisos, $this->getPermisosNombres())) === 0;
    }

    /**
     * Verifica si el usuario tiene un rol específico.
     */
    public function isRole(string $rolNombre): bool
    {
        return $this->rol?->nombre === $rolNombre;
    }

    /**
     * Verifica si el usuario tiene ALGUNO de los roles dados.
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->rol?->nombre, $roles, true);
    }

    // Atajos de roles comunes
    public function isSuperAdmin(): bool { return $this->isRole('super_admin'); }
    public function isAdmin(): bool { return $this->isRole('admin'); }
    public function isIngeniero(): bool { return $this->isRole('ingeniero'); }
    public function isTecnico(): bool { return $this->isRole('tecnico'); }
    public function isSecretaria(): bool { return $this->isRole('secretaria'); }

    /**
     * Limpia la caché de permisos (útil si cambias el rol en runtime).
     */
    public function refreshPermisos(): void
    {
        $this->permisosCache = null;
        $this->unsetRelation('rol');
    }

    // ============================================================
    // ACCESORES
    // ============================================================

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

    public function getEmailAttribute($value)
    {
        if ($value) {
            return $value;
        }

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