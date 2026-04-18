<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar la tabla antes de insertar
        DB::table('rol')->truncate();
        
        // Insertar roles con diferentes niveles
        DB::table('rol')->insert([
            [
                'nombre' => 'super_admin',
                'descripcion' => 'Super Administrador - Control total del sistema',
                'nivel' => 10, 
                'es_activo' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'admin',
                'descripcion' => 'Administrador - Gestión de usuarios y contenido',
                'nivel' => 5, 
                'es_activo' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'usuario',
                'descripcion' => 'Usuario regular - Acceso básico al sistema',
                'nivel' => 1,
                'es_activo' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}