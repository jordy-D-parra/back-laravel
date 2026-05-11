<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModeloSeeder extends Seeder
{
    public function run()
    {
        $modelos = [
            // HP Laptops (marca_id=1)
            ['marca_id' => 1, 'nombre' => 'ProBook 450', 'descripcion' => 'Laptop empresarial', 'activo' => true],
            ['marca_id' => 1, 'nombre' => 'EliteBook 840', 'descripcion' => 'Laptop premium', 'activo' => true],
            ['marca_id' => 1, 'nombre' => 'Pavilion 15', 'descripcion' => 'Laptop de consumo', 'activo' => true],

            // Dell Laptops (marca_id=2)
            ['marca_id' => 2, 'nombre' => 'Latitude 3420', 'descripcion' => 'Laptop empresarial', 'activo' => true],
            ['marca_id' => 2, 'nombre' => 'XPS 13', 'descripcion' => 'Ultrabook premium', 'activo' => true],
            ['marca_id' => 2, 'nombre' => 'Inspiron 15', 'descripcion' => 'Laptop de consumo', 'activo' => true],

            // Samsung Monitores (marca_id=7)
            ['marca_id' => 7, 'nombre' => 'Odyssey G7', 'descripcion' => 'Monitor gaming 27"', 'activo' => true],
            ['marca_id' => 7, 'nombre' => 'T55', 'descripcion' => 'Monitor curvo 24"', 'activo' => true],

            // LG Monitores (marca_id=8)
            ['marca_id' => 8, 'nombre' => 'UltraGear 27', 'descripcion' => 'Monitor gaming 27"', 'activo' => true],
            ['marca_id' => 8, 'nombre' => 'UltraFine', 'descripcion' => 'Monitor profesional', 'activo' => true],

            // Epson Impresoras (marca_id=13)
            ['marca_id' => 13, 'nombre' => 'EcoTank L3150', 'descripcion' => 'Impresora multifuncional', 'activo' => true],
            ['marca_id' => 13, 'nombre' => 'WorkForce Pro', 'descripcion' => 'Impresora empresarial', 'activo' => true],
        ];

        foreach ($modelos as $modelo) {
            DB::table('modelos')->updateOrInsert(
                ['marca_id' => $modelo['marca_id'], 'nombre' => $modelo['nombre']],
                array_merge($modelo, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }
}
