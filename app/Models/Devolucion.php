<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    use HasFactory;

    protected $table = 'devolucion';

    protected $fillable = [
        'id_prestamo',
        'id_tecnico',
        'fecha_devolucion',
        'hora_devolucion',
        'tipo_devolucion',
        'observaciones'
    ];

    protected $casts = [
        'fecha_devolucion' => 'date'
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'id_prestamo');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'id_tecnico');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleDevolucion::class, 'id_devolucion');
    }
}
