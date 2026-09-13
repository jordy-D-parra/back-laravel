<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleDevolucion extends Model
{
    use HasFactory;

    protected $table = 'detalle_devolucion';

    protected $fillable = [
        'id_devolucion',
        'id_activo',
        'periferico_id',
        'tipo_item',
        'cantidad',
        'estado_devuelto',
        'observaciones'
    ];

    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class, 'id_devolucion');
    }

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo');
    }

    public function periferico()
    {
        return $this->belongsTo(Periferico::class, 'periferico_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estatus::class, 'estado_devuelto');
    }
}
