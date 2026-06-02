<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('==============================');
        $this->command->info('Iniciando seeding del sistema...');
        $this->command->info('==============================');

        // ========== 1. PRIMERO: TABLAS BASE (sin dependencias) ==========
        $this->call(RolSeeder::class);
        $this->call(PermisoSeeder::class);

        // ========== 2. TRABAJADORES (base para usuarios) ==========
        //$this->call(TrabajadorSeeder::class);

        // ========== 3. ESTATUS (base para inventario) ==========
        $this->call(EstatusSeeder::class);

        // ========== 4. ENTIDADES (instituciones, departamentos, responsables) ==========
      //  $this->call(EntidadesSeeder::class);

        // ========== 5. USUARIO ADMIN (depende de trabajadores y roles) ==========
      //  $this->call(UsuarioAdminSeeder::class);

        // ========== 6. CATÁLOGO DE EQUIPOS (depende de categorías y marcas) ==========
        $this->call(EquiposDemoSeeder::class);
       // $this->call(UsuarioAdminSeeder::class);
       // $this->call(SolicitudDemoSeeder::class);
        // ========== 7. INVENTARIO (depende de equipos, entidades y estatus) ==========
        $this->call(InventarioDemoSeeder::class);

        $this->command->info('==============================');
        $this->command->info('✅ Seeding completado exitosamente');
        $this->command->info('==============================');
    }
}
