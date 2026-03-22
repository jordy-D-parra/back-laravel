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
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'security_question_1',
        'security_question_2',
        'role',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ========== MÉTODOS DE ROLES ==========

    /**
     * Verificar si el usuario es Super Administrador
     */
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    /**
     * Verificar si el usuario es Trabajador
     */
    public function isWorker()
    {
        return $this->role === 'worker';
    }

    /**
     * Verificar si el usuario es Usuario Base
     */
    public function isUser()
    {
        return $this->role === 'user';
    }

    /**
     * Verificar si el usuario tiene acceso a un rol específico
     *
     * @param string $requiredRole - El rol mínimo requerido (user, worker, super_admin)
     * @return bool
     */
    public function hasAccess($requiredRole)
    {
        $roleHierarchy = [
            'user' => 1,
            'worker' => 2,
            'super_admin' => 3,
        ];

        $userLevel = $roleHierarchy[$this->role] ?? 1;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 1;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Obtener el nombre legible del rol
     */
    public function getRoleNameAttribute()
    {
        return match($this->role) {
            'super_admin' => 'Super Administrador',
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
        return match($this->role) {
            'super_admin' => '<span class="badge bg-danger">👑 Super Admin</span>',
            'worker' => '<span class="badge bg-primary">🔧 Trabajador</span>',
            'user' => '<span class="badge bg-secondary">👤 Usuario Base</span>',
            default => '<span class="badge bg-dark">Sin rol</span>'
        };
    }

    // ========== RELACIONES ==========

    /**
     * Relación con las respuestas de seguridad
     */
    public function securityAnswers()
    {
        return $this->hasMany(SecurityAnswer::class);
    }
}
