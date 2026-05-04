<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'rol';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'nivel',
        'es_activo',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'es_activo' => 'boolean',
        'nivel' => 'integer',
    ];

    /**
     * Get the users for the role.
     */
    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_rol');
    }
}
