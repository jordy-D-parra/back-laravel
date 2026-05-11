<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarcaSeeder extends Seeder
{
    public function run()
    {
        $marcas = [
            // Categoría Laptop (id=1)
            ['nombre' => 'HP', 'categoria_id' => 1, 'descripcion' => 'Hewlett-Packard', 'activo' => true],
            ['nombre' => 'Dell', 'categoria_id' => 1, 'descripcion' => 'Computadoras y periféricos', 'activo' => true],
            ['nombre' => 'Lenovo', 'categoria_id' => 1, 'descripcion' => 'Laptops y equipos de oficina', 'activo' => true],
            ['nombre' => 'Apple', 'categoria_id' => 1, 'descripcion' => 'Equipos de alta gama', 'activo' => true],
            ['nombre' => 'Acer', 'categoria_id' => 1, 'descripcion' => 'Computadoras', 'activo' => true],
            ['nombre' => 'Asus', 'categoria_id' => 1, 'descripcion' => 'Componentes y laptops', 'activo' => true],

            // Categoría Monitor (id=2)
            ['nombre' => 'Samsung', 'categoria_id' => 2, 'descripcion' => 'Electrónica y monitores', 'activo' => true],
            ['nombre' => 'LG', 'categoria_id' => 2, 'descripcion' => 'Electrónica', 'activo' => true],
            ['nombre' => 'Dell', 'categoria_id' => 2, 'descripcion' => 'Monitores', 'activo' => true],

            // Categoría Impresora (id=3)
            ['nombre' => 'Epson', 'categoria_id' => 3, 'descripcion' => 'Impresoras y proyectores', 'activo' => true],
            ['nombre' => 'HP', 'categoria_id' => 3, 'descripcion' => 'Impresoras', 'activo' => true],
            ['nombre' => 'Brother', 'categoria_id' => 3, 'descripcion' => 'Impresoras', 'activo' => true],
            ['nombre' => 'Canon', 'categoria_id' => 3, 'descripcion' => 'Impresoras', 'activo' => true],
        ];

        foreach ($marcas as $marca) {
            DB::table('marcas')->updateOrInsert(
                ['nombre' => $marca['nombre']],
                array_merge($marca, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }
}
