<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar relaciones existentes
        DB::table('permiso_rol')->truncate();

        $permisos = [
            // ========== DASHBOARD ==========
            ['nombre' => 'ver-dashboard', 'descripcion' => 'Ver el panel de control principal', 'categoria' => 'dashboard'],

            // ========== USUARIOS ==========
            ['nombre' => 'ver-usuarios', 'descripcion' => 'Ver listado de usuarios', 'categoria' => 'usuarios'],
            ['nombre' => 'crear-usuario', 'descripcion' => 'Crear nuevos usuarios', 'categoria' => 'usuarios'],
            ['nombre' => 'editar-usuario', 'descripcion' => 'Editar usuarios existentes', 'categoria' => 'usuarios'],
            ['nombre' => 'eliminar-usuario', 'descripcion' => 'Eliminar usuarios', 'categoria' => 'usuarios'],
            ['nombre' => 'resetear-password-usuario', 'descripcion' => 'Resetear contraseña de usuarios', 'categoria' => 'usuarios'],
            ['nombre' => 'activar-desactivar-usuario', 'descripcion' => 'Activar/desactivar usuarios', 'categoria' => 'usuarios'],

            // ========== TRABAJADORES ==========
            ['nombre' => 'ver-trabajadores', 'descripcion' => 'Ver listado de trabajadores', 'categoria' => 'trabajadores'],
            ['nombre' => 'crear-trabajador', 'descripcion' => 'Registrar nuevos trabajadores', 'categoria' => 'trabajadores'],
            ['nombre' => 'editar-trabajador', 'descripcion' => 'Editar datos de trabajadores', 'categoria' => 'trabajadores'],
            ['nombre' => 'eliminar-trabajador', 'descripcion' => 'Eliminar trabajadores', 'categoria' => 'trabajadores'],

            // ========== ROLES ==========
            ['nombre' => 'ver-roles', 'descripcion' => 'Ver listado de roles', 'categoria' => 'roles'],
            ['nombre' => 'crear-rol', 'descripcion' => 'Crear nuevos roles', 'categoria' => 'roles'],
            ['nombre' => 'editar-rol', 'descripcion' => 'Editar roles existentes', 'categoria' => 'roles'],
            ['nombre' => 'eliminar-rol', 'descripcion' => 'Eliminar roles', 'categoria' => 'roles'],
            ['nombre' => 'asignar-permisos', 'descripcion' => 'Asignar permisos a roles', 'categoria' => 'roles'],

            // ========== INSTITUCIONES ==========
            ['nombre' => 'ver-instituciones', 'descripcion' => 'Ver listado de instituciones', 'categoria' => 'instituciones'],
            ['nombre' => 'crear-institucion', 'descripcion' => 'Crear nuevas instituciones', 'categoria' => 'instituciones'],
            ['nombre' => 'editar-institucion', 'descripcion' => 'Editar instituciones', 'categoria' => 'instituciones'],
            ['nombre' => 'eliminar-institucion', 'descripcion' => 'Eliminar instituciones', 'categoria' => 'instituciones'],

            // ========== DEPARTAMENTOS ==========
            ['nombre' => 'ver-departamentos', 'descripcion' => 'Ver listado de departamentos', 'categoria' => 'departamentos'],
            ['nombre' => 'crear-departamento', 'descripcion' => 'Crear nuevos departamentos', 'categoria' => 'departamentos'],
            ['nombre' => 'editar-departamento', 'descripcion' => 'Editar departamentos', 'categoria' => 'departamentos'],
            ['nombre' => 'eliminar-departamento', 'descripcion' => 'Eliminar departamentos', 'categoria' => 'departamentos'],

            // ========== RESPONSABLES ==========
            ['nombre' => 'ver-responsables', 'descripcion' => 'Ver listado de responsables', 'categoria' => 'responsables'],
            ['nombre' => 'crear-responsable', 'descripcion' => 'Crear nuevos responsables', 'categoria' => 'responsables'],
            ['nombre' => 'editar-responsable', 'descripcion' => 'Editar responsables', 'categoria' => 'responsables'],
            ['nombre' => 'eliminar-responsable', 'descripcion' => 'Eliminar responsables', 'categoria' => 'responsables'],

            // ========== MARCAS ==========
            ['nombre' => 'ver-marcas', 'descripcion' => 'Ver listado de marcas', 'categoria' => 'marcas'],
            ['nombre' => 'crear-marca', 'descripcion' => 'Crear nuevas marcas', 'categoria' => 'marcas'],
            ['nombre' => 'editar-marca', 'descripcion' => 'Editar marcas', 'categoria' => 'marcas'],
            ['nombre' => 'eliminar-marca', 'descripcion' => 'Eliminar marcas', 'categoria' => 'marcas'],

            // ========== CATEGORÍAS DE EQUIPOS ==========
            ['nombre' => 'ver-categorias-equipos', 'descripcion' => 'Ver listado de categorías de equipos', 'categoria' => 'categorias'],
            ['nombre' => 'crear-categoria-equipo', 'descripcion' => 'Crear nuevas categorías de equipos', 'categoria' => 'categorias'],
            ['nombre' => 'editar-categoria-equipo', 'descripcion' => 'Editar categorías de equipos', 'categoria' => 'categorias'],
            ['nombre' => 'eliminar-categoria-equipo', 'descripcion' => 'Eliminar categorías de equipos', 'categoria' => 'categorias'],

            // ========== MODELOS ==========
            ['nombre' => 'ver-modelos', 'descripcion' => 'Ver listado de modelos', 'categoria' => 'modelos'],
            ['nombre' => 'crear-modelo', 'descripcion' => 'Crear nuevos modelos', 'categoria' => 'modelos'],
            ['nombre' => 'editar-modelo', 'descripcion' => 'Editar modelos', 'categoria' => 'modelos'],
            ['nombre' => 'eliminar-modelo', 'descripcion' => 'Eliminar modelos', 'categoria' => 'modelos'],

            // ========== ACTIVOS ==========
            ['nombre' => 'ver-activos', 'descripcion' => 'Ver listado de activos', 'categoria' => 'activos'],
            ['nombre' => 'crear-activo', 'descripcion' => 'Crear nuevos activos', 'categoria' => 'activos'],
            ['nombre' => 'editar-activo', 'descripcion' => 'Editar activos', 'categoria' => 'activos'],
            ['nombre' => 'eliminar-activo', 'descripcion' => 'Eliminar activos', 'categoria' => 'activos'],
            ['nombre' => 'cambiar-estatus-activo', 'descripcion' => 'Cambiar estado de activos', 'categoria' => 'activos'],

            // ========== COMPONENTES ==========
            ['nombre' => 'ver-componentes', 'descripcion' => 'Ver listado de componentes', 'categoria' => 'componentes'],
            ['nombre' => 'crear-componente', 'descripcion' => 'Crear nuevos componentes', 'categoria' => 'componentes'],
            ['nombre' => 'editar-componente', 'descripcion' => 'Editar componentes', 'categoria' => 'componentes'],
            ['nombre' => 'eliminar-componente', 'descripcion' => 'Eliminar componentes', 'categoria' => 'componentes'],

            // ========== SOLICITUDES ==========
            ['nombre' => 'ver-solicitudes', 'descripcion' => 'Ver listado de solicitudes', 'categoria' => 'solicitudes'],
            ['nombre' => 'crear-solicitud', 'descripcion' => 'Crear nuevas solicitudes', 'categoria' => 'solicitudes'],
            ['nombre' => 'editar-solicitud', 'descripcion' => 'Editar solicitudes pendientes', 'categoria' => 'solicitudes'],
            ['nombre' => 'cancelar-solicitud', 'descripcion' => 'Cancelar solicitudes propias', 'categoria' => 'solicitudes'],
            ['nombre' => 'aprobar-solicitudes', 'descripcion' => 'Aprobar o rechazar solicitudes', 'categoria' => 'solicitudes'],

            // ========== FICHAS DE SOPORTE ==========
           ['nombre' => 'ver-fichas-soporte', 'descripcion' => 'Ver listado de fichas de soporte', 'categoria' => 'soporte'],
           ['nombre' => 'crear-ficha-soporte', 'descripcion' => 'Crear nuevas fichas de soporte', 'categoria' => 'soporte'],
           ['nombre' => 'editar-ficha-soporte', 'descripcion' => 'Editar fichas de soporte', 'categoria' => 'soporte'],
           ['nombre' => 'cerrar-ficha-soporte', 'descripcion' => 'Cerrar/finalizar fichas de soporte', 'categoria' => 'soporte'],
           ['nombre' => 'eliminar-ficha-soporte', 'descripcion' => 'Eliminar fichas de soporte', 'categoria' => 'soporte'],
            ];

        foreach ($permisos as $permiso) {
            Permiso::updateOrCreate(
                ['nombre' => $permiso['nombre']],
                $permiso
            );
        }

        // Asignar TODOS los permisos al rol admin
        $adminRol = Rol::where('nombre', 'admin')->first();
        if ($adminRol) {
            $adminRol->permisos()->sync(Permiso::all()->pluck('id'));
            $this->command->info('✅ Permisos asignados al rol admin: ' . Permiso::count());
        }

        $this->command->info('✅ Permisos creados: ' . count($permisos));

        // Mostrar tabla resumen
        $this->command->table(
            ['Categoría', 'Cantidad'],
            Permiso::select('categoria', DB::raw('count(*) as total'))->groupBy('categoria')->get()->map(fn($item) => [$item->categoria, $item->total])->toArray()
        );
    }
}
