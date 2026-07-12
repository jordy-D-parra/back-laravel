<?php

namespace App\Models;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;

class PrestamoExtension extends Model
{
    protected $table = 'prestamo_extensiones';

    protected $fillable = [
        'prestamo_id',
        'aprobado_por',
        'tipo',
        'fecha_anterior',
        'fecha_nueva',
        'motivo',
        'items_extendidos',
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class);
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(Usuario::class, 'aprobado_por');
    }
}
