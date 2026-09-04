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

        // ==================== MARCAS (PRIMERO) ====================
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

        // ==================== CATEGORÍAS CON MARCA_ID ====================
        // Primero, LIMPIAR categorías existentes sin marca_id o con datos viejos
        // OPCIONAL: Si quieres mantener las existentes, solo actualiza las que ya tienen marca
        // Aquí vamos a ELIMINAR las categorías existentes para evitar duplicados
        // Si NO quieres eliminar, comenta las siguientes 2 líneas
        
        // Opción 1: Eliminar todas las categorías existentes (recomendado para este seeder)
        Categoria::truncate();
        $this->command->warn('⚠️ Se eliminaron las categorías existentes para evitar duplicados');
        
        // Opción 2: Si NO quieres eliminar, usa esto en su lugar:
        // $this->command->warn('⚠️ Manteniendo categorías existentes. Solo se actualizarán las que coincidan.');
        
        $categoriasPorMarca = [
            'Dell' => [
                ['nombre' => 'Laptop', 'descripcion' => 'Computadoras portátiles'],
                ['nombre' => 'Computadora de Escritorio', 'descripcion' => 'CPU, torres y estaciones de trabajo'],
                ['nombre' => 'Monitor', 'descripcion' => 'Pantallas y monitores'],
                ['nombre' => 'Servidor', 'descripcion' => 'Servidores y equipos de rack'],
            ],
            'HP' => [
                ['nombre' => 'Laptop', 'descripcion' => 'Computadoras portátiles'],
                ['nombre' => 'Computadora de Escritorio', 'descripcion' => 'CPU, torres y estaciones de trabajo'],
                ['nombre' => 'Monitor', 'descripcion' => 'Pantallas y monitores'],
                ['nombre' => 'Impresora', 'descripcion' => 'Impresoras láser y de tinta'],
            ],
            'Lenovo' => [
                ['nombre' => 'Laptop', 'descripcion' => 'Computadoras portátiles'],
                ['nombre' => 'Computadora de Escritorio', 'descripcion' => 'CPU, torres y estaciones de trabajo'],
                ['nombre' => 'Monitor', 'descripcion' => 'Pantallas y monitores'],
            ],
            'Apple' => [
                ['nombre' => 'Laptop', 'descripcion' => 'Computadoras portátiles'],
                ['nombre' => 'Computadora de Escritorio', 'descripcion' => 'CPU, torres y estaciones de trabajo'],
                ['nombre' => 'Tablet', 'descripcion' => 'Tabletas electrónicas'],
            ],
            'Samsung' => [
                ['nombre' => 'Monitor', 'descripcion' => 'Pantallas y monitores'],
                ['nombre' => 'Disco Duro / SSD', 'descripcion' => 'Unidades de almacenamiento'],
                ['nombre' => 'Tablet', 'descripcion' => 'Tabletas electrónicas'],
            ],
            'Kingston' => [
                ['nombre' => 'Disco Duro / SSD', 'descripcion' => 'Unidades de almacenamiento'],
                ['nombre' => 'Memoria RAM', 'descripcion' => 'Módulos de memoria'],
            ],
            'Crucial' => [
                ['nombre' => 'Disco Duro / SSD', 'descripcion' => 'Unidades de almacenamiento'],
                ['nombre' => 'Memoria RAM', 'descripcion' => 'Módulos de memoria'],
            ],
            'Logitech' => [
                ['nombre' => 'Teclado', 'descripcion' => 'Teclados y periféricos de entrada'],
                ['nombre' => 'Mouse', 'descripcion' => 'Ratones y dispositivos señaladores'],
                ['nombre' => 'Cámara Web', 'descripcion' => 'Cámaras para videoconferencia'],
                ['nombre' => 'Parlantes / Cornetas', 'descripcion' => 'Altavoces y sistemas de audio'],
            ],
            'Microsoft' => [
                ['nombre' => 'Teclado', 'descripcion' => 'Teclados y periféricos de entrada'],
                ['nombre' => 'Mouse', 'descripcion' => 'Ratones y dispositivos señaladores'],
                ['nombre' => 'Cámara Web', 'descripcion' => 'Cámaras para videoconferencia'],
            ],
            'Epson' => [
                ['nombre' => 'Impresora', 'descripcion' => 'Impresoras láser y de tinta'],
                ['nombre' => 'Escáner', 'descripcion' => 'Escáneres de documentos'],
                ['nombre' => 'Proyector', 'descripcion' => 'Proyectores multimedia'],
            ],
            'Canon' => [
                ['nombre' => 'Impresora', 'descripcion' => 'Impresoras láser y de tinta'],
                ['nombre' => 'Escáner', 'descripcion' => 'Escáneres de documentos'],
                ['nombre' => 'Cámara Web', 'descripcion' => 'Cámaras para videoconferencia'],
            ],
            'Brother' => [
                ['nombre' => 'Impresora', 'descripcion' => 'Impresoras láser y de tinta'],
                ['nombre' => 'Escáner', 'descripcion' => 'Escáneres de documentos'],
            ],
            'Acer' => [
                ['nombre' => 'Laptop', 'descripcion' => 'Computadoras portátiles'],
                ['nombre' => 'Monitor', 'descripcion' => 'Pantallas y monitores'],
                ['nombre' => 'Proyector', 'descripcion' => 'Proyectores multimedia'],
            ],
            'Asus' => [
                ['nombre' => 'Laptop', 'descripcion' => 'Computadoras portátiles'],
                ['nombre' => 'Monitor', 'descripcion' => 'Pantallas y monitores'],
                ['nombre' => 'Router / Switch', 'descripcion' => 'Equipos de red'],
            ],
            'TP-Link' => [
                ['nombre' => 'Router / Switch', 'descripcion' => 'Equipos de red'],
                ['nombre' => 'Cable / Adaptador', 'descripcion' => 'Cables y adaptadores varios'],
            ],
            'Cisco' => [
                ['nombre' => 'Router / Switch', 'descripcion' => 'Equipos de red'],
                ['nombre' => 'Teléfono IP', 'descripcion' => 'Teléfonos VoIP'],
            ],
            'Western Digital' => [
                ['nombre' => 'Disco Duro / SSD', 'descripcion' => 'Unidades de almacenamiento'],
            ],
            'Seagate' => [
                ['nombre' => 'Disco Duro / SSD', 'descripcion' => 'Unidades de almacenamiento'],
            ],
            'APC' => [
                ['nombre' => 'UPS / Regulador', 'descripcion' => 'Sistemas de energía ininterrumpida'],
            ],
            'ViewSonic' => [
                ['nombre' => 'Monitor', 'descripcion' => 'Pantallas y monitores'],
                ['nombre' => 'Proyector', 'descripcion' => 'Proyectores multimedia'],
            ],
        ];

        $categoriasCreadas = [];
        foreach ($categoriasPorMarca as $nombreMarca => $categoriasLista) {
            $marca = Marca::where('nombre', $nombreMarca)->first();
            if ($marca) {
                foreach ($categoriasLista as $catData) {
                    // Usamos firstOrCreate para evitar duplicados
                    $categoria = Categoria::firstOrCreate(
                        [
                            'nombre' => $catData['nombre'],
                            'marca_id' => $marca->id,
                        ],
                        [
                            'descripcion' => $catData['descripcion'],
                            'activo' => true,
                        ]
                    );
                    $categoriasCreadas[] = $categoria;
                }
            }
        }
        $this->command->info('✅ ' . count($categoriasCreadas) . ' categorías creadas');

        // ==================== MODELOS ====================
        $modelosData = [
            // DELL - Laptops
            ['categoria' => 'Laptop', 'marca' => 'Dell', 'nombre' => 'Latitude 5540', 'descripcion' => 'Laptop empresarial 15.6" Core i7'],
            ['categoria' => 'Laptop', 'marca' => 'Dell', 'nombre' => 'Latitude 5520', 'descripcion' => 'Laptop empresarial 15.6" Core i5'],
            ['categoria' => 'Laptop', 'marca' => 'Dell', 'nombre' => 'Inspiron 15 3525', 'descripcion' => 'Laptop hogar/oficina 15.6" Ryzen 5'],
            ['categoria' => 'Laptop', 'marca' => 'Dell', 'nombre' => 'XPS 15', 'descripcion' => 'Laptop premium 15.6" Core i9'],
            
            // DELL - Computadoras de Escritorio
            ['categoria' => 'Computadora de Escritorio', 'marca' => 'Dell', 'nombre' => 'OptiPlex 3000', 'descripcion' => 'Desktop empresarial Core i5'],
            ['categoria' => 'Computadora de Escritorio', 'marca' => 'Dell', 'nombre' => 'OptiPlex 7000', 'descripcion' => 'Desktop alto rendimiento Core i7'],
            
            // DELL - Monitores
            ['categoria' => 'Monitor', 'marca' => 'Dell', 'nombre' => 'P2422H', 'descripcion' => 'Monitor IPS 24" Full HD'],
            ['categoria' => 'Monitor', 'marca' => 'Dell', 'nombre' => 'S2721QS', 'descripcion' => 'Monitor 27" 4K UHD'],
            
            // HP - Laptops
            ['categoria' => 'Laptop', 'marca' => 'HP', 'nombre' => 'EliteBook 840 G9', 'descripcion' => 'Laptop empresarial 14" Core i7'],
            ['categoria' => 'Laptop', 'marca' => 'HP', 'nombre' => 'ProBook 450 G10', 'descripcion' => 'Laptop profesional 15.6" Core i5'],
            ['categoria' => 'Laptop', 'marca' => 'HP', 'nombre' => 'Pavilion 15', 'descripcion' => 'Laptop hogar 15.6" Ryzen 7'],
            
            // HP - Computadoras de Escritorio
            ['categoria' => 'Computadora de Escritorio', 'marca' => 'HP', 'nombre' => 'EliteDesk 800 G9', 'descripcion' => 'Desktop empresarial Core i7'],
            ['categoria' => 'Computadora de Escritorio', 'marca' => 'HP', 'nombre' => 'ProDesk 400 G9', 'descripcion' => 'Desktop oficina Core i5'],
            
            // HP - Monitores
            ['categoria' => 'Monitor', 'marca' => 'HP', 'nombre' => 'M24f', 'descripcion' => 'Monitor 24" Full HD IPS'],
            
            // HP - Impresoras
            ['categoria' => 'Impresora', 'marca' => 'HP', 'nombre' => 'LaserJet Pro M404dn', 'descripcion' => 'Impresora láser monocromática'],
            ['categoria' => 'Impresora', 'marca' => 'HP', 'nombre' => 'DeskJet 4175e', 'descripcion' => 'Impresora multifuncional tinta'],
            
            // Lenovo - Laptops
            ['categoria' => 'Laptop', 'marca' => 'Lenovo', 'nombre' => 'ThinkPad X1 Carbon Gen 11', 'descripcion' => 'Laptop ultraligera 14" Core i7'],
            ['categoria' => 'Laptop', 'marca' => 'Lenovo', 'nombre' => 'ThinkPad E14 Gen 5', 'descripcion' => 'Laptop empresarial 14" Core i5'],
            ['categoria' => 'Laptop', 'marca' => 'Lenovo', 'nombre' => 'IdeaPad 3', 'descripcion' => 'Laptop económica 15.6" Ryzen 3'],
            
            // Lenovo - Computadoras de Escritorio
            ['categoria' => 'Computadora de Escritorio', 'marca' => 'Lenovo', 'nombre' => 'ThinkCentre M720q', 'descripcion' => 'Mini PC empresarial Core i5'],
            ['categoria' => 'Computadora de Escritorio', 'marca' => 'Lenovo', 'nombre' => 'ThinkCentre M90q', 'descripcion' => 'Mini PC alto rendimiento Core i7'],
            
            // Lenovo - Monitores
            ['categoria' => 'Monitor', 'marca' => 'Lenovo', 'nombre' => 'ThinkVision T24i-20', 'descripcion' => 'Monitor 24" Full HD'],
            
            // Samsung - Monitores
            ['categoria' => 'Monitor', 'marca' => 'Samsung', 'nombre' => 'S24R350', 'descripcion' => 'Monitor 24" Full HD IPS'],
            
            // ViewSonic - Monitores
            ['categoria' => 'Monitor', 'marca' => 'ViewSonic', 'nombre' => 'VA2432-H', 'descripcion' => 'Monitor 24" Full HD IPS'],
            
            // Epson - Impresoras
            ['categoria' => 'Impresora', 'marca' => 'Epson', 'nombre' => 'EcoTank L3250', 'descripcion' => 'Impresora tanque de tinta'],
            ['categoria' => 'Impresora', 'marca' => 'Epson', 'nombre' => 'EcoTank L5290', 'descripcion' => 'Impresora multifuncional tanque'],
            
            // Canon - Impresoras
            ['categoria' => 'Impresora', 'marca' => 'Canon', 'nombre' => 'PIXMA G3110', 'descripcion' => 'Impresora tanque de tinta'],
            
            // Brother - Impresoras
            ['categoria' => 'Impresora', 'marca' => 'Brother', 'nombre' => 'DCP-T520W', 'descripcion' => 'Impresora multifuncional tanque'],
            
            // Acer - Laptops
            ['categoria' => 'Laptop', 'marca' => 'Acer', 'nombre' => 'Aspire 5', 'descripcion' => 'Laptop versátil 15.6" Core i5'],
            
            // Asus - Laptops
            ['categoria' => 'Laptop', 'marca' => 'Asus', 'nombre' => 'VivoBook 15', 'descripcion' => 'Laptop delgada 15.6" Core i3'],
            
            // Cisco - Router/Switch
            ['categoria' => 'Router / Switch', 'marca' => 'Cisco', 'nombre' => 'Catalyst 2960', 'descripcion' => 'Switch 24 puertos Gigabit'],
            
            // TP-Link - Router/Switch
            ['categoria' => 'Router / Switch', 'marca' => 'TP-Link', 'nombre' => 'Archer AX73', 'descripcion' => 'Router WiFi 6 dual band'],
            ['categoria' => 'Router / Switch', 'marca' => 'TP-Link', 'nombre' => 'TL-SG1024D', 'descripcion' => 'Switch 24 puertos Gigabit'],
            
            // Kingston - Discos
            ['categoria' => 'Disco Duro / SSD', 'marca' => 'Kingston', 'nombre' => 'A400 SSD 480GB', 'descripcion' => 'SSD SATA 2.5"'],
            ['categoria' => 'Disco Duro / SSD', 'marca' => 'Kingston', 'nombre' => 'NV2 NVMe 1TB', 'descripcion' => 'SSD NVMe M.2'],
            
            // Crucial - Discos
            ['categoria' => 'Disco Duro / SSD', 'marca' => 'Crucial', 'nombre' => 'MX500 SSD 1TB', 'descripcion' => 'SSD SATA 2.5"'],
            
            // Western Digital - Discos
            ['categoria' => 'Disco Duro / SSD', 'marca' => 'Western Digital', 'nombre' => 'Blue HDD 1TB', 'descripcion' => 'Disco duro SATA 3.5"'],
            
            // Seagate - Discos
            ['categoria' => 'Disco Duro / SSD', 'marca' => 'Seagate', 'nombre' => 'Barracuda HDD 2TB', 'descripcion' => 'Disco duro SATA 3.5"'],
            
            // Kingston - RAM
            ['categoria' => 'Memoria RAM', 'marca' => 'Kingston', 'nombre' => 'DDR4 8GB 3200MHz', 'descripcion' => 'Módulo RAM DDR4'],
            ['categoria' => 'Memoria RAM', 'marca' => 'Kingston', 'nombre' => 'DDR4 16GB 3200MHz', 'descripcion' => 'Módulo RAM DDR4'],
            
            // Crucial - RAM
            ['categoria' => 'Memoria RAM', 'marca' => 'Crucial', 'nombre' => 'DDR4 8GB 2666MHz', 'descripcion' => 'Módulo RAM DDR4'],
            ['categoria' => 'Memoria RAM', 'marca' => 'Crucial', 'nombre' => 'DDR4 16GB 2666MHz', 'descripcion' => 'Módulo RAM DDR4'],
            
            // Logitech - Teclados
            ['categoria' => 'Teclado', 'marca' => 'Logitech', 'nombre' => 'K120', 'descripcion' => 'Teclado USB estándar'],
            ['categoria' => 'Teclado', 'marca' => 'Logitech', 'nombre' => 'K400 Plus', 'descripcion' => 'Teclado inalámbrico con touchpad'],
            
            // Microsoft - Teclados
            ['categoria' => 'Teclado', 'marca' => 'Microsoft', 'nombre' => 'Wired Keyboard 600', 'descripcion' => 'Teclado USB estándar'],
            
            // Logitech - Mouse
            ['categoria' => 'Mouse', 'marca' => 'Logitech', 'nombre' => 'M90', 'descripcion' => 'Mouse USB óptico'],
            ['categoria' => 'Mouse', 'marca' => 'Logitech', 'nombre' => 'M170', 'descripcion' => 'Mouse inalámbrico'],
            
            // Microsoft - Mouse
            ['categoria' => 'Mouse', 'marca' => 'Microsoft', 'nombre' => 'Basic Optical Mouse', 'descripcion' => 'Mouse USB óptico'],
            
            // Logitech - Cámaras Web
            ['categoria' => 'Cámara Web', 'marca' => 'Logitech', 'nombre' => 'C920 HD Pro', 'descripcion' => 'Webcam Full HD 1080p'],
            ['categoria' => 'Cámara Web', 'marca' => 'Logitech', 'nombre' => 'C270', 'descripcion' => 'Webcam HD 720p'],
            
            // Microsoft - Cámaras Web
            ['categoria' => 'Cámara Web', 'marca' => 'Microsoft', 'nombre' => 'LifeCam HD-3000', 'descripcion' => 'Webcam HD 720p'],
        ];

        $modelosCreados = [];
        foreach ($modelosData as $mod) {
            $categoria = Categoria::where('nombre', $mod['categoria'])
                                  ->whereHas('marca', function($q) use ($mod) {
                                      $q->where('nombre', $mod['marca']);
                                  })
                                  ->first();

            if ($categoria) {
                $modelo = Modelo::updateOrCreate(
                    ['categoria_id' => $categoria->id, 'nombre' => $mod['nombre']],
                    [
                        'marca_id' => $categoria->marca_id,
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
            'XPS 15' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM DDR5', 'capacidad' => '32GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD NVMe M.2', 'capacidad' => '1TB'],
                ['tipo' => 'Batería', 'descripcion' => 'Batería de litio 6 celdas', 'capacidad' => '86Wh'],
                ['tipo' => 'Cargador', 'descripcion' => 'Cargador USB-C', 'capacidad' => '130W'],
                ['tipo' => 'Pantalla', 'descripcion' => 'Pantalla OLED táctil', 'capacidad' => '15.6" 4K'],
                ['tipo' => 'Procesador', 'descripcion' => 'Intel Core i9-13900H', 'capacidad' => '5.4GHz'],
            ],
            'EliteBook 840 G9' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM DDR5', 'capacidad' => '16GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD NVMe M.2', 'capacidad' => '512GB'],
                ['tipo' => 'Batería', 'descripcion' => 'Batería de litio 3 celdas', 'capacidad' => '51Wh'],
                ['tipo' => 'Cargador', 'descripcion' => 'Cargador USB-C', 'capacidad' => '65W'],
                ['tipo' => 'Pantalla', 'descripcion' => 'Pantalla LED IPS', 'capacidad' => '14" FHD'],
                ['tipo' => 'Procesador', 'descripcion' => 'Intel Core i7-1355U', 'capacidad' => '5.0GHz'],
            ],
            'ThinkPad X1 Carbon Gen 11' => [
                ['tipo' => 'RAM', 'descripcion' => 'Memoria RAM LPDDR5', 'capacidad' => '16GB'],
                ['tipo' => 'Disco', 'descripcion' => 'Disco SSD NVMe M.2', 'capacidad' => '1TB'],
                ['tipo' => 'Batería', 'descripcion' => 'Batería de litio', 'capacidad' => '57Wh'],
                ['tipo' => 'Cargador', 'descripcion' => 'Cargador USB-C', 'capacidad' => '65W'],
                ['tipo' => 'Pantalla', 'descripcion' => 'Pantalla IPS antirreflejo', 'capacidad' => '14" 2.8K'],
                ['tipo' => 'Procesador', 'descripcion' => 'Intel Core i7-1365U', 'capacidad' => '5.2GHz'],
            ],
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
            'P2422H' => [
                ['tipo' => 'Pantalla', 'descripcion' => 'Panel IPS LED', 'capacidad' => '23.8" FHD'],
                ['tipo' => 'Cable', 'descripcion' => 'Cable DisplayPort', 'capacidad' => '1.8m'],
                ['tipo' => 'Cable', 'descripcion' => 'Cable HDMI', 'capacidad' => '1.5m'],
            ],
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
            'A400 SSD 480GB' => [
                ['tipo' => 'Disco', 'descripcion' => 'SSD SATA III', 'capacidad' => '480GB'],
            ],
            'NV2 NVMe 1TB' => [
                ['tipo' => 'Disco', 'descripcion' => 'SSD NVMe PCIe 4.0', 'capacidad' => '1TB'],
            ],
            'Blue HDD 1TB' => [
                ['tipo' => 'Disco', 'descripcion' => 'HDD SATA III 7200RPM', 'capacidad' => '1TB'],
            ],
            'DDR4 8GB 3200MHz' => [
                ['tipo' => 'RAM', 'descripcion' => 'Módulo DDR4 UDIMM', 'capacidad' => '8GB 3200MHz'],
            ],
            'DDR4 16GB 3200MHz' => [
                ['tipo' => 'RAM', 'descripcion' => 'Módulo DDR4 UDIMM', 'capacidad' => '16GB 3200MHz'],
            ],
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
                ['Marcas', count($marcas)],
                ['Categorías', count($categoriasCreadas)],
                ['Modelos', count($modelosCreados)],
                ['Componentes de modelo', $totalComponentes],
            ]
        );
    }
}