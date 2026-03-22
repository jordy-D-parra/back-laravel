<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'action', 'description', 'ip_address',
        'user_agent', 'old_data', 'new_data'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper para mostrar acción con ícono
    public function getActionIconAttribute()
    {
        return match($this->action) {
            'login' => '🔓',
            'logout' => '🔒',
            'register' => '📝',
            'change_role' => '👑',
            'change_password' => '🔐',
            'update_profile' => '✏️',
            'update_security' => '❓',
            default => '📌'
        };
    }

    // Helper para color de badge
    public function getActionColorAttribute()
    {
        return match($this->action) {
            'login' => 'success',
            'logout' => 'secondary',
            'register' => 'info',
            'change_role' => 'warning',
            'change_password' => 'danger',
            'update_profile' => 'primary',
            'update_security' => 'dark',
            default => 'light'
        };
    }
}
