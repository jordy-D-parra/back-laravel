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
        $this->command->info('Creando entidades (instituciones, departamentos y responsables)...');

        // ============================================
        // 1. GOBERNACIÓN DEL ESTADO YARACUY
        // ============================================
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
            'ubicacion' => 'Sede Principal - Piso 3',
            'activo' => true,
            'institucion_id' => $gobernacion->id
        ]);

        // 1.2 Departamento de Recursos Humanos
        $rrhh = Departamento::create([
            'nombre' => 'Recursos Humanos',
            'informacion' => 'Gestión del personal de la Gobernación',
            'representante' => 'Directora de RRHH',
            'ubicacion' => 'Sede Principal - Piso 1',
            'activo' => true,
            'institucion_id' => $gobernacion->id
        ]);

        // 1.3 Departamento de Finanzas
        $finanzas = Departamento::create([
            'nombre' => 'Finanzas',
            'informacion' => 'Gestión financiera y presupuestaria',
            'representante' => 'Director de Finanzas',
            'ubicacion' => 'Sede Principal - Piso 2',
            'activo' => true,
            'institucion_id' => $gobernacion->id
        ]);

        // 1.4 Departamento de Mantenimiento
        $mantenimiento = Departamento::create([
            'nombre' => 'Mantenimiento',
            'informacion' => 'Mantenimiento de infraestructura y equipos',
            'representante' => 'Jefe de Mantenimiento',
            'ubicacion' => 'Sede Principal - Sótano',
            'activo' => true,
            'institucion_id' => $gobernacion->id
        ]);

        // 1.5 Responsable del Depto. Informática
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

        // 1.6 Responsable del Depto. RRHH
        Responsable::create([
            'nombre' => 'María González',
            'documento' => 'V-87654321',
            'telefono' => '0416-8765432',
            'email' => 'mgonzalez@gobernacion.gob.ve',
            'cargo' => 'Directora de RRHH',
            'activo' => true,
            'institucion_id' => $gobernacion->id,
            'departamento_id' => $rrhh->id
        ]);

        // 1.7 Responsable institucional (sin departamento - Representante de la Gobernación)
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

        $this->command->info('✅ Gobernación creada con 4 departamentos');

        // ============================================
        // 2. ESCUELA BOLIVARIANA SIMÓN BOLÍVAR
        // ============================================
        $escuela = Institucion::create([
            'nombre' => 'Escuela Bolivariana Simón Bolívar',
            'informacion' => 'Institución educativa de educación primaria',
            'representante' => 'Director',
            'ubicacion' => 'Cocorote, Yaracuy',
            'activo' => true
        ]);

        // 2.1 Departamento de Dirección
        $direccionEscuela = Departamento::create([
            'nombre' => 'Dirección',
            'informacion' => 'Dirección y coordinación académica',
            'representante' => 'Director',
            'ubicacion' => 'Edificio Principal',
            'activo' => true,
            'institucion_id' => $escuela->id
        ]);

        // 2.2 Departamento de Personal Docente
        $docentes = Departamento::create([
            'nombre' => 'Personal Docente',
            'informacion' => 'Coordinación de profesores y maestros',
            'representante' => 'Coordinador Docente',
            'ubicacion' => 'Edificio Principal - Piso 1',
            'activo' => true,
            'institucion_id' => $escuela->id
        ]);

        // 2.3 Responsable del Depto. Dirección
        Responsable::create([
            'nombre' => 'Luis Martínez',
            'documento' => 'V-99887766',
            'telefono' => '0424-1112233',
            'email' => 'lmartinez@escuela.gob.ve',
            'cargo' => 'Director',
            'activo' => true,
            'institucion_id' => $escuela->id,
            'departamento_id' => $direccionEscuela->id
        ]);

        // 2.4 Responsable institucional (sin departamento)
        Responsable::create([
            'nombre' => 'Ana Rodríguez',
            'documento' => 'V-88776655',
            'telefono' => '0424-2223344',
            'email' => 'arodriguez@escuela.gob.ve',
            'cargo' => 'Subdirectora',
            'activo' => true,
            'institucion_id' => $escuela->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ Escuela creada con 2 departamentos');

        // ============================================
        // 3. HOSPITAL CENTRAL DE SAN FELIPE
        // ============================================
        $hospital = Institucion::create([
            'nombre' => 'Hospital Central de San Felipe',
            'informacion' => 'Centro de salud principal del estado',
            'representante' => 'Director Médico',
            'ubicacion' => 'San Felipe, Yaracuy',
            'activo' => true
        ]);

        // 3.1 Departamento de Administración
        $administracion = Departamento::create([
            'nombre' => 'Administración',
            'informacion' => 'Departamento administrativo y financiero',
            'representante' => 'Administrador',
            'ubicacion' => 'Piso 1 - Oficina 101',
            'activo' => true,
            'institucion_id' => $hospital->id
        ]);

        // 3.2 Departamento de Emergencia
        $emergencia = Departamento::create([
            'nombre' => 'Emergencia',
            'informacion' => 'Servicio de emergencias médicas',
            'representante' => 'Jefe de Emergencia',
            'ubicacion' => 'Piso 0 - Área de Urgencias',
            'activo' => true,
            'institucion_id' => $hospital->id
        ]);

        // 3.3 Departamento de Farmacia
        $farmacia = Departamento::create([
            'nombre' => 'Farmacia',
            'informacion' => 'Dispensación de medicamentos',
            'representante' => 'Jefe de Farmacia',
            'ubicacion' => 'Piso 1 - Oficina 110',
            'activo' => true,
            'institucion_id' => $hospital->id
        ]);

        // 3.4 Responsable del Depto. Administración
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

        // 3.5 Responsable del Depto. Emergencia
        Responsable::create([
            'nombre' => 'Dr. Carlos Sánchez',
            'documento' => 'V-44332211',
            'telefono' => '0414-5556677',
            'email' => 'csanchez@hospital.gob.ve',
            'cargo' => 'Jefe de Emergencia',
            'activo' => true,
            'institucion_id' => $hospital->id,
            'departamento_id' => $emergencia->id
        ]);

        // 3.6 Responsable institucional (sin departamento - Representante del Hospital)
        Responsable::create([
            'nombre' => 'Dr. Roberto Méndez',
            'documento' => 'V-33221100',
            'telefono' => '0412-9998877',
            'email' => 'rmendez@hospital.gob.ve',
            'cargo' => 'Director Médico',
            'activo' => true,
            'institucion_id' => $hospital->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ Hospital creado con 3 departamentos');

        // ============================================
        // 4. ALCALDÍA DEL MUNICIPIO SAN FELIPE
        // ============================================
        $alcaldia = Institucion::create([
            'nombre' => 'Alcaldía del Municipio San Felipe',
            'informacion' => 'Ente municipal de San Felipe',
            'representante' => 'Alcalde',
            'ubicacion' => 'San Felipe, Yaracuy',
            'activo' => true
        ]);

        // 4.1 Departamento de Obras Públicas
        $obras = Departamento::create([
            'nombre' => 'Obras Públicas',
            'informacion' => 'Planificación y ejecución de obras municipales',
            'representante' => 'Director de Obras',
            'ubicacion' => 'Sede Principal - Planta Baja',
            'activo' => true,
            'institucion_id' => $alcaldia->id
        ]);

        // 4.2 Departamento de Cultura
        $cultura = Departamento::create([
            'nombre' => 'Cultura y Deporte',
            'informacion' => 'Promoción de actividades culturales y deportivas',
            'representante' => 'Director de Cultura',
            'ubicacion' => 'Casa de la Cultura',
            'activo' => true,
            'institucion_id' => $alcaldia->id
        ]);

        // 4.3 Responsable institucional
        Responsable::create([
            'nombre' => 'Alberto Díaz',
            'documento' => 'V-22110099',
            'telefono' => '0412-3334455',
            'email' => 'adiaz@alcaldia.gob.ve',
            'cargo' => 'Alcalde',
            'activo' => true,
            'institucion_id' => $alcaldia->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ Alcaldía creada con 2 departamentos');

        // ============================================
        // 5. UNIVERSIDAD NACIONAL EXPERIMENTAL (UNEY)
        // ============================================
        $uney = Institucion::create([
            'nombre' => 'Universidad Nacional Experimental de Yaracuy (UNEY)',
            'informacion' => 'Institución de educación superior',
            'representante' => 'Rector',
            'ubicacion' => 'San Felipe, Yaracuy',
            'activo' => true
        ]);

        // 5.1 Departamento de Ingeniería
        $ingenieria = Departamento::create([
            'nombre' => 'Ingeniería',
            'informacion' => 'Carreras de ingeniería civil, sistemas y eléctrica',
            'representante' => 'Director de Ingeniería',
            'ubicacion' => 'Edificio de Ingeniería - Piso 2',
            'activo' => true,
            'institucion_id' => $uney->id
        ]);

        // 5.2 Departamento de Ciencias Sociales
        $sociales = Departamento::create([
            'nombre' => 'Ciencias Sociales',
            'informacion' => 'Carreras de administración, contaduría y educación',
            'representante' => 'Director de Ciencias Sociales',
            'ubicacion' => 'Edificio de Ciencias Sociales',
            'activo' => true,
            'institucion_id' => $uney->id
        ]);

        // 5.3 Responsable institucional
        Responsable::create([
            'nombre' => 'Dra. Elena Torres',
            'documento' => 'V-11009988',
            'telefono' => '0414-7776644',
            'email' => 'etorres@uney.edu.ve',
            'cargo' => 'Rectora',
            'activo' => true,
            'institucion_id' => $uney->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ Universidad creada con 2 departamentos');

        // ============================================
        // 6. EMPRESA DE SERVICIOS PÚBLICOS
        // ============================================
        $servicios = Institucion::create([
            'nombre' => 'Empresa de Servicios Públicos de Yaracuy',
            'informacion' => 'Gestión de servicios de agua, electricidad y aseo',
            'representante' => 'Gerente General',
            'ubicacion' => 'San Felipe, Yaracuy',
            'activo' => true
        ]);

        // 6.1 Departamento de Agua
        $agua = Departamento::create([
            'nombre' => 'Agua Potable',
            'informacion' => 'Gestión del servicio de agua potable',
            'representante' => 'Director de Agua',
            'ubicacion' => 'Planta de Tratamiento',
            'activo' => true,
            'institucion_id' => $servicios->id
        ]);

        // 6.2 Departamento de Electricidad
        $electricidad = Departamento::create([
            'nombre' => 'Electricidad',
            'informacion' => 'Gestión del servicio eléctrico',
            'representante' => 'Director de Electricidad',
            'ubicacion' => 'Subestación Eléctrica',
            'activo' => true,
            'institucion_id' => $servicios->id
        ]);

        // 6.3 Responsable institucional
        Responsable::create([
            'nombre' => 'Ing. José Pereira',
            'documento' => 'V-00998877',
            'telefono' => '0412-5553322',
            'email' => 'jpereira@servicios.gob.ve',
            'cargo' => 'Gerente General',
            'activo' => true,
            'institucion_id' => $servicios->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ Empresa de Servicios creada con 2 departamentos');

        // ============================================
        // 7. POLICÍA DEL ESTADO YARACUY
        // ============================================
        $policia = Institucion::create([
            'nombre' => 'Policía del Estado Yaracuy',
            'informacion' => 'Cuerpo de seguridad ciudadana del estado',
            'representante' => 'Comisario Jefe',
            'ubicacion' => 'San Felipe, Yaracuy',
            'activo' => true
        ]);

        // 7.1 Departamento de Seguridad Ciudadana
        $seguridad = Departamento::create([
            'nombre' => 'Seguridad Ciudadana',
            'informacion' => 'Patrullaje y atención de emergencias',
            'representante' => 'Jefe de Seguridad',
            'ubicacion' => 'Comando Central',
            'activo' => true,
            'institucion_id' => $policia->id
        ]);

        // 7.2 Departamento de Tránsito
        $transito = Departamento::create([
            'nombre' => 'Tránsito y Vialidad',
            'informacion' => 'Control de tránsito y accidentes',
            'representante' => 'Jefe de Tránsito',
            'ubicacion' => 'Comando de Tránsito',
            'activo' => true,
            'institucion_id' => $policia->id
        ]);

        // 7.3 Responsable institucional
        Responsable::create([
            'nombre' => 'Comisario Luis Ramírez',
            'documento' => 'V-88776655',
            'telefono' => '0412-8889944',
            'email' => 'lramirez@policia.gob.ve',
            'cargo' => 'Comisario Jefe',
            'activo' => true,
            'institucion_id' => $policia->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ Policía creada con 2 departamentos');

        // ============================================
        // 8. DEFENSORÍA DEL PUEBLO - YARACUY
        // ============================================
        $defensoria = Institucion::create([
            'nombre' => 'Defensoría del Pueblo - Yaracuy',
            'informacion' => 'Defensa de los derechos humanos',
            'representante' => 'Defensor del Pueblo',
            'ubicacion' => 'San Felipe, Yaracuy',
            'activo' => true
        ]);

        // 8.1 Departamento de Atención al Ciudadano
        $atencion = Departamento::create([
            'nombre' => 'Atención al Ciudadano',
            'informacion' => 'Recepción de denuncias y quejas',
            'representante' => 'Jefe de Atención',
            'ubicacion' => 'Oficina Principal - Planta Baja',
            'activo' => true,
            'institucion_id' => $defensoria->id
        ]);

        // 8.2 Responsable institucional
        Responsable::create([
            'nombre' => 'Abg. Carlos Mendoza',
            'documento' => 'V-77665544',
            'telefono' => '0414-7778899',
            'email' => 'cmendoza@defensoria.gob.ve',
            'cargo' => 'Defensor del Pueblo',
            'activo' => true,
            'institucion_id' => $defensoria->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ Defensoría creada con 1 departamento');

        // ============================================
        // RESUMEN FINAL
        // ============================================
        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('✅ SEEDER COMPLETADO EXITOSAMENTE');
        $this->command->info('========================================');

        $totalInstituciones = Institucion::count();
        $totalDepartamentos = Departamento::count();
        $totalResponsables = Responsable::count();

        $this->command->table(
            ['Entidad', 'Cantidad'],
            [
                ['Instituciones', $totalInstituciones],
                ['Departamentos', $totalDepartamentos],
                ['Responsables', $totalResponsables],
            ]
        );

        $this->command->newLine();
        $this->command->info('📋 Instituciones creadas:');
        Institucion::pluck('nombre')->each(function($nombre) {
            $this->command->line("  • {$nombre}");
        });
    }
}
