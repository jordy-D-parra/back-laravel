<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionSistema extends Model
{
    protected $table = 'notificacion_sistema';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'titulo',
        'mensaje',
        'datos_extra',
        'leida',
        'fecha_envio'
    ];

    protected $casts = [
        'datos_extra' => 'array',
        'leida' => 'boolean',
        'fecha_envio' => 'datetime'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function marcarComoLeida()
    {
        $this->update(['leida' => true]);
    }
}
