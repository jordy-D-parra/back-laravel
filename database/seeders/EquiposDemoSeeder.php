<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\ModeloComponente;
use Illuminate\Support\Facades\DB;

class EquiposDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creando datos de demostración para catálogo de equipos...');

        // ==================== CATEGORÍAS ====================
        $categorias = [
            ['nombre' => 'Laptop', 'descripcion' => 'Computadoras portátiles'],
            ['nombre' => 'Computadora de Escritorio', 'descripcion' => 'CPU, torres y estaciones de trabajo'],
            ['nombre' => 'Monitor', 'descripcion' => 'Pantallas y monitores'],
            ['nombre' => 'Impresora', 'descripcion' => 'Impresoras láser y de tinta'],
            ['nombre' => 'Escáner', 'descripcion' => 'Escáneres de documentos'],
            ['nombre' => 'Router / Switch', 'descripcion' => 'Equipos de red'],
            ['nombre' => 'UPS / Regulador', 'descripcion' => 'Sistemas de energía ininterrumpida'],
            ['nombre' => 'Proyector', 'descripcion' => 'Proyectores multimedia'],
            ['nombre' => 'Teléfono IP', 'descripcion' => 'Teléfonos VoIP'],
            ['nombre' => 'Tablet', 'descripcion' => 'Tabletas electrónicas'],
            ['nombre' => 'Disco Duro / SSD', 'descripcion' => 'Unidades de almacenamiento'],
            ['nombre' => 'Memoria RAM', 'descripcion' => 'Módulos de memoria'],
            ['nombre' => 'Teclado', 'descripcion' => 'Teclados y periféricos de entrada'],
            ['nombre' => 'Mouse', 'descripcion' => 'Ratones y dispositivos señaladores'],
            ['nombre' => 'Cargador', 'descripcion' => 'Cargadores y fuentes de poder'],
            ['nombre' => 'Cable / Adaptador', 'descripcion' => 'Cables y adaptadores varios'],
            ['nombre' => 'Cámara Web', 'descripcion' => 'Cámaras para videoconferencia'],
            ['nombre' => 'Hub USB / Dock Station', 'descripcion' => 'Concentradores y docks'],
            ['nombre' => 'Parlantes / Cornetas', 'descripcion' => 'Altavoces y sistemas de audio'],
            ['nombre' => 'Servidor', 'descripcion' => 'Servidores y equipos de rack'],
        ];

        foreach ($categorias as $cat) {
            Categoria::updateOrCreate(['nombre' => $cat['nombre']], $cat);
        }
        $this->command->info('✅ ' . count($categorias) . ' categorías creadas');

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

        // ==================== MODELOS ====================
        $modelosData = [
            // LAPTOPS
            ['marca' => 'Dell', 'categoria' => 'Laptop', 'nombre' => 'Latitude 5540', 'descripcion' => 'Laptop empresarial 15.6" Core i7'],
            ['marca' => 'Dell', 'categoria' => 'Laptop', 'nombre' => 'Latitude 5520', 'descripcion' => 'Laptop empresarial 15.6" Core i5'],
            ['marca' => 'Dell', 'categoria' => 'Laptop', 'nombre' => 'Inspiron 15 3525', 'descripcion' => 'Laptop hogar/oficina 15.6" Ryzen 5'],
            ['marca' => 'Dell', 'categoria' => 'Laptop', 'nombre' => 'XPS 15', 'descripcion' => 'Laptop premium 15.6" Core i9'],
            ['marca' => 'HP', 'categoria' => 'Laptop', 'nombre' => 'EliteBook 840 G9', 'descripcion' => 'Laptop empresarial 14" Core i7'],
            ['marca' => 'HP', 'categoria' => 'Laptop', 'nombre' => 'ProBook 450 G10', 'descripcion' => 'Laptop profesional 15.6" Core i5'],
            ['marca' => 'HP', 'categoria' => 'Laptop', 'nombre' => 'Pavilion 15', 'descripcion' => 'Laptop hogar 15.6" Ryzen 7'],
            ['marca' => 'Lenovo', 'categoria' => 'Laptop', 'nombre' => 'ThinkPad X1 Carbon Gen 11', 'descripcion' => 'Laptop ultraligera 14" Core i7'],
            ['marca' => 'Lenovo', 'categoria' => 'Laptop', 'nombre' => 'ThinkPad E14 Gen 5', 'descripcion' => 'Laptop empresarial 14" Core i5'],
            ['marca' => 'Lenovo', 'categoria' => 'Laptop', 'nombre' => 'IdeaPad 3', 'descripcion' => 'Laptop económica 15.6" Ryzen 3'],
            ['marca' => 'Acer', 'categoria' => 'Laptop', 'nombre' => 'Aspire 5', 'descripcion' => 'Laptop versátil 15.6" Core i5'],
            ['marca' => 'Asus', 'categoria' => 'Laptop', 'nombre' => 'VivoBook 15', 'descripcion' => 'Laptop delgada 15.6" Core i3'],

            // COMPUTADORAS DE ESCRITORIO
            ['marca' => 'Dell', 'categoria' => 'Computadora de Escritorio', 'nombre' => 'OptiPlex 3000', 'descripcion' => 'Desktop empresarial Core i5'],
            ['marca' => 'Dell', 'categoria' => 'Computadora de Escritorio', 'nombre' => 'OptiPlex 7000', 'descripcion' => 'Desktop alto rendimiento Core i7'],
            ['marca' => 'HP', 'categoria' => 'Computadora de Escritorio', 'nombre' => 'EliteDesk 800 G9', 'descripcion' => 'Desktop empresarial Core i7'],
            ['marca' => 'HP', 'categoria' => 'Computadora de Escritorio', 'nombre' => 'ProDesk 400 G9', 'descripcion' => 'Desktop oficina Core i5'],
            ['marca' => 'Lenovo', 'categoria' => 'Computadora de Escritorio', 'nombre' => 'ThinkCentre M720q', 'descripcion' => 'Mini PC empresarial Core i5'],
            ['marca' => 'Lenovo', 'categoria' => 'Computadora de Escritorio', 'nombre' => 'ThinkCentre M90q', 'descripcion' => 'Mini PC alto rendimiento Core i7'],

            // MONITORES
            ['marca' => 'Dell', 'categoria' => 'Monitor', 'nombre' => 'P2422H', 'descripcion' => 'Monitor IPS 24" Full HD'],
            ['marca' => 'Dell', 'categoria' => 'Monitor', 'nombre' => 'S2721QS', 'descripcion' => 'Monitor 27" 4K UHD'],
            ['marca' => 'HP', 'categoria' => 'Monitor', 'nombre' => 'M24f', 'descripcion' => 'Monitor 24" Full HD IPS'],
            ['marca' => 'Lenovo', 'categoria' => 'Monitor', 'nombre' => 'ThinkVision T24i-20', 'descripcion' => 'Monitor 24" Full HD'],
            ['marca' => 'Samsung', 'categoria' => 'Monitor', 'nombre' => 'S24R350', 'descripcion' => 'Monitor 24" Full HD IPS'],
            ['marca' => 'ViewSonic', 'categoria' => 'Monitor', 'nombre' => 'VA2432-H', 'descripcion' => 'Monitor 24" Full HD IPS'],

            // IMPRESORAS
            ['marca' => 'HP', 'categoria' => 'Impresora', 'nombre' => 'LaserJet Pro M404dn', 'descripcion' => 'Impresora láser monocromática'],
            ['marca' => 'HP', 'categoria' => 'Impresora', 'nombre' => 'DeskJet 4175e', 'descripcion' => 'Impresora multifuncional tinta'],
            ['marca' => 'Epson', 'categoria' => 'Impresora', 'nombre' => 'EcoTank L3250', 'descripcion' => 'Impresora tanque de tinta'],
            ['marca' => 'Epson', 'categoria' => 'Impresora', 'nombre' => 'EcoTank L5290', 'descripcion' => 'Impresora multifuncional tanque'],
            ['marca' => 'Canon', 'categoria' => 'Impresora', 'nombre' => 'PIXMA G3110', 'descripcion' => 'Impresora tanque de tinta'],
            ['marca' => 'Brother', 'categoria' => 'Impresora', 'nombre' => 'DCP-T520W', 'descripcion' => 'Impresora multifuncional tanque'],

            // ROUTER/SWITCH
            ['marca' => 'Cisco', 'categoria' => 'Router / Switch', 'nombre' => 'Catalyst 2960', 'descripcion' => 'Switch 24 puertos Gigabit'],
            ['marca' => 'TP-Link', 'categoria' => 'Router / Switch', 'nombre' => 'Archer AX73', 'descripcion' => 'Router WiFi 6 dual band'],
            ['marca' => 'TP-Link', 'categoria' => 'Router / Switch', 'nombre' => 'TL-SG1024D', 'descripcion' => 'Switch 24 puertos Gigabit'],

            // DISCOS DUROS
            ['marca' => 'Kingston', 'categoria' => 'Disco Duro / SSD', 'nombre' => 'A400 SSD 480GB', 'descripcion' => 'SSD SATA 2.5"'],
            ['marca' => 'Kingston', 'categoria' => 'Disco Duro / SSD', 'nombre' => 'NV2 NVMe 1TB', 'descripcion' => 'SSD NVMe M.2'],
            ['marca' => 'Crucial', 'categoria' => 'Disco Duro / SSD', 'nombre' => 'MX500 SSD 1TB', 'descripcion' => 'SSD SATA 2.5"'],
            ['marca' => 'Western Digital', 'categoria' => 'Disco Duro / SSD', 'nombre' => 'Blue HDD 1TB', 'descripcion' => 'Disco duro SATA 3.5"'],
            ['marca' => 'Seagate', 'categoria' => 'Disco Duro / SSD', 'nombre' => 'Barracuda HDD 2TB', 'descripcion' => 'Disco duro SATA 3.5"'],

            // MEMORIA RAM
            ['marca' => 'Kingston', 'categoria' => 'Memoria RAM', 'nombre' => 'DDR4 8GB 3200MHz', 'descripcion' => 'Módulo RAM DDR4'],
            ['marca' => 'Kingston', 'categoria' => 'Memoria RAM', 'nombre' => 'DDR4 16GB 3200MHz', 'descripcion' => 'Módulo RAM DDR4'],
            ['marca' => 'Crucial', 'categoria' => 'Memoria RAM', 'nombre' => 'DDR4 8GB 2666MHz', 'descripcion' => 'Módulo RAM DDR4'],
            ['marca' => 'Crucial', 'categoria' => 'Memoria RAM', 'nombre' => 'DDR4 16GB 2666MHz', 'descripcion' => 'Módulo RAM DDR4'],

            // TECLADOS
            ['marca' => 'Logitech', 'categoria' => 'Teclado', 'nombre' => 'K120', 'descripcion' => 'Teclado USB estándar'],
            ['marca' => 'Logitech', 'categoria' => 'Teclado', 'nombre' => 'K400 Plus', 'descripcion' => 'Teclado inalámbrico con touchpad'],
            ['marca' => 'Microsoft', 'categoria' => 'Teclado', 'nombre' => 'Wired Keyboard 600', 'descripcion' => 'Teclado USB estándar'],

            // MOUSE
            ['marca' => 'Logitech', 'categoria' => 'Mouse', 'nombre' => 'M90', 'descripcion' => 'Mouse USB óptico'],
            ['marca' => 'Logitech', 'categoria' => 'Mouse', 'nombre' => 'M170', 'descripcion' => 'Mouse inalámbrico'],
            ['marca' => 'Microsoft', 'categoria' => 'Mouse', 'nombre' => 'Basic Optical Mouse', 'descripcion' => 'Mouse USB óptico'],

            // CÁMARAS WEB
            ['marca' => 'Logitech', 'categoria' => 'Cámara Web', 'nombre' => 'C920 HD Pro', 'descripcion' => 'Webcam Full HD 1080p'],
            ['marca' => 'Logitech', 'categoria' => 'Cámara Web', 'nombre' => 'C270', 'descripcion' => 'Webcam HD 720p'],
            ['marca' => 'Microsoft', 'categoria' => 'Cámara Web', 'nombre' => 'LifeCam HD-3000', 'descripcion' => 'Webcam HD 720p'],
        ];

        $modelosCreados = [];
        foreach ($modelosData as $mod) {
            $marca = Marca::where('nombre', $mod['marca'])->first();
            $categoria = Categoria::where('nombre', $mod['categoria'])->first();

            if ($marca && $categoria) {
                $modelo = Modelo::updateOrCreate(
                    ['marca_id' => $marca->id, 'nombre' => $mod['nombre']],
                    [
                        'categoria_id' => $categoria->id,
                        'descripcion' => $mod['descripcion'],
                        'activo' => true
                    ]
                );
                $modelosCreados[] = $modelo;
            }
        }
        $this->command->info('✅ ' . count($modelosCreados) . ' modelos creados');

        // ==================== COMPONENTES DE MODELOS ====================
        $componentesPorModelo = [
            // Laptops Dell
            'Latitude 5540' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM DDR4', 'capacidad' => '16GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD NVMe M.2', 'capacidad' => '512GB'],
                ['tipo' => 'Batería', 'descripcion' => 'Batería de litio 6 celdas', 'capacidad' => '68Wh'],
                ['tipo' => 'Cargador', 'descripcion' => 'Cargador USB-C', 'capacidad' => '65W'],
                ['tipo' => 'Pantalla', 'descripcion' => 'Pantalla LED IPS', 'capacidad' => '15.6" FHD'],
                ['tipo' => 'Procesador', 'descripcion' => 'Intel Core i7-1365U', 'capacidad' => '5.2GHz'],
            ],
            'Latitude 5520' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM DDR4', 'capacidad' => '8GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD NVMe M.2', 'capacidad' => '256GB'],
                ['tipo' => 'Batería', 'descripcion' => 'Batería de litio 4 celdas', 'capacidad' => '54Wh'],
                ['tipo' => 'Cargador', 'descripcion' => 'Cargador USB-C', 'capacidad' => '65W'],
                ['tipo' => 'Pantalla', 'descripcion' => 'Pantalla LED IPS', 'capacidad' => '15.6" FHD'],
                ['tipo' => 'Procesador', 'descripcion' => 'Intel Core i5-1135G7', 'capacidad' => '4.2GHz'],
            ],
            'Inspiron 15 3525' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM DDR4', 'capacidad' => '8GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD SATA', 'capacidad' => '512GB'],
                ['tipo' => 'Batería', 'descripcion' => 'Batería de litio 3 celdas', 'capacidad' => '41Wh'],
                ['tipo' => 'Cargador', 'descripcion' => 'Cargador DC', 'capacidad' => '45W'],
                ['tipo' => 'Pantalla', 'descripcion' => 'Pantalla LED', 'capacidad' => '15.6" HD'],
                ['tipo' => 'Procesador', 'descripcion' => 'AMD Ryzen 5', 'capacidad' => '4.0GHz'],
            ],
            'XPS 15' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM DDR5', 'capacidad' => '32GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD NVMe M.2', 'capacidad' => '1TB'],
                ['tipo' => 'Batería', 'descripcion' => 'Batería de litio 6 celdas', 'capacidad' => '86Wh'],
                ['tipo' => 'Cargador', 'descripcion' => 'Cargador USB-C', 'capacidad' => '130W'],
                ['tipo' => 'Pantalla', 'descripcion' => 'Pantalla OLED táctil', 'capacidad' => '15.6" 4K'],
                ['tipo' => 'Procesador', 'descripcion' => 'Intel Core i9-13900H', 'capacidad' => '5.4GHz'],
            ],

            // Laptops HP
            'EliteBook 840 G9' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM DDR5', 'capacidad' => '16GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD NVMe M.2', 'capacidad' => '512GB'],
                ['tipo' => 'Batería', 'descripcion' => 'Batería de litio 3 celdas', 'capacidad' => '51Wh'],
                ['tipo' => 'Cargador', 'descripcion' => 'Cargador USB-C', 'capacidad' => '65W'],
                ['tipo' => 'Pantalla', 'descripcion' => 'Pantalla LED IPS', 'capacidad' => '14" FHD'],
                ['tipo' => 'Procesador', 'descripcion' => 'Intel Core i7-1355U', 'capacidad' => '5.0GHz'],
            ],
            'ProBook 450 G10' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM DDR4', 'capacidad' => '16GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD NVMe M.2', 'capacidad' => '512GB'],
                ['tipo' => 'Batería', 'descripcion' => 'Batería de litio 3 celdas', 'capacidad' => '41Wh'],
                ['tipo' => 'Cargador', 'descripcion' => 'Cargador DC', 'capacidad' => '45W'],
                ['tipo' => 'Pantalla', 'descripcion' => 'Pantalla LED IPS', 'capacidad' => '15.6" FHD'],
                ['tipo' => 'Procesador', 'descripcion' => 'Intel Core i5-1235U', 'capacidad' => '4.4GHz'],
            ],
            'Pavilion 15' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM DDR4', 'capacidad' => '8GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD NVMe M.2', 'capacidad' => '256GB'],
                ['tipo' => 'Batería', 'descripcion' => 'Batería de litio 3 celdas', 'capacidad' => '41Wh'],
                ['tipo' => 'Cargador', 'descripcion' => 'Cargador DC', 'capacidad' => '45W'],
                ['tipo' => 'Pantalla', 'descripcion' => 'Pantalla LED IPS', 'capacidad' => '15.6" FHD'],
                ['tipo' => 'Procesador', 'descripcion' => 'AMD Ryzen 7', 'capacidad' => '4.5GHz'],
            ],

            // Laptops Lenovo
            'ThinkPad X1 Carbon Gen 11' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM LPDDR5', 'capacidad' => '16GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD NVMe M.2', 'capacidad' => '1TB'],
                ['tipo' => 'Batería', 'descripcion' => 'Batería de litio', 'capacidad' => '57Wh'],
                ['tipo' => 'Cargador', 'descripcion' => 'Cargador USB-C', 'capacidad' => '65W'],
                ['tipo' => 'Pantalla', 'descripcion' => 'Pantalla IPS antirreflejo', 'capacidad' => '14" 2.8K'],
                ['tipo' => 'Procesador', 'descripcion' => 'Intel Core i7-1365U', 'capacidad' => '5.2GHz'],
            ],
            'ThinkPad E14 Gen 5' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM DDR4', 'capacidad' => '8GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD NVMe M.2', 'capacidad' => '256GB'],
                ['tipo' => 'Batería', 'descripcion' => 'Batería de litio', 'capacidad' => '45Wh'],
                ['tipo' => 'Cargador', 'descripcion' => 'Cargador USB-C', 'capacidad' => '65W'],
                ['tipo' => 'Pantalla', 'descripcion' => 'Pantalla IPS', 'capacidad' => '14" FHD'],
                ['tipo' => 'Procesador', 'descripcion' => 'Intel Core i5-1235U', 'capacidad' => '4.4GHz'],
            ],

            // Computadoras de escritorio
            'OptiPlex 3000' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM DDR4', 'capacidad' => '8GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD SATA', 'capacidad' => '256GB'],
                ['tipo' => 'Procesador', 'descripcion' => 'Intel Core i5-12500', 'capacidad' => '4.6GHz'],
                ['tipo' => 'Fuente', 'descripcion' => 'Fuente de poder', 'capacidad' => '260W'],
            ],
            'OptiPlex 7000' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM DDR5', 'capacidad' => '32GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD NVMe M.2', 'capacidad' => '1TB'],
                ['tipo' => 'Procesador', 'descripcion' => 'Intel Core i7-12700', 'capacidad' => '4.9GHz'],
                ['tipo' => 'Fuente', 'descripcion' => 'Fuente de poder', 'capacidad' => '400W'],
            ],
            'EliteDesk 800 G9' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM DDR5', 'capacidad' => '16GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD NVMe M.2', 'capacidad' => '512GB'],
                ['tipo' => 'Procesador', 'descripcion' => 'Intel Core i7-12700', 'capacidad' => '4.9GHz'],
                ['tipo' => 'Fuente', 'descripcion' => 'Fuente de poder', 'capacidad' => '350W'],
            ],

            // Monitores
            'P2422H' => [
                ['tipo' => 'Pantalla', 'descripcion' => 'Panel IPS LED', 'capacidad' => '23.8" FHD'],
                ['tipo' => 'Cable', 'descripcion' => 'Cable DisplayPort', 'capacidad' => '1.8m'],
                ['tipo' => 'Cable', 'descripcion' => 'Cable HDMI', 'capacidad' => '1.5m'],
            ],
            'S2721QS' => [
                ['tipo' => 'Pantalla', 'descripcion' => 'Panel IPS LED', 'capacidad' => '27" 4K UHD'],
                ['tipo' => 'Cable', 'descripcion' => 'Cable HDMI 2.0', 'capacidad' => '2m'],
            ],

            // Impresoras
            'LaserJet Pro M404dn' => [
                ['tipo' => 'Tóner', 'descripcion' => 'Cartucho de tóner negro', 'capacidad' => '3000 páginas'],
                ['tipo' => 'Cable', 'descripcion' => 'Cable USB-B', 'capacidad' => '2m'],
                ['tipo' => 'Cable', 'descripcion' => 'Cable de red Ethernet', 'capacidad' => '3m'],
            ],
            'EcoTank L3250' => [
                ['tipo' => 'Tinta', 'descripcion' => 'Tinta negra', 'capacidad' => '127ml'],
                ['tipo' => 'Tinta', 'descripcion' => 'Tinta cian', 'capacidad' => '70ml'],
                ['tipo' => 'Tinta', 'descripcion' => 'Tinta magenta', 'capacidad' => '70ml'],
                ['tipo' => 'Tinta', 'descripcion' => 'Tinta amarilla', 'capacidad' => '70ml'],
            ],

            // Discos
            'A400 SSD 480GB' => [
                ['tipo' => 'Disco', 'descripcion' => 'SSD SATA III', 'capacidad' => '480GB'],
            ],
            'NV2 NVMe 1TB' => [
                ['tipo' => 'Disco', 'descripcion' => 'SSD NVMe PCIe 4.0', 'capacidad' => '1TB'],
            ],
            'Blue HDD 1TB' => [
                ['tipo' => 'Disco', 'descripcion' => 'HDD SATA III 7200RPM', 'capacidad' => '1TB'],
            ],

            // RAM
            'DDR4 8GB 3200MHz' => [
                ['tipo' => 'RAM', 'descripcion' => 'Módulo DDR4 UDIMM', 'capacidad' => '8GB 3200MHz'],
            ],
            'DDR4 16GB 3200MHz' => [
                ['tipo' => 'RAM', 'descripcion' => 'Módulo DDR4 UDIMM', 'capacidad' => '16GB 3200MHz'],
            ],

            // Cámaras
            'C920 HD Pro' => [
                ['tipo' => 'Cable', 'descripcion' => 'Cable USB integrado', 'capacidad' => '1.5m'],
            ],
        ];

        $totalComponentes = 0;
        foreach ($componentesPorModelo as $modeloNombre => $componentes) {
            $modelo = Modelo::where('nombre', $modeloNombre)->first();
            if ($modelo) {
                foreach ($componentes as $comp) {
                    ModeloComponente::updateOrCreate(
                        [
                            'modelo_id' => $modelo->id,
                            'tipo' => $comp['tipo'],
                            'descripcion' => $comp['descripcion'],
                        ],
                        [
                            'capacidad' => $comp['capacidad'],
                            'requerido' => true,
                        ]
                    );
                    $totalComponentes++;
                }
            }
        }
        $this->command->info('✅ ' . $totalComponentes . ' componentes de modelo creados');

        // ==================== RESUMEN ====================
        $this->command->newLine();
        $this->command->info('🎉 DATOS DE DEMOSTRACIÓN CREADOS EXITOSAMENTE');
        $this->command->table(
            ['Entidad', 'Cantidad'],
            [
                ['Categorías', count($categorias)],
                ['Marcas', count($marcas)],
                ['Modelos', count($modelosCreados)],
                ['Componentes de modelo', $totalComponentes],
            ]
        );
    }
}
