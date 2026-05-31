<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    // Tabla referenciada
    protected $table = 'prestamos';

    // Orden de atributos para mostrar en la tabla
    // Referencia: resources/views/admin/prestamo/index.blade.php
    // # | Responsable | Activo | Fecha Salida | Fecha Devolución | Estado | Acciones

    protected $fillable = [
        'solicitud_id',
        'responsable_id',      // Responsable
        'activo_id',           // Activo
        'fecha_salida',        // Fecha Salida
        'fecha_devolucion',    // Fecha Devolución
        'estado',              // Estado: pendiente, entregado, vencido, devuelto
        'observaciones',       // Observaciones (no se muestra directo pero es útil)
    ];

    // Relaciones
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function responsable()
    {
        return $this->belongsTo(Responsable::class, 'responsable_id');
    }

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }
}
