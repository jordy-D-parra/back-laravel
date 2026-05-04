<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $table = 'prestamo';

    protected $fillable = [
        'id_solicitud',
        'id_tecnico',
        'id_responsable',
        'fecha_prestamo',
        'hora_prestamo',
        'fecha_retorno_estimada',
        'fecha_retorno_real',
        'tipo_prestamo',
        'estado_prestamo',
        'pendiente_completar',
        'observaciones',
        'aprobado_por'
    ];

    protected $casts = [
        'fecha_prestamo' => 'date',
        'fecha_retorno_estimada' => 'date',
        'fecha_retorno_real' => 'date',
        'hora_prestamo' => 'datetime:H:i:s'
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'id_solicitud');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'id_tecnico');
    }

    public function responsable()
    {
        return $this->belongsTo(Responsable::class, 'id_responsable');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePrestamo::class, 'id_prestamo');
    }

    public function extensiones()
    {
        return $this->hasMany(ExtensionPrestamo::class, 'prestamo_id');
    }

    public function devoluciones()
    {
        return $this->hasMany(Devolucion::class, 'id_prestamo');
    }
}
