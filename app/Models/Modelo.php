<?php
// app/Models/Modelo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modelo extends Model
{
    use HasFactory;

    protected $fillable = [
        'marca_id',
        'categoria_id',
        'nombre',
        'descripcion',
        'especificaciones',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
}