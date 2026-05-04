<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'usuario'; // ← IMPORTANTE: apunta a la tabla usuario


    protected $connection = 'pgsql';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'cedula',           // ← cédula en lugar de email
        'password',
        'nombre',
        'apellido',
        'departamento',
        'cargo',
        'departamento_id',
        'pregunta_seguridad_1',
        'respuesta_1',
        'pregunta_seguridad_2',
        'respuesta_2',
        'id_rol',
        'estado_usuario',
        'fecha_solicitud',
        'fecha_aprobacion',
        'aprobado_por',
        'ultimo_login',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'datetime',
            'fecha_aprobacion' => 'datetime',
            'ultimo_login' => 'datetime',
            'activo' => 'boolean',
            'password' => 'hashed',
        ];
    }


    // ========== RELACIONES ==========

    /**
     * Relación con el rol
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    // ========== MÉTODOS DE ROLES ==========

    /**
     * Verificar si el usuario es Super Administrador
     */
    public function isSuperAdmin()
    {
        return $this->rol && $this->rol->nombre === 'super_admin';
    }

    /**
     * Verificar si el usuario es Administrador
     */
    public function isAdmin()
    {
        return $this->rol && $this->rol->nombre === 'admin';
    }

    /**
     * Verificar si el usuario es Trabajador
     */
    public function isWorker()
    {
        return $this->rol && $this->rol->nombre === 'worker';
    }

    /**
     * Verificar si el usuario es Usuario Base
     */
    public function isUser()
    {
        return $this->rol && $this->rol->nombre === 'user';
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($roleName)
    {
        return $this->rol && $this->rol->nombre === $roleName;
    }

    /**
     * Verificar si el usuario tiene acceso a un rol específico
     */
    public function hasAccess($requiredRole)
    {
        $roleHierarchy = [
            'user' => 1,
            'worker' => 2,
            'admin' => 3,
            'super_admin' => 4,
        ];

        $userRoleName = $this->rol ? $this->rol->nombre : 'user';
        $userLevel = $roleHierarchy[$userRoleName] ?? 1;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 1;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Obtener el nombre legible del rol
     */
    public function getRoleNameAttribute()
    {
        if (!$this->rol) {
            return 'Sin rol';
        }

        return match($this->rol->nombre) {
            'super_admin' => 'Super Administrador',
            'admin' => 'Administrador',
            'worker' => 'Trabajador',
            'user' => 'Usuario Base',
            default => 'Sin rol'
        };
    }

    /**
     * Obtener el badge HTML del rol para Bootstrap
     */
    public function getRoleBadgeAttribute()
    {
        if (!$this->rol) {
            return '<span class="badge bg-dark">Sin rol</span>';
        }

        return match($this->rol->nombre) {
            'super_admin' => '<span class="badge bg-danger">👑 Super Admin</span>',
            'admin' => '<span class="badge bg-warning text-dark">⚙️ Administrador</span>',
            'worker' => '<span class="badge bg-primary">🔧 Trabajador</span>',
            'user' => '<span class="badge bg-secondary">👤 Usuario Base</span>',
            default => '<span class="badge bg-dark">Sin rol</span>'
        };
    }

    /**
     * Obtener nombre completo
     */
    public function getFullNameAttribute()
    {
        return $this->nombre . ' ' . $this->apellido;
    }
}
