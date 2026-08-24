<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prestamo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo', 'tipo_prestamo', 'estado',
        'observaciones', 'condiciones',
        'solicitud_id', 'departamento_id', 'institucion_id',
        'responsable_receptor_id', 'responsable_emisor_id',
        'usuario_registra_id',
        'fecha_prestamo', 'fecha_devolucion_esperada',
        'fecha_devolucion_real',
        'tiene_extension', 'total_extensiones',
    ];

    protected $casts = [
        'fecha_prestamo' => 'date',
        'fecha_devolucion_esperada' => 'date',
        'fecha_devolucion_real' => 'date',
        'tiene_extension' => 'boolean',
    ];

    // Relaciones
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    public function responsableReceptor()
    {
        return $this->belongsTo(Responsable::class, 'responsable_receptor_id');
    }

    public function responsableEmisor()
    {
        return $this->belongsTo(Responsable::class, 'responsable_emisor_id');
    }

    public function usuarioRegistra()
    {
        return $this->belongsTo(Usuario::class, 'usuario_registra_id');
    }

    public function detalles()
    {
        return $this->hasMany(PrestamoDetalle::class);
    }

    public function extensiones()
    {
        return $this->hasMany(PrestamoExtension::class);
    }

    // Helpers
    public function getDestinoNombreAttribute()
    {
        if ($this->departamento_id) {
            return $this->departamento->nombre;
        }
        if ($this->institucion_id) {
            return $this->institucion->nombre;
        }
        return 'No especificado';
    }

    public function getDiasRestantesAttribute()
    {
        if ($this->fecha_devolucion_real) return 0;
        return now()->startOfDay()->diffInDays($this->fecha_devolucion_esperada, false);
    }

    public function getEstaVencidoAttribute()
    {
        return !$this->fecha_devolucion_real && now()->startOfDay()->gt($this->fecha_devolucion_esperada);
    }

    // Generar código
    public static function generarCodigo()
    {
        $anio = date('Y');
        $ultimo = self::whereYear('created_at', $anio)->orderBy('id', 'desc')->first();
        $numero = $ultimo ? intval(substr($ultimo->codigo, -4)) + 1 : 1;
        return 'PRES-' . $anio . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
}
