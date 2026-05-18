<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            // Usuarios
            ['nombre' => 'crear-usuario', 'descripcion' => 'Crear nuevos usuarios'],
            ['nombre' => 'editar-usuario', 'descripcion' => 'Editar usuarios existentes'],
            ['nombre' => 'desactivar-usuario', 'descripcion' => 'Activar/desactivar usuarios'],
            ['nombre' => 'ver-usuarios', 'descripcion' => 'Ver listado de usuarios'],

            // Trabajadores
            ['nombre' => 'crear-trabajador', 'descripcion' => 'Registrar nuevos trabajadores'],
            ['nombre' => 'editar-trabajador', 'descripcion' => 'Editar datos de trabajadores'],
            ['nombre' => 'ver-trabajadores', 'descripcion' => 'Ver listado de trabajadores'],

            // Inventario (para futuro)
            ['nombre' => 'gestionar-inventario', 'descripcion' => 'Gestionar el inventario completo'],
            ['nombre' => 'ver-inventario', 'descripcion' => 'Consultar inventario'],

            // Auditoría
            ['nombre' => 'ver-auditoria', 'descripcion' => 'Ver registros de auditoría'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::create($permiso);
        }

        // Asignar todos los permisos al rol admin
        $adminRol = Rol::where('nombre', 'admin')->first();
        $adminRol->permisos()->attach(Permiso::all());

        // Permisos para ingeniero
        $ingenieroRol = Rol::where('nombre', 'ingeniero')->first();
        $ingenieroRol->permisos()->attach(
            Permiso::whereIn('nombre', [
                'ver-usuarios',
                'ver-trabajadores',
                'gestionar-inventario',
                'ver-inventario',
                'ver-auditoria'
            ])->get()
        );

        // Permisos para secretaria
        $secretariaRol = Rol::where('nombre', 'secretaria')->first();
        $secretariaRol->permisos()->attach(
            Permiso::whereIn('nombre', [
                'crear-trabajador',
                'editar-trabajador',
                'ver-trabajadores',
                'ver-inventario',
            ])->get()
        );

        // Permisos para tecnico
        $tecnicoRol = Rol::where('nombre', 'tecnico')->first();
        $tecnicoRol->permisos()->attach(
            Permiso::whereIn('nombre', [
                'ver-trabajadores',
                'ver-inventario',
                'gestionar-inventario',
            ])->get()
        );
    }
}
