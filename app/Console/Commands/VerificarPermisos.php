<?php

namespace App\Console\Commands;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Console\Command;

class VerificarPermisos extends Command
{
    protected $signature = 'permisos:verificar';
    protected $description = 'Verifica el estado del sistema de permisos';

    public function handle(): int
    {
        $this->info('==========================================');
        $this->info('VERIFICACIÓN DEL SISTEMA DE PERMISOS');
        $this->info('==========================================');
        $this->newLine();

        // 1. Contar permisos
        $totalPermisos = Permiso::count();
        $this->info("📋 Total de permisos: {$totalPermisos}");

        // 2. Contar roles
        $totalRoles = Rol::count();
        $this->info("👥 Total de roles: {$totalRoles}");
        $this->newLine();

        // 3. Tabla de roles y sus permisos
        $this->info('📊 Permisos por rol:');
        $this->table(
            ['Rol', 'Permisos', 'Usuarios'],
            Rol::withCount(['permisos', 'usuarios'])->get()
                ->map(fn($r) => [
                    $r->nombre,
                    $r->permisos_count,
                    $r->usuarios_count,
                ])
                ->toArray()
        );
        $this->newLine();

        // 4. Verificar usuarios sin rol
        $sinRol = Usuario::whereNull('rol_id')->count();
        if ($sinRol > 0) {
            $this->warn("⚠️  Hay {$sinRol} usuario(s) SIN ROL asignado");
        } else {
            $this->info("✅ Todos los usuarios tienen rol asignado");
        }
        $this->newLine();

        // 5. Verificar roles sin permisos
        $rolesSinPermisos = Rol::doesntHave('permisos')->get();
        if ($rolesSinPermisos->isNotEmpty()) {
            $this->warn("⚠️  Roles sin permisos asignados:");
            foreach ($rolesSinPermisos as $rol) {
                $this->line("   - {$rol->nombre}");
            }
        } else {
            $this->info("✅ Todos los roles tienen permisos asignados");
        }
        $this->newLine();

        // 6. Lista completa de permisos por categoría
        $this->info('📂 Permisos por categoría:');
        $this->table(
            ['Categoría', 'Cantidad'],
            Permiso::selectRaw('categoria, count(*) as total')
                ->groupBy('categoria')
                ->orderBy('categoria')
                ->get()
                ->map(fn($p) => [$p->categoria, $p->total])
                ->toArray()
        );

        $this->newLine();
        $this->info('==========================================');

        return Command::SUCCESS;
    }
}