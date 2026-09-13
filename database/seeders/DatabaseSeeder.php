<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('==============================');
        $this->command->info('Iniciando seeding del sistema...');
        $this->command->info('==============================');

        // ========== 1. TABLAS BASE (sin dependencias) ==========
        $this->call(RolSeeder::class);
        $this->call(PermisoSeeder::class);
        $this->call(TrabajadorSeeder::class);
        $this->call(EstatusSeeder::class);

        // ========== 2. UBICACIONES GEOGRÁFICAS (¡ANTES DE ENTIDADES!) ==========
        $this->call(EstadosVenezuelaSeeder::class);
        $this->call(MunicipiosYaracuySeeder::class);
        $this->call(ParroquiasYaracuySeeder::class);

        // ========== 3. ENTIDADES (AHORA CON UBICACIÓN) ==========
        $this->call(EntidadesSeeder::class);

        // ========== 4. USUARIO ADMIN ==========
        $this->call(UsuarioAdminSeeder::class);

        // ========== 5. CATÁLOGO DE EQUIPOS ==========
        $this->call(EquiposDemoSeeder::class);

        // ========== 6. INVENTARIO ==========
        $this->call(InventarioDemoSeeder::class);

        // ========== 7. SOLICITUDES Y PRÉSTAMOS ==========
        $this->call(SolicitudPrestamoDemoSeeder::class);


        $this->command->info('==============================');
        $this->command->info('✅ Seeding completado');
        $this->command->info('==============================');
    }
}
