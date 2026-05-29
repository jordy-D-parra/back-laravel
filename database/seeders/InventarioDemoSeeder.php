<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activo;
use App\Models\Componente;
use App\Models\Modelo;
use App\Models\ModeloComponente;
use App\Models\Institucion;
use App\Models\Responsable;
use App\Models\Estatus;

class InventarioDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creando datos de demostración para inventario...');

        $institucion = Institucion::first();
        $responsable = Responsable::first();
        $estatusDisponible = Estatus::where('descripcion', 'Disponible')->first();
        $estatusBodega = Estatus::where('descripcion', 'En bodega')->first();

        if (!$institucion || !$responsable || !$estatusDisponible) {
            $this->command->error('Faltan datos base. Ejecuta primero EntidadesSeeder y EstatusSeeder');
            return;
        }

        // Obtener algunos modelos con sus componentes
        $modelos = Modelo::with('modeloComponentes')->take(5)->get();

        $activosCreados = 0;
        $componentesCreados = 0;

        foreach ($modelos as $modelo) {
            // Crear 2 activos por modelo
            for ($i = 1; $i <= 2; $i++) {
                $activo = Activo::create([
                    'serial' => strtoupper(substr($modelo->marca->nombre ?? 'GEN', 0, 3)) . '-' . rand(1000, 9999) . '-' . chr(65 + $i),
                    'modelo_id' => $modelo->id,
                    'id_estatus' => $estatusDisponible->id,
                    'institucion_id' => $institucion->id,
                    'responsable_id' => $responsable->id,
                    'ubicacion' => 'Oficina ' . rand(1, 10) . chr(65 + rand(0, 2)),
                    'fecha_adquisicion' => now()->subMonths(rand(1, 24))->format('Y-m-d'),
                    'fecha_fin_garantia' => now()->addMonths(rand(6, 36))->format('Y-m-d'),
                    'vida_util_anos' => rand(3, 5),
                    'observaciones' => 'Equipo de prueba',
                ]);
                $activosCreados++;

                // Crear componentes reales basados en los componentes del modelo
                foreach ($modelo->modeloComponentes as $compModelo) {
                    $marcasPorTipo = [
                        'RAM' => ['Kingston', 'Crucial', 'Corsair'],
                        'Disco' => ['Samsung', 'Western Digital', 'Kingston'],
                        'Batería' => ['Dell', 'HP', 'Lenovo'],
                        'Cargador' => ['Dell', 'HP', 'Lenovo'],
                        'Pantalla' => ['LG', 'Samsung', 'AUO'],
                        'Procesador' => ['Intel', 'AMD'],
                        'Teclado' => ['Logitech', 'Dell', 'HP'],
                        'Mouse' => ['Logitech', 'Microsoft'],
                    ];

                    $marcas = $marcasPorTipo[$compModelo->tipo] ?? ['Genérica'];

                    Componente::create([
                        'tipo' => $compModelo->tipo,
                        'modelo_componente_id' => $compModelo->id,
                        'marca' => $marcas[array_rand($marcas)],
                        'modelo' => $compModelo->descripcion,
                        'serial' => strtoupper(substr($compModelo->tipo, 0, 3)) . '-' . rand(10000, 99999),
                        'capacidad' => $compModelo->capacidad,
                        'estado' => 'instalado',
                        'activo_id' => $activo->id,
                        'institucion_id' => $institucion->id,
                        'responsable_id' => $responsable->id,
                        'ubicacion' => $activo->ubicacion,
                        'fecha_instalacion' => now(),
                    ]);
                    $componentesCreados++;
                }
            }
        }

        // Crear algunos componentes en bodega (sin activo)
        for ($i = 0; $i < 10; $i++) {
            $tipos = ['RAM', 'Disco', 'Cargador', 'Mouse', 'Teclado'];
            $tipo = $tipos[array_rand($tipos)];

            Componente::create([
                'tipo' => $tipo,
                'modelo_componente_id' => null,
                'marca' => ['Kingston', 'Logitech', 'Samsung', 'Dell'][array_rand(['Kingston', 'Logitech', 'Samsung', 'Dell'])],
                'modelo' => 'Genérico',
                'serial' => 'STOCK-' . strtoupper(substr($tipo, 0, 3)) . '-' . rand(1000, 9999),
                'capacidad' => $tipo === 'RAM' ? '8GB' : ($tipo === 'Disco' ? '512GB' : null),
                'estado' => 'en_bodega',
                'activo_id' => null,
                'institucion_id' => $institucion->id,
                'responsable_id' => $responsable->id,
                'ubicacion' => 'Bodega Central',
            ]);
            $componentesCreados++;
        }

        $this->command->info("✅ $activosCreados activos creados");
        $this->command->info("✅ $componentesCreados componentes creados");
    }
}
