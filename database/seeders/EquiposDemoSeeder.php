<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Modelo;

class EquiposDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creando datos de demostración para catálogo de equipos...');

        // ==================== MARCAS ====================
        $marcas = [
            ['nombre' => 'Dell', 'descripcion' => 'Fabricante estadounidense de equipos informáticos'],
            ['nombre' => 'HP', 'descripcion' => 'Hewlett-Packard, fabricante de hardware y periféricos'],
            ['nombre' => 'Lenovo', 'descripcion' => 'Fabricante chino de computadoras y dispositivos'],
            ['nombre' => 'Apple', 'descripcion' => 'Fabricante de MacBooks, iMacs y dispositivos'],
            ['nombre' => 'Acer', 'descripcion' => 'Fabricante taiwanés de computadoras'],
            ['nombre' => 'Asus', 'descripcion' => 'Fabricante taiwanés de hardware'],
            ['nombre' => 'Samsung', 'descripcion' => 'Fabricante surcoreano de electrónicos'],
            ['nombre' => 'Kingston', 'descripcion' => 'Fabricante de memorias RAM y almacenamiento'],
            ['nombre' => 'Crucial', 'descripcion' => 'Marca de Micron para memorias y SSD'],
            ['nombre' => 'Western Digital', 'descripcion' => 'Fabricante de discos duros y SSD'],
            ['nombre' => 'Seagate', 'descripcion' => 'Fabricante de discos duros'],
            ['nombre' => 'Logitech', 'descripcion' => 'Fabricante de periféricos y accesorios'],
            ['nombre' => 'Microsoft', 'descripcion' => 'Fabricante de software y hardware'],
            ['nombre' => 'Epson', 'descripcion' => 'Fabricante de impresoras y proyectores'],
            ['nombre' => 'Canon', 'descripcion' => 'Fabricante de impresoras y cámaras'],
            ['nombre' => 'Brother', 'descripcion' => 'Fabricante de impresoras y equipos de oficina'],
            ['nombre' => 'APC', 'descripcion' => 'Fabricante de UPS y reguladores'],
            ['nombre' => 'Cisco', 'descripcion' => 'Fabricante de equipos de red'],
            ['nombre' => 'TP-Link', 'descripcion' => 'Fabricante de equipos de red'],
            ['nombre' => 'ViewSonic', 'descripcion' => 'Fabricante de monitores y proyectores'],
        ];

        foreach ($marcas as $mar) {
            Marca::updateOrCreate(['nombre' => $mar['nombre']], $mar);
        }
        $this->command->info('✅ ' . count($marcas) . ' marcas creadas');

        // ==================== CATEGORÍAS GLOBALES ====================
        // ✅ Ahora son INDEPENDIENTES de la marca
        $categorias = [
            ['nombre' => 'Laptop', 'descripcion' => 'Computadoras portátiles'],
            ['nombre' => 'Computadora de Escritorio', 'descripcion' => 'CPU, torres y estaciones de trabajo'],
            ['nombre' => 'Monitor', 'descripcion' => 'Pantallas y monitores'],
            ['nombre' => 'Servidor', 'descripcion' => 'Servidores y equipos de rack'],
            ['nombre' => 'Impresora', 'descripcion' => 'Impresoras láser y de tinta'],
            ['nombre' => 'Escáner', 'descripcion' => 'Escáneres de documentos'],
            ['nombre' => 'Proyector', 'descripcion' => 'Proyectores multimedia'],
            ['nombre' => 'Tablet', 'descripcion' => 'Tabletas electrónicas'],
            ['nombre' => 'Disco Duro / SSD', 'descripcion' => 'Unidades de almacenamiento'],
            ['nombre' => 'Memoria RAM', 'descripcion' => 'Módulos de memoria'],
            ['nombre' => 'Teclado', 'descripcion' => 'Teclados y periféricos de entrada'],
            ['nombre' => 'Mouse', 'descripcion' => 'Ratones y dispositivos señaladores'],
            ['nombre' => 'Cámara Web', 'descripcion' => 'Cámaras para videoconferencia'],
            ['nombre' => 'Parlantes / Cornetas', 'descripcion' => 'Altavoces y sistemas de audio'],
            ['nombre' => 'Router / Switch', 'descripcion' => 'Equipos de red'],
            ['nombre' => 'Teléfono IP', 'descripcion' => 'Teléfonos VoIP'],
            ['nombre' => 'UPS / Regulador', 'descripcion' => 'Sistemas de energía ininterrumpida'],
            ['nombre' => 'Cable / Adaptador', 'descripcion' => 'Cables y adaptadores varios'],
        ];

        // Asegurarnos de tener la tabla vacía de duplicados viejos
        Schema::disableForeignKeyConstraints();
        Categoria::truncate();
        Schema::enableForeignKeyConstraints();

        foreach ($categorias as $cat) {
            Categoria::create([
                'nombre' => $cat['nombre'],
                'descripcion' => $cat['descripcion'],
                'activo' => true,
            ]);
        }
        $this->command->info('✅ ' . count($categorias) . ' categorías GLOBALES creadas');

        // ==================== MODELOS ====================
        // Cada modelo: marca + categoría + nombre
        $modelosData = [
            // Dell - Laptops
            ['marca' => 'Dell', 'categoria' => 'Laptop', 'nombre' => 'Latitude 5540', 'descripcion' => 'Laptop empresarial 15.6" Core i7'],
            ['marca' => 'Dell', 'categoria' => 'Laptop', 'nombre' => 'Latitude 5520', 'descripcion' => 'Laptop empresarial 15.6" Core i5'],
            ['marca' => 'Dell', 'categoria' => 'Laptop', 'nombre' => 'Inspiron 15 3525', 'descripcion' => 'Laptop hogar/oficina 15.6" Ryzen 5'],
            ['marca' => 'Dell', 'categoria' => 'Laptop', 'nombre' => 'XPS 15', 'descripcion' => 'Laptop premium 15.6" Core i9'],

            // Dell - Desktops
            ['marca' => 'Dell', 'categoria' => 'Computadora de Escritorio', 'nombre' => 'OptiPlex 3000', 'descripcion' => 'Desktop empresarial Core i5'],
            ['marca' => 'Dell', 'categoria' => 'Computadora de Escritorio', 'nombre' => 'OptiPlex 7000', 'descripcion' => 'Desktop alto rendimiento Core i7'],

            // Dell - Monitores
            ['marca' => 'Dell', 'categoria' => 'Monitor', 'nombre' => 'P2422H', 'descripcion' => 'Monitor IPS 24" Full HD'],
            ['marca' => 'Dell', 'categoria' => 'Monitor', 'nombre' => 'S2721QS', 'descripcion' => 'Monitor 27" 4K UHD'],

            // HP - Laptops
            ['marca' => 'HP', 'categoria' => 'Laptop', 'nombre' => 'EliteBook 840 G9', 'descripcion' => 'Laptop empresarial 14" Core i7'],
            ['marca' => 'HP', 'categoria' => 'Laptop', 'nombre' => 'ProBook 450 G10', 'descripcion' => 'Laptop profesional 15.6" Core i5'],
            ['marca' => 'HP', 'categoria' => 'Laptop', 'nombre' => 'Pavilion 15', 'descripcion' => 'Laptop hogar 15.6" Ryzen 7'],

            // HP - Desktops
            ['marca' => 'HP', 'categoria' => 'Computadora de Escritorio', 'nombre' => 'EliteDesk 800 G9', 'descripcion' => 'Desktop empresarial Core i7'],
            ['marca' => 'HP', 'categoria' => 'Computadora de Escritorio', 'nombre' => 'ProDesk 400 G9', 'descripcion' => 'Desktop oficina Core i5'],

            // HP - Monitores
            ['marca' => 'HP', 'categoria' => 'Monitor', 'nombre' => 'M24f', 'descripcion' => 'Monitor 24" Full HD IPS'],

            // HP - Impresoras
            ['marca' => 'HP', 'categoria' => 'Impresora', 'nombre' => 'LaserJet Pro M404dn', 'descripcion' => 'Impresora láser monocromática'],
            ['marca' => 'HP', 'categoria' => 'Impresora', 'nombre' => 'DeskJet 4175e', 'descripcion' => 'Impresora multifuncional tinta'],

            // Lenovo - Laptops
            ['marca' => 'Lenovo', 'categoria' => 'Laptop', 'nombre' => 'ThinkPad X1 Carbon Gen 11', 'descripcion' => 'Laptop ultraligera 14" Core i7'],
            ['marca' => 'Lenovo', 'categoria' => 'Laptop', 'nombre' => 'ThinkPad E14 Gen 5', 'descripcion' => 'Laptop empresarial 14" Core i5'],
            ['marca' => 'Lenovo', 'categoria' => 'Laptop', 'nombre' => 'IdeaPad 3', 'descripcion' => 'Laptop económica 15.6" Ryzen 3'],

            // Lenovo - Desktops
            ['marca' => 'Lenovo', 'categoria' => 'Computadora de Escritorio', 'nombre' => 'ThinkCentre M720q', 'descripcion' => 'Mini PC empresarial Core i5'],
            ['marca' => 'Lenovo', 'categoria' => 'Computadora de Escritorio', 'nombre' => 'ThinkCentre M90q', 'descripcion' => 'Mini PC alto rendimiento Core i7'],

            // Lenovo - Monitores
            ['marca' => 'Lenovo', 'categoria' => 'Monitor', 'nombre' => 'ThinkVision T24i-20', 'descripcion' => 'Monitor 24" Full HD'],

            // Samsung - Monitores
            ['marca' => 'Samsung', 'categoria' => 'Monitor', 'nombre' => 'S24R350', 'descripcion' => 'Monitor 24" Full HD IPS'],

            // ViewSonic - Monitores
            ['marca' => 'ViewSonic', 'categoria' => 'Monitor', 'nombre' => 'VA2432-H', 'descripcion' => 'Monitor 24" Full HD IPS'],

            // Epson - Impresoras
            ['marca' => 'Epson', 'categoria' => 'Impresora', 'nombre' => 'EcoTank L3250', 'descripcion' => 'Impresora tanque de tinta'],
            ['marca' => 'Epson', 'categoria' => 'Impresora', 'nombre' => 'EcoTank L5290', 'descripcion' => 'Impresora multifuncional tanque'],

            // Canon - Impresoras
            ['marca' => 'Canon', 'categoria' => 'Impresora', 'nombre' => 'PIXMA G3110', 'descripcion' => 'Impresora tanque de tinta'],

            // Brother - Impresoras
            ['marca' => 'Brother', 'categoria' => 'Impresora', 'nombre' => 'DCP-T520W', 'descripcion' => 'Impresora multifuncional tanque'],

            // Acer - Laptops
            ['marca' => 'Acer', 'categoria' => 'Laptop', 'nombre' => 'Aspire 5', 'descripcion' => 'Laptop versátil 15.6" Core i5'],

            // Asus - Laptops
            ['marca' => 'Asus', 'categoria' => 'Laptop', 'nombre' => 'VivoBook 15', 'descripcion' => 'Laptop delgada 15.6" Core i3'],

            // Cisco - Router/Switch
            ['marca' => 'Cisco', 'categoria' => 'Router / Switch', 'nombre' => 'Catalyst 2960', 'descripcion' => 'Switch 24 puertos Gigabit'],

            // TP-Link - Router/Switch
            ['marca' => 'TP-Link', 'categoria' => 'Router / Switch', 'nombre' => 'Archer AX73', 'descripcion' => 'Router WiFi 6 dual band'],
            ['marca' => 'TP-Link', 'categoria' => 'Router / Switch', 'nombre' => 'TL-SG1024D', 'descripcion' => 'Switch 24 puertos Gigabit'],

            // Kingston - Discos
            ['marca' => 'Kingston', 'categoria' => 'Disco Duro / SSD', 'nombre' => 'A400 SSD 480GB', 'descripcion' => 'SSD SATA 2.5"'],
            ['marca' => 'Kingston', 'categoria' => 'Disco Duro / SSD', 'nombre' => 'NV2 NVMe 1TB', 'descripcion' => 'SSD NVMe M.2'],

            // Crucial - Discos
            ['marca' => 'Crucial', 'categoria' => 'Disco Duro / SSD', 'nombre' => 'MX500 SSD 1TB', 'descripcion' => 'SSD SATA 2.5"'],

            // Western Digital - Discos
            ['marca' => 'Western Digital', 'categoria' => 'Disco Duro / SSD', 'nombre' => 'Blue HDD 1TB', 'descripcion' => 'Disco duro SATA 3.5"'],

            // Seagate - Discos
            ['marca' => 'Seagate', 'categoria' => 'Disco Duro / SSD', 'nombre' => 'Barracuda HDD 2TB', 'descripcion' => 'Disco duro SATA 3.5"'],

            // Kingston - RAM
            ['marca' => 'Kingston', 'categoria' => 'Memoria RAM', 'nombre' => 'DDR4 8GB 3200MHz', 'descripcion' => 'Módulo RAM DDR4'],
            ['marca' => 'Kingston', 'categoria' => 'Memoria RAM', 'nombre' => 'DDR4 16GB 3200MHz', 'descripcion' => 'Módulo RAM DDR4'],

            // Crucial - RAM
            ['marca' => 'Crucial', 'categoria' => 'Memoria RAM', 'nombre' => 'DDR4 8GB 2666MHz', 'descripcion' => 'Módulo RAM DDR4'],
            ['marca' => 'Crucial', 'categoria' => 'Memoria RAM', 'nombre' => 'DDR4 16GB 2666MHz', 'descripcion' => 'Módulo RAM DDR4'],

            // Logitech - Teclados
            ['marca' => 'Logitech', 'categoria' => 'Teclado', 'nombre' => 'K120', 'descripcion' => 'Teclado USB estándar'],
            ['marca' => 'Logitech', 'categoria' => 'Teclado', 'nombre' => 'K400 Plus', 'descripcion' => 'Teclado inalámbrico con touchpad'],

            // Microsoft - Teclados
            ['marca' => 'Microsoft', 'categoria' => 'Teclado', 'nombre' => 'Wired Keyboard 600', 'descripcion' => 'Teclado USB estándar'],

            // Logitech - Mouse
            ['marca' => 'Logitech', 'categoria' => 'Mouse', 'nombre' => 'M90', 'descripcion' => 'Mouse USB óptico'],
            ['marca' => 'Logitech', 'categoria' => 'Mouse', 'nombre' => 'M170', 'descripcion' => 'Mouse inalámbrico'],

            // Microsoft - Mouse
            ['marca' => 'Microsoft', 'categoria' => 'Mouse', 'nombre' => 'Basic Optical Mouse', 'descripcion' => 'Mouse USB óptico'],

            // Logitech - Cámaras Web
            ['marca' => 'Logitech', 'categoria' => 'Cámara Web', 'nombre' => 'C920 HD Pro', 'descripcion' => 'Webcam Full HD 1080p'],
            ['marca' => 'Logitech', 'categoria' => 'Cámara Web', 'nombre' => 'C270', 'descripcion' => 'Webcam HD 720p'],

            // Microsoft - Cámaras Web
            ['marca' => 'Microsoft', 'categoria' => 'Cámara Web', 'nombre' => 'LifeCam HD-3000', 'descripcion' => 'Webcam HD 720p'],
        ];

        $modelosCreados = [];
        foreach ($modelosData as $mod) {
            $marca = Marca::where('nombre', $mod['marca'])->first();
            $categoria = Categoria::where('nombre', $mod['categoria'])->first();

            if (!$marca || !$categoria) {
                $this->command->warn("⚠️ Saltando modelo {$mod['nombre']}: marca o categoría no encontrada");
                continue;
            }

            $modelo = Modelo::updateOrCreate(
                [
                    'marca_id' => $marca->id,
                    'categoria_id' => $categoria->id,
                    'nombre' => $mod['nombre'],
                ],
                [
                    'descripcion' => $mod['descripcion'],
                    'activo' => true,
                ]
            );
            $modelosCreados[] = $modelo;
        }
        $this->command->info('✅ ' . count($modelosCreados) . ' modelos creados');

        // ==================== RESUMEN ====================
        $this->command->newLine();
        $this->command->info('🎉 DATOS DE DEMOSTRACIÓN CREADOS EXITOSAMENTE');
        $this->command->table(
            ['Entidad', 'Cantidad'],
            [
                ['Marcas', count($marcas)],
                ['Categorías (globales)', count($categorias)],
                ['Modelos', count($modelosCreados)],
            ]
        );
    }
}