<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institucion;
use App\Models\Departamento;
use App\Models\Responsable;

class EntidadesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Gobernación del Estado Yaracuy
        $gobernacion = Institucion::create([
            'nombre' => 'Gobernación del Estado Yaracuy',
            'informacion' => 'Ente gubernamental del Estado Yaracuy',
            'representante' => 'Gobernador del Estado',
            'ubicacion' => 'San Felipe, Yaracuy',
            'activo' => true
        ]);

        // 1.1 Departamento de Informática
        $informatica = Departamento::create([
            'nombre' => 'Departamento de Informática',
            'informacion' => 'Gestión tecnológica de la Gobernación',
            'representante' => 'Jefe de Informática',
            'ubicacion' => 'Sede Principal',
            'activo' => true,
            'institucion_id' => $gobernacion->id
        ]);

        // 1.2 Departamento de Recursos Humanos
        $rrhh = Departamento::create([
            'nombre' => 'Recursos Humanos',
            'informacion' => 'Gestión del personal',
            'representante' => 'Directora de RRHH',
            'ubicacion' => 'Sede Principal',
            'activo' => true,
            'institucion_id' => $gobernacion->id
        ]);

        // 1.3 Responsable del Depto. Informática
        Responsable::create([
            'nombre' => 'Juan Pérez',
            'documento' => 'V-12345678',
            'telefono' => '0412-1234567',
            'email' => 'jperez@gobernacion.gob.ve',
            'cargo' => 'Jefe de Informática',
            'activo' => true,
            'institucion_id' => $gobernacion->id,
            'departamento_id' => $informatica->id
        ]);

        // 1.4 Responsable institucional (sin departamento)
        Responsable::create([
            'nombre' => 'Pedro Gómez',
            'documento' => 'V-11223344',
            'telefono' => '0416-5555555',
            'email' => 'pgomez@gobernacion.gob.ve',
            'cargo' => 'Responsable de Bienes',
            'activo' => true,
            'institucion_id' => $gobernacion->id,
            'departamento_id' => null
        ]);

        // 2. Escuela (sin departamentos, solo responsables directos)
        $escuela = Institucion::create([
            'nombre' => 'Escuela Bolivariana Simón Bolívar',
            'informacion' => 'Institución educativa',
            'representante' => 'Director',
            'ubicacion' => 'Cocorote, Yaracuy',
            'activo' => true
        ]);

        Responsable::create([
            'nombre' => 'Luis Martínez',
            'documento' => 'V-99887766',
            'telefono' => '0424-1112233',
            'email' => 'lmartinez@escuela.gob.ve',
            'cargo' => 'Director',
            'activo' => true,
            'institucion_id' => $escuela->id,
            'departamento_id' => null
        ]);

        // 3. Hospital
        $hospital = Institucion::create([
            'nombre' => 'Hospital Central de San Felipe',
            'informacion' => 'Centro de salud principal',
            'representante' => 'Director Médico',
            'ubicacion' => 'San Felipe, Yaracuy',
            'activo' => true
        ]);

        $administracion = Departamento::create([
            'nombre' => 'Administración',
            'informacion' => 'Departamento administrativo',
            'representante' => 'Administrador',
            'ubicacion' => 'Piso 1',
            'activo' => true,
            'institucion_id' => $hospital->id
        ]);

        Responsable::create([
            'nombre' => 'María Rodríguez',
            'documento' => 'V-55443322',
            'telefono' => '0426-7778899',
            'email' => 'mrodriguez@hospital.gob.ve',
            'cargo' => 'Administradora',
            'activo' => true,
            'institucion_id' => $hospital->id,
            'departamento_id' => $administracion->id
        ]);
    }
}
