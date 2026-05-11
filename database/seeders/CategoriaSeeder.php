<?php
// database/seeders/CategoriaSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run()
    {
        $categorias = [
            ['nombre' => 'Laptop', 'descripcion' => 'Computadoras portátiles'],
            ['nombre' => 'Desktop', 'descripcion' => 'Computadoras de escritorio'],
            ['nombre' => 'Monitor', 'descripcion' => 'Pantallas y monitores'],
            ['nombre' => 'Impresora', 'descripcion' => 'Impresoras y multifuncionales'],
            ['nombre' => 'Proyector', 'descripcion' => 'Proyectores y videowalls'],
            ['nombre' => 'Tablet', 'descripcion' => 'Tablets y dispositivos móviles'],
            ['nombre' => 'Servidor', 'descripcion' => 'Servidores y racks'],
            ['nombre' => 'Switch', 'descripcion' => 'Switches de red'],
            ['nombre' => 'Router', 'descripcion' => 'Routers y access points'],
            ['nombre' => 'Almacenamiento', 'descripcion' => 'Discos y NAS'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}
