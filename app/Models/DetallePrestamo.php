<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePrestamo extends Model
{
    use HasFactory;

    protected $table = 'detalle_prestamo';

    protected $fillable = [
        'id_prestamo',
        'id_activo',
        'periferico_id',
        'tipo_item',
        'cantidad',
        'estado_al_prestar',
        'observaciones',
        'devuelto'
    ];

    protected $casts = [
        'devuelto' => 'boolean'
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'id_prestamo');
    }

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo');
    }

    public function periferico()
    {
        return $this->belongsTo(Periferico::class, 'periferico_id');
    }

    public function estadoPrestamo()
    {
        return $this->belongsTo(Estatus::class, 'estado_al_prestar');
    }
}
