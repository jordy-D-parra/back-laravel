<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_log';
    
    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'ip_address',
        'user_agent',
        'operation',
        'table_name',
        'record_id',
        'field_name',
        'old_data',
        'new_data',
        'description',
        'request_method',
        'request_url'
    ];
    
    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    // Accesor para el color de la acción
    public function getActionColorAttribute()
    {
        return match($this->operation) {
            'CREATE', 'STORE', 'REGISTRAR' => 'success',
            'UPDATE', 'EDIT', 'CAMBIAR' => 'warning',
            'DELETE', 'DESTROY', 'ELIMINAR' => 'danger',
            'LOGIN' => 'info',
            'LOGOUT' => 'secondary',
            'EXPORT', 'IMPORT' => 'primary',
            default => 'secondary'
        };
    }
    
    // Accesor para el ícono de la acción
    public function getActionIconAttribute()
    {
        return match($this->operation) {
            'CREATE', 'STORE', 'REGISTRAR' => '➕',
            'UPDATE', 'EDIT', 'CAMBIAR' => '✏️',
            'DELETE', 'DESTROY', 'ELIMINAR' => '🗑️',
            'LOGIN' => '🔐',
            'LOGOUT' => '🚪',
            'EXPORT' => '📤',
            'IMPORT' => '📥',
            default => '📌'
        };
    }
    
    // Accesor para la acción formateada
    public function getActionAttribute()
    {
        return $this->operation;
    }
}