<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtensionPrestamo extends Model
{
    use HasFactory;

    protected $table = 'extension_prestamo';

    protected $fillable = [
        'prestamo_id',
        'solicitada_por',
        'nueva_fecha_devolucion',
        'motivo',
        'estado',
        'aprobada_por',
        'fecha_aprobacion'
    ];

    protected $casts = [
        'nueva_fecha_devolucion' => 'date',
        'fecha_aprobacion' => 'datetime'
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'prestamo_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitada_por');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobada_por');
    }
}
