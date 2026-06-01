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
            // 'representante' => 'Gobernador del Estado', // ELIMINADO
            'ubicacion' => 'San Felipe, Yaracuy',
            'activo' => true
        ]);

        // Crear el representante de la institución
        Responsable::create([
            'nombre' => 'Gobernador del Estado',
            'documento' => 'V-12345678',
            'telefono' => '0412-1234567',
            'email' => 'gobernador@yaracuy.gob.ve',
            'direccion' => 'Palacio de Gobierno, San Felipe',
            'cargo' => 'Gobernador',
            'activo' => true,
            'institucion_id' => $gobernacion->id,
            'departamento_id' => null,
        ]);

        // 1.1 Departamento de Informática
        $informatica = Departamento::create([
            'nombre' => 'Departamento de Informática',
            'informacion' => 'Gestión tecnológica de la Gobernación',
            // 'representante' => 'Jefe de Informática', // ELIMINADO
            'ubicacion' => 'Sede Principal',
            'activo' => true,
            'institucion_id' => $gobernacion->id
        ]);

        // 1.2 Responsable del Depto. Informática (reutilizar o crear nuevo)
        Responsable::create([
            'nombre' => 'Juan Pérez',
            'documento' => 'V-12345678',
            'telefono' => '0412-1234567',
            'email' => 'jperez@gobernacion.gob.ve',
            'direccion' => 'Sede Principal, Oficina 3B',
            'cargo' => 'Jefe de Informática',
            'activo' => true,
            'institucion_id' => $gobernacion->id,
            'departamento_id' => $informatica->id
        ]);

        // 1.3 Departamento de Recursos Humanos
        $rrhh = Departamento::create([
            'nombre' => 'Recursos Humanos',
            'informacion' => 'Gestión del personal',
            // 'representante' => 'Directora de RRHH', // ELIMINADO
            'ubicacion' => 'Sede Principal, Piso 2',
            'activo' => true,
            'institucion_id' => $gobernacion->id
        ]);

        // Responsable de RRHH
        Responsable::create([
            'nombre' => 'María González',
            'documento' => 'V-87654321',
            'telefono' => '0414-7654321',
            'email' => 'mgonzalez@gobernacion.gob.ve',
            'direccion' => 'Sede Principal, Piso 2',
            'cargo' => 'Directora de RRHH',
            'activo' => true,
            'institucion_id' => $gobernacion->id,
            'departamento_id' => $rrhh->id
        ]);

        // 1.4 Responsable institucional adicional (sin departamento)
        Responsable::create([
            'nombre' => 'Pedro Gómez',
            'documento' => 'V-11223344',
            'telefono' => '0416-5555555',
            'email' => 'pgomez@gobernacion.gob.ve',
            'direccion' => 'Sede Principal',
            'cargo' => 'Responsable de Bienes',
            'activo' => true,
            'institucion_id' => $gobernacion->id,
            'departamento_id' => null
        ]);

        // 2. Escuela (sin departamentos, solo responsables directos)
        $escuela = Institucion::create([
            'nombre' => 'Escuela Bolivariana Simón Bolívar',
            'informacion' => 'Institución educativa',
            // 'representante' => 'Director', // ELIMINADO
            'ubicacion' => 'Cocorote, Yaracuy',
            'activo' => true
        ]);

        Responsable::create([
            'nombre' => 'Luis Martínez',
            'documento' => 'V-99887766',
            'telefono' => '0424-1112233',
            'email' => 'lmartinez@escuela.gob.ve',
            'direccion' => 'Cocorote, Yaracuy',
            'cargo' => 'Director',
            'activo' => true,
            'institucion_id' => $escuela->id,
            'departamento_id' => null
        ]);

        // 3. Hospital
        $hospital = Institucion::create([
            'nombre' => 'Hospital Central de San Felipe',
            'informacion' => 'Centro de salud principal',
            // 'representante' => 'Director Médico', // ELIMINADO
            'ubicacion' => 'San Felipe, Yaracuy',
            'activo' => true
        ]);

        $administracion = Departamento::create([
            'nombre' => 'Administración',
            'informacion' => 'Departamento administrativo',
            // 'representante' => 'Administrador', // ELIMINADO
            'ubicacion' => 'Piso 1',
            'activo' => true,
            'institucion_id' => $hospital->id
        ]);

        Responsable::create([
            'nombre' => 'María Rodríguez',
            'documento' => 'V-55443322',
            'telefono' => '0426-7778899',
            'email' => 'mrodriguez@hospital.gob.ve',
            'direccion' => 'Hospital Central, Piso 1',
            'cargo' => 'Administradora',
            'activo' => true,
            'institucion_id' => $hospital->id,
            'departamento_id' => $administracion->id
        ]);

        // Responsable institucional del hospital
        Responsable::create([
            'nombre' => 'Dr. Carlos Méndez',
            'documento' => 'V-12345678',
            'telefono' => '0412-8887766',
            'email' => 'cmendez@hospital.gob.ve',
            'direccion' => 'Hospital Central',
            'cargo' => 'Director Médico',
            'activo' => true,
            'institucion_id' => $hospital->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ Entidades creadas: ' . Institucion::count() . ' instituciones, ' . Departamento::count() . ' departamentos, ' . Responsable::count() . ' responsables');
    }
}
