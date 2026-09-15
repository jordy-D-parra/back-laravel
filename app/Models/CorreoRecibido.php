<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorreoRecibido extends Model
{
    protected $table = 'correos_recibidos';

    protected $fillable = [
        'message_id',
        'from_email',
        'from_name',
        'subject',
        'tipo',
        'body_text',
        'body_html',
        'attachments',
        'received_at',
        'leido',
        'procesado',
        'solicitud_id',
        'ficha_soporte_id',
        'datos_extraidos',
        'fecha_requerida_entrega',
        'usuario_id',
    ];

    protected $casts = [
        'attachments' => 'array',
        'datos_extraidos' => 'array',
        'received_at' => 'datetime',
        'fecha_requerida_entrega' => 'date',
        'leido' => 'boolean',
        'procesado' => 'boolean',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function fichaSoporte(): BelongsTo
    {
        return $this->belongsTo(FichaSoporte::class, 'ficha_soporte_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Scopes
    public function scopeNoLeidos($query)
    {
        return $query->where('leido', false);
    }

    public function scopeNoProcesados($query)
    {
        return $query->where('procesado', false);
    }

    public function scopeSoporte($query)
    {
        return $query->where('tipo', 'soporte');
    }

    public function scopeSolicitud($query)
    {
        return $query->where('tipo', 'solicitud');
    }
}