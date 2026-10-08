<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👥 Creando 20 roles del sistema...');

        $roles = [
            // ========== ROLES PRINCIPALES ==========
            ['nombre' => 'admin',       'descripcion' => 'Administrador del sistema con acceso total'],
            ['nombre' => 'super_admin', 'descripcion' => 'Super Administrador con control absoluto'],
            ['nombre' => 'ingeniero',   'descripcion' => 'Ingeniero del departamento - Control total sobre inventario y equipos'],
            ['nombre' => 'tecnico',     'descripcion' => 'Técnico de soporte - Puede crear y editar activos, pero no eliminar'],
            ['nombre' => 'secretaria',  'descripcion' => 'Secretaria del departamento - Solo lectura en inventario'],

            // ========== ROLES OPERATIVOS ==========
            ['nombre' => 'supervisor',  'descripcion' => 'Supervisor de operaciones y personal'],
            ['nombre' => 'coordinador', 'descripcion' => 'Coordinador de área o departamento'],
            ['nombre' => 'analista',    'descripcion' => 'Analista de sistemas y datos'],
            ['nombre' => 'asistente',   'descripcion' => 'Asistente administrativo'],
            ['nombre' => 'auxiliar',    'descripcion' => 'Auxiliar de oficina y apoyo'],

            // ========== ROLES TÉCNICOS ==========
            ['nombre' => 'desarrollador', 'descripcion' => 'Desarrollador de software y aplicaciones'],
            ['nombre' => 'dba',           'descripcion' => 'Administrador de bases de datos'],
            ['nombre' => 'soporte_ti',    'descripcion' => 'Soporte técnico de tecnologías de información'],
            ['nombre' => 'redes',         'descripcion' => 'Especialista en redes y comunicaciones'],
            ['nombre' => 'seguridad',     'descripcion' => 'Especialista en seguridad informática'],

            // ========== ROLES ADMINISTRATIVOS ==========
            ['nombre' => 'contador',    'descripcion' => 'Contador público y financiero'],
            ['nombre' => 'auditor',     'descripcion' => 'Auditor interno de procesos'],
            ['nombre' => 'rrhh',        'descripcion' => 'Analista de recursos humanos'],
            ['nombre' => 'compras',     'descripcion' => 'Encargado de compras y adquisiciones'],
            ['nombre' => 'almacen',     'descripcion' => 'Encargado de almacén e inventario'],
        ];

        $creados = 0;
        $existentes = 0;

        foreach ($roles as $data) {
            $rol = Rol::firstOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );

            if ($rol->wasRecentlyCreated) {
                $creados++;
            } else {
                $existentes++;
            }
        }

        // ============ RESUMEN ============
        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('✅ SEEDER DE ROLES COMPLETADO');
        $this->command->info('========================================');
        $this->command->info("   • Nuevos roles creados: {$creados}");
        $this->command->info("   • Ya existentes (omitidos): {$existentes}");
        $this->command->info('   • Total en sistema: ' . Rol::count());
        $this->command->newLine();

        $this->command->table(
            ['ID', 'Nombre', 'Descripción'],
            Rol::orderBy('id')
                ->get()
                ->map(fn($r) => [
                    $r->id,
                    $r->nombre,
                    $r->descripcion,
                ])
                ->toArray()
        );
    }
}
