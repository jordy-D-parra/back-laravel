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

        // ============================================================
        // DEFINICIÓN COMPLETA DE PERMISOS
        // ============================================================
        $permisos = [
            // ========== DASHBOARD ==========
            ['nombre' => 'ver-dashboard', 'descripcion' => 'Ver el panel de control principal', 'categoria' => 'dashboard'],
            ['nombre' => 'ver-calendario', 'descripcion' => 'Ver el calendario de actividades', 'categoria' => 'dashboard'],

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

            // ========== PRÉSTAMOS ==========
            ['nombre' => 'ver-prestamos', 'descripcion' => 'Ver listado de préstamos', 'categoria' => 'prestamos'],
            ['nombre' => 'crear-prestamo', 'descripcion' => 'Crear nuevos préstamos', 'categoria' => 'prestamos'],
            ['nombre' => 'editar-prestamo', 'descripcion' => 'Editar préstamos existentes', 'categoria' => 'prestamos'],
            ['nombre' => 'devolver-prestamo', 'descripcion' => 'Registrar devolución de préstamos', 'categoria' => 'prestamos'],
            ['nombre' => 'extender-prestamo', 'descripcion' => 'Extender fecha de préstamos', 'categoria' => 'prestamos'],
            ['nombre' => 'cancelar-prestamo', 'descripcion' => 'Cancelar préstamos', 'categoria' => 'prestamos'],
            ['nombre' => 'eliminar-prestamo', 'descripcion' => 'Eliminar préstamos', 'categoria' => 'prestamos'],
            ['nombre' => 'aprobar-prestamo', 'descripcion' => 'Aprobar o rechazar préstamos', 'categoria' => 'prestamos'],

            // ========== SOLICITUDES ==========
            ['nombre' => 'ver-solicitudes', 'descripcion' => 'Ver listado de solicitudes', 'categoria' => 'solicitudes'],
            ['nombre' => 'crear-solicitud', 'descripcion' => 'Crear nuevas solicitudes', 'categoria' => 'solicitudes'],
            ['nombre' => 'editar-solicitud', 'descripcion' => 'Editar solicitudes pendientes', 'categoria' => 'solicitudes'],
            ['nombre' => 'cancelar-solicitud', 'descripcion' => 'Cancelar solicitudes propias', 'categoria' => 'solicitudes'],
            ['nombre' => 'aprobar-solicitudes', 'descripcion' => 'Aprobar o rechazar solicitudes', 'categoria' => 'solicitudes'],
            ['nombre' => 'eliminar-solicitud', 'descripcion' => 'Eliminar solicitudes', 'categoria' => 'solicitudes'],

            // ========== FICHAS DE SOPORTE ==========
            ['nombre' => 'ver-fichas-soporte', 'descripcion' => 'Ver listado de fichas de soporte', 'categoria' => 'soporte'],
            ['nombre' => 'crear-ficha-soporte', 'descripcion' => 'Crear nuevas fichas de soporte', 'categoria' => 'soporte'],
            ['nombre' => 'editar-ficha-soporte', 'descripcion' => 'Editar fichas de soporte', 'categoria' => 'soporte'],
            ['nombre' => 'cerrar-ficha-soporte', 'descripcion' => 'Cerrar/finalizar fichas de soporte', 'categoria' => 'soporte'],
            ['nombre' => 'eliminar-ficha-soporte', 'descripcion' => 'Eliminar fichas de soporte', 'categoria' => 'soporte'],

            // ========== UBICACIONES ==========
            ['nombre' => 'ver-estados', 'descripcion' => 'Ver listado de estados', 'categoria' => 'ubicaciones'],
            ['nombre' => 'crear-estado', 'descripcion' => 'Crear nuevos estados', 'categoria' => 'ubicaciones'],
            ['nombre' => 'editar-estado', 'descripcion' => 'Editar estados', 'categoria' => 'ubicaciones'],
            ['nombre' => 'eliminar-estado', 'descripcion' => 'Eliminar estados', 'categoria' => 'ubicaciones'],
            ['nombre' => 'ver-municipios', 'descripcion' => 'Ver listado de municipios', 'categoria' => 'ubicaciones'],
            ['nombre' => 'crear-municipio', 'descripcion' => 'Crear nuevos municipios', 'categoria' => 'ubicaciones'],
            ['nombre' => 'editar-municipio', 'descripcion' => 'Editar municipios', 'categoria' => 'ubicaciones'],
            ['nombre' => 'eliminar-municipio', 'descripcion' => 'Eliminar municipios', 'categoria' => 'ubicaciones'],
            ['nombre' => 'ver-parroquias', 'descripcion' => 'Ver listado de parroquias', 'categoria' => 'ubicaciones'],
            ['nombre' => 'crear-parroquia', 'descripcion' => 'Crear nuevas parroquias', 'categoria' => 'ubicaciones'],
            ['nombre' => 'editar-parroquia', 'descripcion' => 'Editar parroquias', 'categoria' => 'ubicaciones'],
            ['nombre' => 'eliminar-parroquia', 'descripcion' => 'Eliminar parroquias', 'categoria' => 'ubicaciones'],

            // ========== REPORTES ==========
            ['nombre' => 'ver-reportes', 'descripcion' => 'Ver módulo de reportes', 'categoria' => 'reportes'],
            ['nombre' => 'exportar-reportes', 'descripcion' => 'Exportar reportes a PDF/Excel', 'categoria' => 'reportes'],

            // ========== AUDITORÍA ==========
            ['nombre' => 'ver-auditoria', 'descripcion' => 'Ver bitácora de auditoría', 'categoria' => 'auditoria'],
            ['nombre' => 'administrar-auditoria', 'descripcion' => 'Administrar bitácora de auditoría', 'categoria' => 'auditoria'],

            // ========== NOTIFICACIONES ==========
            ['nombre' => 'ver-notificaciones', 'descripcion' => 'Ver bandeja de notificaciones', 'categoria' => 'notificaciones'],
        ];

        // Crear/actualizar los permisos
        foreach ($permisos as $permiso) {
            Permiso::updateOrCreate(
                ['nombre' => $permiso['nombre']],
                $permiso
            );
        }

        $this->command->info('✅ Permisos creados: ' . count($permisos));

        // ============================================================
        // ASIGNACIÓN DE PERMISOS A CADA ROL
        // ============================================================

        // ---------- ADMIN: TODOS LOS PERMISOS ----------
        $adminRol = Rol::where('nombre', 'admin')->first();
        if ($adminRol) {
            $adminRol->permisos()->sync(Permiso::all()->pluck('id'));
            $this->command->info("✅ Rol 'admin': TODOS los permisos asignados.");
        }

        // ---------- INGENIERO: TODO excepto gestión de usuarios/roles ----------
        $ingenieroRol = Rol::where('nombre', 'ingeniero')->first();
        if ($ingenieroRol) {
            $permisosIngeniero = Permiso::whereNotIn('categoria', ['usuarios', 'roles'])->pluck('id');
            $ingenieroRol->permisos()->sync($permisosIngeniero);
            $this->command->info("✅ Rol 'ingeniero': " . $permisosIngeniero->count() . " permisos asignados.");
        }

        // ---------- SECRETARIA: lectura + gestión de trabajadores ----------
        $secretariaRol = Rol::where('nombre', 'secretaria')->first();
        if ($secretariaRol) {
            $nombresSecretaria = [
                'ver-dashboard', 'ver-calendario', 'ver-notificaciones',
                'ver-trabajadores', 'crear-trabajador', 'editar-trabajador',
                'ver-instituciones', 'ver-departamentos', 'ver-responsables',
                'ver-marcas', 'ver-categorias-equipos', 'ver-modelos',
                'ver-activos', 'ver-componentes',
                'ver-prestamos', 'crear-prestamo', 'editar-prestamo', 'devolver-prestamo',
                'ver-solicitudes', 'crear-solicitud', 'editar-solicitud', 'cancelar-solicitud',
                'ver-fichas-soporte',
                'ver-reportes',
            ];
            $permisosSecretaria = Permiso::whereIn('nombre', $nombresSecretaria)->pluck('id');
            $secretariaRol->permisos()->sync($permisosSecretaria);
            $this->command->info("✅ Rol 'secretaria': " . $permisosSecretaria->count() . " permisos asignados.");
        }

        // ---------- TÉCNICO: soporte + activos ----------
        $tecnicoRol = Rol::where('nombre', 'tecnico')->first();
        if ($tecnicoRol) {
            $nombresTecnico = [
                'ver-dashboard', 'ver-calendario', 'ver-notificaciones',
                'ver-marcas', 'ver-categorias-equipos', 'ver-modelos',
                'ver-activos', 'crear-activo', 'editar-activo',
                'ver-componentes', 'crear-componente', 'editar-componente',
                'ver-fichas-soporte', 'crear-ficha-soporte', 'editar-ficha-soporte',
                'cerrar-ficha-soporte',
                'ver-prestamos',
                'ver-reportes',
            ];
            $permisosTecnico = Permiso::whereIn('nombre', $nombresTecnico)->pluck('id');
            $tecnicoRol->permisos()->sync($permisosTecnico);
            $this->command->info("✅ Rol 'tecnico': " . $permisosTecnico->count() . " permisos asignados.");
        }

        // ============================================================
        // RESUMEN
        // ============================================================
        $this->command->newLine();
        $this->command->table(
            ['Categoría', 'Cantidad'],
            Permiso::select('categoria', DB::raw('count(*) as total'))
                ->groupBy('categoria')
                ->get()
                ->map(fn($item) => [$item->categoria, $item->total])
                ->toArray()
        );

        $this->command->newLine();
        $this->command->table(
            ['Rol', 'Permisos asignados'],
            Rol::withCount('permisos')->get()
                ->map(fn($r) => [$r->nombre, $r->permisos_count])
                ->toArray()
        );
    }
}