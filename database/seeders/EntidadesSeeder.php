<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institucion;
use App\Models\Departamento;
use App\Models\Responsable;
use App\Models\Estado;
use App\Models\Municipio;
use App\Models\Parroquia;

class EntidadesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('========================================');
        $this->command->info('🌱 Seeder Masivo de Entidades');
        $this->command->info('========================================');

        $estadoYaracuy = Estado::where('nombre', 'Yaracuy')->first();

        if (!$estadoYaracuy) {
            $this->command->error('❌ Estado Yaracuy no encontrado. Ejecuta primero los seeders de ubicación.');
            return;
        }

        $municipios = Municipio::where('estado_id', $estadoYaracuy->id)->get();

        if ($municipios->isEmpty()) {
            $this->command->error('❌ Municipios no encontrados.');
            return;
        }

        $institucionesData = $this->getInstitucionesData();

        $totalInstituciones = 0;
        $totalDepartamentos = 0;
        $totalResponsables = 0;

        foreach ($institucionesData as $dataInst) {
            $municipio = $municipios->random();
            $parroquia = Parroquia::where('municipio_id', $municipio->id)->first();

            $institucion = Institucion::updateOrCreate(
                ['nombre' => $dataInst['institucion']['nombre']],
                [
                    'informacion' => $dataInst['institucion']['informacion'],
                    'representante' => $dataInst['institucion']['representante'],
                    'estado_id' => $estadoYaracuy->id,
                    'municipio_id' => $municipio->id,
                    'parroquia_id' => $parroquia?->id,
                    'activo' => true,
                ]
            );
            $totalInstituciones++;

            $this->command->info("🏢 [{$totalInstituciones}] {$institucion->nombre}");

            foreach ($dataInst['departamentos'] as $dataDepto) {
                $departamento = Departamento::updateOrCreate(
                    [
                        'nombre' => $dataDepto['nombre'],
                        'institucion_id' => $institucion->id,
                    ],
                    [
                        'informacion' => $dataDepto['informacion'],
                        'representante' => $dataDepto['responsable']['nombre'] ?? 'Sin asignar',
                        'ubicacion' => $dataDepto['ubicacion'] ?? null,
                        'activo' => true,
                    ]
                );
                $totalDepartamentos++;

                if (!empty($dataDepto['responsable'])) {
                    $respData = $dataDepto['responsable'];

                    Responsable::updateOrCreate(
                        ['documento' => $respData['documento']],
                        [
                            'nombre' => $respData['nombre'],
                            'telefono' => $respData['telefono'] ?? null,
                            'email' => $respData['email'] ?? null,
                            'cargo' => $respData['cargo'] ?? 'Responsable',
                            'direccion' => null,
                            'institucion_id' => $institucion->id,
                            'departamento_id' => $departamento->id,
                            'activo' => true,
                        ]
                    );
                    $totalResponsables++;
                }
            }
        }

        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('✅ SEEDER COMPLETADO');
        $this->command->info('========================================');
        $this->command->table(
            ['Entidad', 'Cantidad'],
            [
                ['Instituciones', $totalInstituciones],
                ['Departamentos', $totalDepartamentos],
                ['Responsables', $totalResponsables],
            ]
        );
        $this->command->info('📊 TOTALES EN BD:');
        $this->command->line('   • Instituciones: ' . Institucion::count());
        $this->command->line('   • Departamentos: ' . Departamento::count());
        $this->command->line('   • Responsables:  ' . Responsable::count());
    }

    private function getInstitucionesData(): array
    {
        return [
            // ============================================
            // 1. GOBERNACIÓN DEL ESTADO YARACUY
            // ============================================
            [
                'institucion' => [
                    'nombre' => 'Gobernación del Estado Yaracuy',
                    'informacion' => 'Ente gubernamental principal del Estado Yaracuy.',
                    'representante' => 'Gobernador del Estado',
                ],
                'departamentos' => [
                    ['nombre' => 'Despacho del Gobernador', 'informacion' => 'Oficina principal.', 'ubicacion' => 'Piso 6', 'responsable' => ['nombre' => 'Julio León Heredia', 'documento' => 'V-9876543', 'cargo' => 'Gobernador', 'telefono' => '0254-4441101', 'email' => 'gobernador@yaracuy.gob.ve']],
                    ['nombre' => 'Secretaría General', 'informacion' => 'Coordinación administrativa.', 'ubicacion' => 'Piso 5', 'responsable' => ['nombre' => 'María Fernanda Rojas', 'documento' => 'V-11223344', 'cargo' => 'Secretaria General', 'telefono' => '0254-4441102', 'email' => 'sgeneral@yaracuy.gob.ve']],
                    ['nombre' => 'Dirección de Informática', 'informacion' => 'Gestión tecnológica.', 'ubicacion' => 'Piso 3', 'responsable' => ['nombre' => 'Juan Carlos Pérez', 'documento' => 'V-12345678', 'cargo' => 'Director de Informática', 'telefono' => '0412-1234567', 'email' => 'informatica@yaracuy.gob.ve']],
                    ['nombre' => 'Recursos Humanos', 'informacion' => 'Gestión del personal.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Ana María González', 'documento' => 'V-87654321', 'cargo' => 'Directora de RRHH', 'telefono' => '0416-8765432', 'email' => 'rrhh@yaracuy.gob.ve']],
                    ['nombre' => 'Administración y Finanzas', 'informacion' => 'Gestión financiera.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Carlos Alberto Sánchez', 'documento' => 'V-13579246', 'cargo' => 'Director de Administración', 'telefono' => '0414-5556677', 'email' => 'finanzas@yaracuy.gob.ve']],
                    ['nombre' => 'Planificación y Presupuesto', 'informacion' => 'Planificación estratégica.', 'ubicacion' => 'Piso 4', 'responsable' => ['nombre' => 'Roberto José Méndez', 'documento' => 'V-99887766', 'cargo' => 'Director de Planificación', 'telefono' => '0414-7778899', 'email' => 'planificacion@yaracuy.gob.ve']],
                    ['nombre' => 'Servicios Generales', 'informacion' => 'Mantenimiento y logística.', 'ubicacion' => 'Planta Baja', 'responsable' => ['nombre' => 'Pedro Ramírez López', 'documento' => 'V-10223344', 'cargo' => 'Director de Servicios', 'telefono' => '0412-5556677', 'email' => 'servicios@yaracuy.gob.ve']],
                ],
            ],

            // ============================================
            // 2. ALCALDÍA SAN FELIPE
            // ============================================
            [
                'institucion' => ['nombre' => 'Alcaldía del Municipio San Felipe', 'informacion' => 'Gobierno municipal de San Felipe.', 'representante' => 'Alcalde'],
                'departamentos' => [
                    ['nombre' => 'Despacho del Alcalde', 'informacion' => 'Oficina principal.', 'ubicacion' => 'Piso 3', 'responsable' => ['nombre' => 'Rogger D´Orazio', 'documento' => 'V-11009988', 'cargo' => 'Alcalde', 'telefono' => '0254-4331101', 'email' => 'alcalde@sanfelipe.gob.ve']],
                    ['nombre' => 'Obras Públicas', 'informacion' => 'Ejecución de obras.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Alberto Díaz Contreras', 'documento' => 'V-22110099', 'cargo' => 'Director de Obras', 'telefono' => '0412-3334455', 'email' => 'obras@sanfelipe.gob.ve']],
                    ['nombre' => 'Hacienda Municipal', 'informacion' => 'Recaudación de tributos.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Elena Beatriz Suárez', 'documento' => 'V-55443322', 'cargo' => 'Directora de Hacienda', 'telefono' => '0424-7776655', 'email' => 'hacienda@sanfelipe.gob.ve']],
                    ['nombre' => 'Desarrollo Social', 'informacion' => 'Programas sociales.', 'ubicacion' => 'Planta Baja', 'responsable' => ['nombre' => 'Luisa Margarita Pérez', 'documento' => 'V-66778899', 'cargo' => 'Directora de Desarrollo Social', 'telefono' => '0414-2223344', 'email' => 'social@sanfelipe.gob.ve']],
                    ['nombre' => 'Cultura y Deporte', 'informacion' => 'Actividades culturales.', 'ubicacion' => 'Casa de la Cultura', 'responsable' => ['nombre' => 'Miguel Ángel Rivas', 'documento' => 'V-33221100', 'cargo' => 'Director de Cultura', 'telefono' => '0412-8889944', 'email' => 'cultura@sanfelipe.gob.ve']],
                ],
            ],

            // ============================================
            // 3. ALCALDÍA COCOROTE
            // ============================================
            [
                'institucion' => ['nombre' => 'Alcaldía del Municipio Cocorote', 'informacion' => 'Gobierno municipal de Cocorote.', 'representante' => 'Alcalde'],
                'departamentos' => [
                    ['nombre' => 'Despacho del Alcalde', 'informacion' => 'Oficina principal.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Pedro José Rodríguez', 'documento' => 'V-14001100', 'cargo' => 'Alcalde', 'telefono' => '0254-4141101', 'email' => 'alcalde@cocorote.gob.ve']],
                    ['nombre' => 'Obras Públicas', 'informacion' => 'Ejecución de obras.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Ing. Marcos Suárez', 'documento' => 'V-15002200', 'cargo' => 'Director de Obras', 'telefono' => '0414-1112233', 'email' => 'obras@cocorote.gob.ve']],
                    ['nombre' => 'Hacienda Municipal', 'informacion' => 'Tributos municipales.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Lcda. Ana Pérez', 'documento' => 'V-16003300', 'cargo' => 'Directora de Hacienda', 'telefono' => '0416-2223344', 'email' => 'hacienda@cocorote.gob.ve']],
                ],
            ],

            // ============================================
            // 4. ALCALDÍA BRUZUAL
            // ============================================
            [
                'institucion' => ['nombre' => 'Alcaldía del Municipio Bruzual', 'informacion' => 'Gobierno municipal de Bruzual.', 'representante' => 'Alcalde'],
                'departamentos' => [
                    ['nombre' => 'Despacho del Alcalde', 'informacion' => 'Oficina principal.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'María Elena Ríos', 'documento' => 'V-17004400', 'cargo' => 'Alcaldesa', 'telefono' => '0254-4141201', 'email' => 'alcaldesa@bruzual.gob.ve']],
                    ['nombre' => 'Obras Públicas', 'informacion' => 'Ejecución de obras.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Ing. Luis Cárdenas', 'documento' => 'V-18005500', 'cargo' => 'Director de Obras', 'telefono' => '0424-3334455', 'email' => 'obras@bruzual.gob.ve']],
                    ['nombre' => 'Hacienda Municipal', 'informacion' => 'Tributos municipales.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Lcdo. Carlos Mora', 'documento' => 'V-19006600', 'cargo' => 'Director de Hacienda', 'telefono' => '0412-4445566', 'email' => 'hacienda@bruzual.gob.ve']],
                    ['nombre' => 'Desarrollo Social', 'informacion' => 'Programas sociales.', 'ubicacion' => 'Planta Baja', 'responsable' => ['nombre' => 'Sra. Carmen Rojas', 'documento' => 'V-20007700', 'cargo' => 'Directora Social', 'telefono' => '0414-5556677', 'email' => 'social@bruzual.gob.ve']],
                ],
            ],

            // ============================================
            // 5. ALCALDÍA NIRGUA
            // ============================================
            [
                'institucion' => ['nombre' => 'Alcaldía del Municipio Nirgua', 'informacion' => 'Gobierno municipal de Nirgua.', 'representante' => 'Alcalde'],
                'departamentos' => [
                    ['nombre' => 'Despacho del Alcalde', 'informacion' => 'Oficina principal.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'José Gregorio Pérez', 'documento' => 'V-21008800', 'cargo' => 'Alcalde', 'telefono' => '0254-4141301', 'email' => 'alcalde@nirgua.gob.ve']],
                    ['nombre' => 'Obras Públicas', 'informacion' => 'Ejecución de obras.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Ing. Rafael Núñez', 'documento' => 'V-22009900', 'cargo' => 'Director de Obras', 'telefono' => '0416-6667788', 'email' => 'obras@nirgua.gob.ve']],
                    ['nombre' => 'Hacienda Municipal', 'informacion' => 'Tributos municipales.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Lcda. María Díaz', 'documento' => 'V-23001100', 'cargo' => 'Directora de Hacienda', 'telefono' => '0424-7778899', 'email' => 'hacienda@nirgua.gob.ve']],
                ],
            ],

            // ============================================
            // 6. ALCALDÍA VEROES
            // ============================================
            [
                'institucion' => ['nombre' => 'Alcaldía del Municipio Veroes', 'informacion' => 'Gobierno municipal de Veroes.', 'representante' => 'Alcalde'],
                'departamentos' => [
                    ['nombre' => 'Despacho del Alcalde', 'informacion' => 'Oficina principal.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Alberto José Lugo', 'documento' => 'V-24002200', 'cargo' => 'Alcalde', 'telefono' => '0254-4141401', 'email' => 'alcalde@veroes.gob.ve']],
                    ['nombre' => 'Obras Públicas', 'informacion' => 'Ejecución de obras.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Ing. Pedro Contreras', 'documento' => 'V-25003300', 'cargo' => 'Director de Obras', 'telefono' => '0414-8889955', 'email' => 'obras@veroes.gob.ve']],
                ],
            ],

            // ============================================
            // 7. ALCALDÍA SUCRE
            // ============================================
            [
                'institucion' => ['nombre' => 'Alcaldía del Municipio Sucre', 'informacion' => 'Gobierno municipal de Sucre.', 'representante' => 'Alcalde'],
                'departamentos' => [
                    ['nombre' => 'Despacho del Alcalde', 'informacion' => 'Oficina principal.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Luis Alfredo Martínez', 'documento' => 'V-26004400', 'cargo' => 'Alcalde', 'telefono' => '0254-4141501', 'email' => 'alcalde@sucre.gob.ve']],
                    ['nombre' => 'Obras Públicas', 'informacion' => 'Ejecución de obras.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Ing. Ricardo Peña', 'documento' => 'V-27005500', 'cargo' => 'Director de Obras', 'telefono' => '0416-1112233', 'email' => 'obras@sucre.gob.ve']],
                    ['nombre' => 'Hacienda Municipal', 'informacion' => 'Tributos municipales.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Lcdo. José Ramírez', 'documento' => 'V-28006600', 'cargo' => 'Director de Hacienda', 'telefono' => '0424-2223344', 'email' => 'hacienda@sucre.gob.ve']],
                ],
            ],

            // ============================================
            // 8. HOSPITAL CENTRAL DE SAN FELIPE
            // ============================================
            [
                'institucion' => ['nombre' => 'Hospital Central de San Felipe', 'informacion' => 'Centro de salud principal.', 'representante' => 'Director Médico'],
                'departamentos' => [
                    ['nombre' => 'Dirección Médica', 'informacion' => 'Dirección general.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Dr. Roberto Méndez Silva', 'documento' => 'V-29007700', 'cargo' => 'Director Médico', 'telefono' => '0412-9998877', 'email' => 'direccion@hospital.gob.ve']],
                    ['nombre' => 'Emergencia', 'informacion' => 'Atención 24h.', 'ubicacion' => 'Piso 0', 'responsable' => ['nombre' => 'Dr. Carlos Sánchez Ruiz', 'documento' => 'V-30008800', 'cargo' => 'Jefe de Emergencia', 'telefono' => '0414-5556677', 'email' => 'emergencia@hospital.gob.ve']],
                    ['nombre' => 'Cirugía', 'informacion' => 'Servicios quirúrgicos.', 'ubicacion' => 'Piso 3', 'responsable' => ['nombre' => 'Dra. Patricia Jiménez', 'documento' => 'V-31009900', 'cargo' => 'Jefa de Cirugía', 'telefono' => '0424-1112233', 'email' => 'cirugia@hospital.gob.ve']],
                    ['nombre' => 'Pediatría', 'informacion' => 'Atención infantil.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Dra. Sofía Ramírez', 'documento' => 'V-32001100', 'cargo' => 'Jefa de Pediatría', 'telefono' => '0416-6667788', 'email' => 'pediatria@hospital.gob.ve']],
                    ['nombre' => 'Farmacia', 'informacion' => 'Dispensación.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Lcda. María Rodríguez', 'documento' => 'V-33002200', 'cargo' => 'Jefa de Farmacia', 'telefono' => '0426-7778899', 'email' => 'farmacia@hospital.gob.ve']],
                    ['nombre' => 'Laboratorio', 'informacion' => 'Análisis clínicos.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Bio. Andrés Torres', 'documento' => 'V-34003300', 'cargo' => 'Jefe de Laboratorio', 'telefono' => '0414-8889955', 'email' => 'laboratorio@hospital.gob.ve']],
                ],
            ],

            // ============================================
            // 9. HOSPITAL PLÁCIDO DANIEL
            // ============================================
            [
                'institucion' => ['nombre' => 'Hospital Dr. Plácido Daniel Rodríguez Rivero', 'informacion' => 'Hospital de San Felipe.', 'representante' => 'Director'],
                'departamentos' => [
                    ['nombre' => 'Dirección', 'informacion' => 'Dirección general.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Dr. Francisco Pérez', 'documento' => 'V-35004400', 'cargo' => 'Director', 'telefono' => '0254-4442201', 'email' => 'direccion@placido.gob.ve']],
                    ['nombre' => 'Emergencia', 'informacion' => 'Atención 24h.', 'ubicacion' => 'Piso 0', 'responsable' => ['nombre' => 'Dr. Miguel Ángel Rojas', 'documento' => 'V-36005500', 'cargo' => 'Jefe de Emergencia', 'telefono' => '0412-3334455', 'email' => 'emergencia@placido.gob.ve']],
                    ['nombre' => 'Consultas Externas', 'informacion' => 'Consultas programadas.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Dra. Beatriz Luna', 'documento' => 'V-37006600', 'cargo' => 'Jefa de Consultas', 'telefono' => '0414-4445566', 'email' => 'consultas@placido.gob.ve']],
                ],
            ],

            // ============================================
            // 10. AMBULATORIO COCO FRÍO
            // ============================================
            [
                'institucion' => ['nombre' => 'Ambulatorio Urbano II Coco Frío', 'informacion' => 'Ambulatorio de atención primaria.', 'representante' => 'Director'],
                'departamentos' => [
                    ['nombre' => 'Dirección', 'informacion' => 'Dirección.', 'ubicacion' => 'Planta Baja', 'responsable' => ['nombre' => 'Dr. Pedro Rivas', 'documento' => 'V-38007700', 'cargo' => 'Director', 'telefono' => '0416-5556677', 'email' => 'direccion@cocofrio.gob.ve']],
                    ['nombre' => 'Consultas', 'informacion' => 'Consultas médicas.', 'ubicacion' => 'Planta Baja', 'responsable' => ['nombre' => 'Dra. Ana Gómez', 'documento' => 'V-39008800', 'cargo' => 'Jefa de Consultas', 'telefono' => '0424-6667788', 'email' => 'consultas@cocofrio.gob.ve']],
                ],
            ],

            // ============================================
            // 11. UNEY
            // ============================================
            [
                'institucion' => ['nombre' => 'Universidad Nacional Experimental de Yaracuy (UNEY)', 'informacion' => 'Institución de educación superior.', 'representante' => 'Rector'],
                'departamentos' => [
                    ['nombre' => 'Rectorado', 'informacion' => 'Dirección superior.', 'ubicacion' => 'Piso 3', 'responsable' => ['nombre' => 'Dra. Elena Torres Blanco', 'documento' => 'V-40009900', 'cargo' => 'Rectora', 'telefono' => '0414-7776644', 'email' => 'rectorado@uney.edu.ve']],
                    ['nombre' => 'Vicerrectorado Académico', 'informacion' => 'Coordinación académica.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Dr. Fernando Luna', 'documento' => 'V-41001100', 'cargo' => 'Vicerrector Académico', 'telefono' => '0424-5553322', 'email' => 'academico@uney.edu.ve']],
                    ['nombre' => 'Decanato de Ingeniería', 'informacion' => 'Carreras de ingeniería.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Ing. José Pereira', 'documento' => 'V-42002200', 'cargo' => 'Decano', 'telefono' => '0412-5553322', 'email' => 'ingenieria@uney.edu.ve']],
                    ['nombre' => 'Decanato de Ciencias Sociales', 'informacion' => 'Ciencias sociales.', 'ubicacion' => 'Edificio B', 'responsable' => ['nombre' => 'Lcdo. Rafael Moreno', 'documento' => 'V-43003300', 'cargo' => 'Decano', 'telefono' => '0416-6667788', 'email' => 'sociales@uney.edu.ve']],
                    ['nombre' => 'Departamento de Informática', 'informacion' => 'Soporte técnico.', 'ubicacion' => 'Piso 3', 'responsable' => ['nombre' => 'Ing. Daniela Ríos', 'documento' => 'V-44004400', 'cargo' => 'Jefa de Informática', 'telefono' => '0426-3334455', 'email' => 'informatica@uney.edu.ve']],
                ],
            ],

            // ============================================
            // 12. UPTYAR
            // ============================================
            [
                'institucion' => ['nombre' => 'Universidad Politécnica Territorial de Yaracuy Arístides Bastidas', 'informacion' => 'Institución universitaria.', 'representante' => 'Rector'],
                'departamentos' => [
                    ['nombre' => 'Rectorado', 'informacion' => 'Dirección.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Dr. Ramón Vásquez', 'documento' => 'V-45005500', 'cargo' => 'Rector', 'telefono' => '0254-4442301', 'email' => 'rectorado@uptya.edu.ve']],
                    ['nombre' => 'Académico', 'informacion' => 'Coordinación académica.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Dra. Yolanda Méndez', 'documento' => 'V-46006600', 'cargo' => 'Vicerrectora', 'telefono' => '0414-1112233', 'email' => 'academico@uptya.edu.ve']],
                    ['nombre' => 'Informática', 'informacion' => 'Sistemas.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Ing. Héctor Salas', 'documento' => 'V-47007700', 'cargo' => 'Jefe de Informática', 'telefono' => '0412-2223344', 'email' => 'informatica@uptya.edu.ve']],
                ],
            ],

            // ============================================
            // 13. LICEO ALBERTO RAVELL
            // ============================================
            [
                'institucion' => ['nombre' => 'Liceo Bolivariano Alberto Ravell', 'informacion' => 'Educación media.', 'representante' => 'Director'],
                'departamentos' => [
                    ['nombre' => 'Dirección', 'informacion' => 'Dirección.', 'ubicacion' => 'Planta Alta', 'responsable' => ['nombre' => 'Lcdo. José Miguel Castro', 'documento' => 'V-48008800', 'cargo' => 'Director', 'telefono' => '0414-3334455', 'email' => 'direccion@albertoravell.edu.ve']],
                    ['nombre' => 'Subdirección Académica', 'informacion' => 'Coordinación académica.', 'ubicacion' => 'Planta Alta', 'responsable' => ['nombre' => 'Lcda. Carmen Pérez', 'documento' => 'V-49009900', 'cargo' => 'Subdirectora', 'telefono' => '0416-4445566', 'email' => 'academica@albertoravell.edu.ve']],
                    ['nombre' => 'Coordinación de Evaluación', 'informacion' => 'Evaluación estudiantil.', 'ubicacion' => 'Planta Baja', 'responsable' => ['nombre' => 'Lcda. Marta Fernández', 'documento' => 'V-50001100', 'cargo' => 'Coordinadora', 'telefono' => '0424-5556677', 'email' => 'evaluacion@albertoravell.edu.ve']],
                ],
            ],

            // ============================================
            // 14. ESCUELA SIMÓN BOLÍVAR
            // ============================================
            [
                'institucion' => ['nombre' => 'Escuela Bolivariana Simón Bolívar', 'informacion' => 'Educación primaria.', 'representante' => 'Directora'],
                'departamentos' => [
                    ['nombre' => 'Dirección', 'informacion' => 'Dirección.', 'ubicacion' => 'Planta Alta', 'responsable' => ['nombre' => 'Lcda. Ana María Rodríguez', 'documento' => 'V-51002200', 'cargo' => 'Directora', 'telefono' => '0412-6667788', 'email' => 'direccion@simonbolivar.edu.ve']],
                    ['nombre' => 'Subdirección', 'informacion' => 'Subdirección.', 'ubicacion' => 'Planta Alta', 'responsable' => ['nombre' => 'Lcdo. Luis Pérez', 'documento' => 'V-52003300', 'cargo' => 'Subdirector', 'telefono' => '0414-7778899', 'email' => 'subdireccion@simonbolivar.edu.ve']],
                ],
            ],

            // ============================================
            // 15. LICEO YARACUY
            // ============================================
            [
                'institucion' => ['nombre' => 'Liceo Bolivariano Yaracuy', 'informacion' => 'Educación media.', 'representante' => 'Director'],
                'departamentos' => [
                    ['nombre' => 'Dirección', 'informacion' => 'Dirección.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Lcdo. Pedro Rangel', 'documento' => 'V-53004400', 'cargo' => 'Director', 'telefono' => '0424-8889900', 'email' => 'direccion@lbyaracuy.edu.ve']],
                    ['nombre' => 'Coordinación', 'informacion' => 'Coordinación académica.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Lcda. Rosa Bravo', 'documento' => 'V-54005500', 'cargo' => 'Coordinadora', 'telefono' => '0416-9990011', 'email' => 'coordinacion@lbyaracuy.edu.ve']],
                ],
            ],

            // ============================================
            // 16. POLICÍA DEL ESTADO YARACUY
            // ============================================
            [
                'institucion' => ['nombre' => 'Policía del Estado Yaracuy', 'informacion' => 'Cuerpo de seguridad ciudadana.', 'representante' => 'Comandante General'],
                'departamentos' => [
                    ['nombre' => 'Comandancia General', 'informacion' => 'Dirección superior.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Comisario Luis Ramírez', 'documento' => 'V-55006600', 'cargo' => 'Comandante General', 'telefono' => '0412-8889944', 'email' => 'comandancia@policia.gob.ve']],
                    ['nombre' => 'Seguridad Ciudadana', 'informacion' => 'Patrullaje.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Inspector Pedro Guerra', 'documento' => 'V-56007700', 'cargo' => 'Director', 'telefono' => '0414-4448899', 'email' => 'seguridad@policia.gob.ve']],
                    ['nombre' => 'Tránsito y Vialidad', 'informacion' => 'Control de tránsito.', 'ubicacion' => 'Comando Tránsito', 'responsable' => ['nombre' => 'Inspector Carlos Andrade', 'documento' => 'V-57008800', 'cargo' => 'Director', 'telefono' => '0424-2223344', 'email' => 'transito@policia.gob.ve']],
                    ['nombre' => 'Investigaciones', 'informacion' => 'Investigaciones criminales.', 'ubicacion' => 'Piso 3', 'responsable' => ['nombre' => 'Comisario María Fernández', 'documento' => 'V-58009900', 'cargo' => 'Directora', 'telefono' => '0412-7778899', 'email' => 'investigaciones@policia.gob.ve']],
                ],
            ],

            // ============================================
            // 17. BOMBEROS
            // ============================================
            [
                'institucion' => ['nombre' => 'Cuerpo de Bomberos del Estado Yaracuy', 'informacion' => 'Servicio de bomberos y emergencias.', 'representante' => 'Comandante'],
                'departamentos' => [
                    ['nombre' => 'Comandancia', 'informacion' => 'Dirección.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Cnel. José Rojas', 'documento' => 'V-59001100', 'cargo' => 'Comandante', 'telefono' => '0414-1112233', 'email' => 'comandancia@bomberos.gob.ve']],
                    ['nombre' => 'Operaciones', 'informacion' => 'Atención de emergencias.', 'ubicacion' => 'Planta Baja', 'responsable' => ['nombre' => 'Tte. Pedro Méndez', 'documento' => 'V-60002200', 'cargo' => 'Jefe de Operaciones', 'telefono' => '0416-2223344', 'email' => 'operaciones@bomberos.gob.ve']],
                ],
            ],

            // ============================================
            // 18. PROTECCIÓN CIVIL
            // ============================================
            [
                'institucion' => ['nombre' => 'Protección Civil Yaracuy', 'informacion' => 'Gestión de riesgos y desastres.', 'representante' => 'Director'],
                'departamentos' => [
                    ['nombre' => 'Dirección', 'informacion' => 'Dirección.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Ing. Luis García', 'documento' => 'V-61003300', 'cargo' => 'Director', 'telefono' => '0412-3334455', 'email' => 'direccion@proteccioncivil.gob.ve']],
                    ['nombre' => 'Operaciones', 'informacion' => 'Atención de emergencias.', 'ubicacion' => 'Planta Baja', 'responsable' => ['nombre' => 'TSU. María Pérez', 'documento' => 'V-62004400', 'cargo' => 'Jefa de Operaciones', 'telefono' => '0414-4445566', 'email' => 'operaciones@proteccioncivil.gob.ve']],
                ],
            ],

            // ============================================
            // 19. ESPY
            // ============================================
            [
                'institucion' => ['nombre' => 'Empresa de Servicios Públicos de Yaracuy (ESPY)', 'informacion' => 'Servicios públicos.', 'representante' => 'Presidente'],
                'departamentos' => [
                    ['nombre' => 'Presidencia', 'informacion' => 'Dirección.', 'ubicacion' => 'Piso 4', 'responsable' => ['nombre' => 'Ing. José Gregorio Roa', 'documento' => 'V-63005500', 'cargo' => 'Presidente', 'telefono' => '0412-1112233', 'email' => 'presidencia@espy.gob.ve']],
                    ['nombre' => 'Agua Potable', 'informacion' => 'Servicio de agua.', 'ubicacion' => 'Planta Tratamiento', 'responsable' => ['nombre' => 'Ing. María Contreras', 'documento' => 'V-64006600', 'cargo' => 'Gerente', 'telefono' => '0414-2223344', 'email' => 'agua@espy.gob.ve']],
                    ['nombre' => 'Electricidad', 'informacion' => 'Servicio eléctrico.', 'ubicacion' => 'Subestación', 'responsable' => ['nombre' => 'Ing. Pedro Luis Salas', 'documento' => 'V-65007700', 'cargo' => 'Gerente', 'telefono' => '0416-3334455', 'email' => 'electricidad@espy.gob.ve']],
                    ['nombre' => 'Aseo Urbano', 'informacion' => 'Recolección de desechos.', 'ubicacion' => 'Planta Baja', 'responsable' => ['nombre' => 'Sr. Antonio Reyes', 'documento' => 'V-66008800', 'cargo' => 'Gerente', 'telefono' => '0424-4445566', 'email' => 'aseo@espy.gob.ve']],
                    ['nombre' => 'Administración', 'informacion' => 'Gestión administrativa.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Lcda. Carmen Díaz', 'documento' => 'V-67009900', 'cargo' => 'Gerente', 'telefono' => '0412-5556677', 'email' => 'administracion@espy.gob.ve']],
                ],
            ],

            // ============================================
            // 20. IVT
            // ============================================
            [
                'institucion' => ['nombre' => 'Instituto de Vialidad y Transporte (IVT)', 'informacion' => 'Vialidad y transporte.', 'representante' => 'Presidente'],
                'departamentos' => [
                    ['nombre' => 'Presidencia', 'informacion' => 'Dirección.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Ing. Rafael Montilla', 'documento' => 'V-68001100', 'cargo' => 'Presidente', 'telefono' => '0412-9990011', 'email' => 'presidencia@ivt.gob.ve']],
                    ['nombre' => 'Vialidad', 'informacion' => 'Construcción de vías.', 'ubicacion' => 'Taller Central', 'responsable' => ['nombre' => 'Ing. Aura Bravo', 'documento' => 'V-69002200', 'cargo' => 'Directora', 'telefono' => '0414-1112233', 'email' => 'vialidad@ivt.gob.ve']],
                    ['nombre' => 'Transporte', 'informacion' => 'Regulación del transporte.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Lcdo. Gustavo Molina', 'documento' => 'V-70003300', 'cargo' => 'Director', 'telefono' => '0416-2223344', 'email' => 'transporte@ivt.gob.ve']],
                    ['nombre' => 'Ingeniería', 'informacion' => 'Proyectos viales.', 'ubicacion' => 'Piso 3', 'responsable' => ['nombre' => 'Ing. Xiomara Pérez', 'documento' => 'V-71004400', 'cargo' => 'Directora', 'telefono' => '0424-3334455', 'email' => 'ingenieria@ivt.gob.ve']],
                ],
            ],

            // ============================================
            // 21. CONSEJO LEGISLATIVO
            // ============================================
            [
                'institucion' => ['nombre' => 'Consejo Legislativo del Estado Yaracuy', 'informacion' => 'Órgano legislativo.', 'representante' => 'Presidente'],
                'departamentos' => [
                    ['nombre' => 'Presidencia del Consejo', 'informacion' => 'Dirección.', 'ubicacion' => 'Piso 3', 'responsable' => ['nombre' => 'Dip. Héctor Rodríguez', 'documento' => 'V-72005500', 'cargo' => 'Presidente', 'telefono' => '0254-4442201', 'email' => 'presidencia@cley.gob.ve']],
                    ['nombre' => 'Comisión de Hacienda', 'informacion' => 'Asuntos fiscales.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Dip. Beatriz Palacios', 'documento' => 'V-73006600', 'cargo' => 'Presidenta', 'telefono' => '0414-6667788', 'email' => 'hacienda@cley.gob.ve']],
                    ['nombre' => 'Comisión de Servicios Sociales', 'informacion' => 'Educación y salud.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Dip. Mauricio Sánchez', 'documento' => 'V-74007700', 'cargo' => 'Presidente', 'telefono' => '0416-7778899', 'email' => 'sociales@cley.gob.ve']],
                    ['nombre' => 'Secretaría Administrativa', 'informacion' => 'Gestión administrativa.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Lcdo. Daniel Rangel', 'documento' => 'V-75008800', 'cargo' => 'Secretario', 'telefono' => '0424-8889900', 'email' => 'administracion@cley.gob.ve']],
                ],
            ],

            // ============================================
            // 22. SANIDAD AGROPECUARIA
            // ============================================
            [
                'institucion' => ['nombre' => 'Servicio Autónomo de Sanidad Agropecuaria', 'informacion' => 'Sanidad agropecuaria.', 'representante' => 'Director'],
                'departamentos' => [
                    ['nombre' => 'Dirección', 'informacion' => 'Dirección.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Ing. Agr. Pedro Salas', 'documento' => 'V-76009900', 'cargo' => 'Director', 'telefono' => '0412-1112233', 'email' => 'direccion@sasa.gob.ve']],
                    ['nombre' => 'Sanidad Vegetal', 'informacion' => 'Control fitosanitario.', 'ubicacion' => 'Planta Baja', 'responsable' => ['nombre' => 'Ing. Agr. María Ríos', 'documento' => 'V-77001100', 'cargo' => 'Jefa', 'telefono' => '0414-2223344', 'email' => 'vegetal@sasa.gob.ve']],
                    ['nombre' => 'Sanidad Animal', 'informacion' => 'Control zoosanitario.', 'ubicacion' => 'Planta Baja', 'responsable' => ['nombre' => 'MV. Carlos Ruiz', 'documento' => 'V-78002200', 'cargo' => 'Jefe', 'telefono' => '0416-3334455', 'email' => 'animal@sasa.gob.ve']],
                ],
            ],

            // ============================================
            // 23. INSTITUTO DE CULTURA
            // ============================================
            [
                'institucion' => ['nombre' => 'Instituto de Cultura del Estado Yaracuy', 'informacion' => 'Promoción cultural.', 'representante' => 'Presidente'],
                'departamentos' => [
                    ['nombre' => 'Presidencia', 'informacion' => 'Dirección.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Lcdo. Antonio Pérez', 'documento' => 'V-79003300', 'cargo' => 'Presidente', 'telefono' => '0254-4442401', 'email' => 'presidencia@cultura.gob.ve']],
                    ['nombre' => 'Artes Escénicas', 'informacion' => 'Teatro y danza.', 'ubicacion' => 'Planta Baja', 'responsable' => ['nombre' => 'Lcda. Rosa Mendoza', 'documento' => 'V-80004400', 'cargo' => 'Directora', 'telefono' => '0414-4445566', 'email' => 'escenicas@cultura.gob.ve']],
                    ['nombre' => 'Patrimonio Cultural', 'informacion' => 'Conservación patrimonial.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Arq. Luis Castro', 'documento' => 'V-81005500', 'cargo' => 'Director', 'telefono' => '0416-5556677', 'email' => 'patrimonio@cultura.gob.ve']],
                ],
            ],

            // ============================================
            // 24. INTI YARACUY
            // ============================================
            [
                'institucion' => ['nombre' => 'Instituto Nacional de Tierras - Yaracuy', 'informacion' => 'Gestión de tierras.', 'representante' => 'Director Regional'],
                'departamentos' => [
                    ['nombre' => 'Dirección Regional', 'informacion' => 'Dirección.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Ing. Agr. José Lugo', 'documento' => 'V-82006600', 'cargo' => 'Director Regional', 'telefono' => '0254-4442501', 'email' => 'direccion@intiyaracuy.gob.ve']],
                    ['nombre' => 'Catastro', 'informacion' => 'Registro de tierras.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'TSU. María Pérez', 'documento' => 'V-83007700', 'cargo' => 'Jefa de Catastro', 'telefono' => '0414-6667788', 'email' => 'catastro@intiyaracuy.gob.ve']],
                ],
            ],

            // ============================================
            // 25. INASS YARACUY
            // ============================================
            [
                'institucion' => ['nombre' => 'INASS - Yaracuy', 'informacion' => 'Seguridad social.', 'representante' => 'Director Regional'],
                'departamentos' => [
                    ['nombre' => 'Dirección Regional', 'informacion' => 'Dirección.', 'ubicacion' => 'Piso 3', 'responsable' => ['nombre' => 'Lcdo. Pedro Márquez', 'documento' => 'V-84008800', 'cargo' => 'Director', 'telefono' => '0254-4442601', 'email' => 'direccion@inass.gob.ve']],
                    ['nombre' => 'Atención al Afiliado', 'informacion' => 'Atención al público.', 'ubicacion' => 'Piso 1', 'responsable' => ['nombre' => 'Sra. Ana Pérez', 'documento' => 'V-85009900', 'cargo' => 'Jefa de Atención', 'telefono' => '0416-7778899', 'email' => 'afiliados@inass.gob.ve']],
                ],
            ],

            // ============================================
            // 26. CANTV YARACUY
            // ============================================
            [
                'institucion' => ['nombre' => 'CANTV Yaracuy', 'informacion' => 'Servicios de telecomunicaciones.', 'representante' => 'Gerente Regional'],
                'departamentos' => [
                    ['nombre' => 'Gerencia Regional', 'informacion' => 'Dirección.', 'ubicacion' => 'Piso 2', 'responsable' => ['nombre' => 'Ing. Luis Rivas', 'documento' => 'V-86001100', 'cargo' => 'Gerente Regional', 'telefono' => '0254-4442701', 'email' => 'gerencia@cantv.com.ve']],
                    ['nombre' => 'Atención al Cliente', 'informacion' => 'Servicio al cliente.', 'ubicacion' => 'Planta Baja', 'responsable' => ['nombre' => 'Lcda. Rosa Silva', 'documento' => 'V-87002200', 'cargo' => 'Jefa de Atención', 'telefono' => '0414-8889955', 'email' => 'atencion@cantv.com.ve']],
                ],
            ],

            
        ];
                // ============================================
        // 9. INSTITUTO NACIONAL DE TIERRA (INTI) - YARACUY
        // ============================================
        $inti = Institucion::create([
            'nombre' => 'Instituto Nacional de Tierras - Yaracuy',
            'informacion' => 'Regularización y administración de tierras',
            'representante' => 'Director Regional',
            'estado_id' => $estadoYaracuy->id,
            'municipio_id' => $municipioSanFelipe->id,
            'parroquia_id' => $parroquiaSanFelipe->id,
            'activo' => true
        ]);

        $atencionTierra = Departamento::create([
            'nombre' => 'Atención al Ciudadano',
            'informacion' => 'Atención y orientación sobre trámites de tierras',
            'representante' => 'Jefe de Atención',
            'ubicacion' => 'Sede Regional - Planta Baja',
            'activo' => true,
            'institucion_id' => $inti->id
        ]);

        $catastro = Departamento::create([
            'nombre' => 'Catastro y Registro',
            'informacion' => 'Registro y catastro de tierras',
            'representante' => 'Jefe de Catastro',
            'ubicacion' => 'Sede Regional - Piso 2',
            'activo' => true,
            'institucion_id' => $inti->id
        ]);

        Responsable::create([
            'nombre' => 'Ing. Marcos Rivero',
            'documento' => 'V-10112233',
            'telefono' => '0412-6667788',
            'email' => 'mrivero@inti.gob.ve',
            'cargo' => 'Director Regional',
            'activo' => true,
            'institucion_id' => $inti->id,
            'departamento_id' => null
        ]);

        Responsable::create([
            'nombre' => 'Lic. Yaneth Colmenares',
            'documento' => 'V-11223344',
            'telefono' => '0414-5556677',
            'email' => 'ycolmenares@inti.gob.ve',
            'cargo' => 'Jefa de Catastro',
            'activo' => true,
            'institucion_id' => $inti->id,
            'departamento_id' => $catastro->id
        ]);

        $this->command->info('✅ INTI creada con 2 departamentos');

        // ============================================
        // 10. INSTITUTO VENEZOLANO DE LOS SEGUROS SOCIALES (IVSS) - YARACUY
        // ============================================
        $ivss = Institucion::create([
            'nombre' => 'IVSS - Yaracuy',
            'informacion' => 'Instituto Venezolano de los Seguros Sociales',
            'representante' => 'Director Regional',
            'estado_id' => $estadoYaracuy->id,
            'municipio_id' => $municipioSanFelipe->id,
            'parroquia_id' => $parroquiaSanFelipe->id,
            'activo' => true
        ]);

        $consultaIvss = Departamento::create([
            'nombre' => 'Consulta Externa',
            'informacion' => 'Atención médica ambulatoria',
            'representante' => 'Coordinador Médico',
            'ubicacion' => 'Ambulatorio Principal',
            'activo' => true,
            'institucion_id' => $ivss->id
        ]);

        $administracionIvss = Departamento::create([
            'nombre' => 'Administración',
            'informacion' => 'Departamento administrativo del IVSS',
            'representante' => 'Jefe de Administración',
            'ubicacion' => 'Oficina Administrativa - Piso 1',
            'activo' => true,
            'institucion_id' => $ivss->id
        ]);

        Responsable::create([
            'nombre' => 'Dr. Franklin Uzcátegui',
            'documento' => 'V-10304050',
            'telefono' => '0416-2223344',
            'email' => 'fuzcategui@ivss.gob.ve',
            'cargo' => 'Director Regional',
            'activo' => true,
            'institucion_id' => $ivss->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ IVSS creado con 2 departamentos');

        // ============================================
        // 11. CONSEJO NACIONAL ELECTORAL (CNE) - YARACUY
        // ============================================
        $cne = Institucion::create([
            'nombre' => 'Consejo Nacional Electoral - Yaracuy',
            'informacion' => 'Oficina Regional del Poder Electoral',
            'representante' => 'Directora Regional',
            'estado_id' => $estadoYaracuy->id,
            'municipio_id' => $municipioSanFelipe->id,
            'parroquia_id' => $parroquiaSanFelipe->id,
            'activo' => true
        ]);

        $registroCivil = Departamento::create([
            'nombre' => 'Registro Civil',
            'informacion' => 'Registro civil y electoral',
            'representante' => 'Jefe de Registro',
            'ubicacion' => 'Sede Regional - Planta Baja',
            'activo' => true,
            'institucion_id' => $cne->id
        ]);

        $informaticaCne = Departamento::create([
            'nombre' => 'Informática y Soporte',
            'informacion' => 'Soporte técnico de los equipos electorales',
            'representante' => 'Jefe de Informática',
            'ubicacion' => 'Sede Regional - Piso 2',
            'activo' => true,
            'institucion_id' => $cne->id
        ]);

        Responsable::create([
            'nombre' => 'Lic. Belkis Rangel',
            'documento' => 'V-12001200',
            'telefono' => '0412-4445566',
            'email' => 'brangel@cne.gob.ve',
            'cargo' => 'Directora Regional',
            'activo' => true,
            'institucion_id' => $cne->id,
            'departamento_id' => null
        ]);

        Responsable::create([
            'nombre' => 'Ing. Wilmer Peraza',
            'documento' => 'V-13554466',
            'telefono' => '0414-8889900',
            'email' => 'wperaza@cne.gob.ve',
            'cargo' => 'Jefe de Informática',
            'activo' => true,
            'institucion_id' => $cne->id,
            'departamento_id' => $informaticaCne->id
        ]);

        $this->command->info('✅ CNE creado con 2 departamentos');

        // ============================================
        // 12. INSTITUTO NACIONAL DE NUTRICIÓN (INN) - YARACUY
        // ============================================
        $inn = Institucion::create([
            'nombre' => 'Instituto Nacional de Nutrición - Yaracuy',
            'informacion' => 'Programas de alimentación y nutrición',
            'representante' => 'Coordinador Regional',
            'estado_id' => $estadoYaracuy->id,
            'municipio_id' => $municipioSanFelipe->id,
            'parroquia_id' => $parroquiaSanFelipe->id,
            'activo' => true
        ]);

        $casasAlimentacion = Departamento::create([
            'nombre' => 'Casas de Alimentación',
            'informacion' => 'Coordinación de casas de alimentación',
            'representante' => 'Coordinador de Casas',
            'ubicacion' => 'Sede Regional',
            'activo' => true,
            'institucion_id' => $inn->id
        ]);

        $nutricion = Departamento::create([
            'nombre' => 'Nutrición y Dietética',
            'informacion' => 'Evaluación y asesoría nutricional',
            'representante' => 'Nutricionista Jefe',
            'ubicacion' => 'Consultorio Nutricional',
            'activo' => true,
            'institucion_id' => $inn->id
        ]);

        Responsable::create([
            'nombre' => 'Lic. Zulay Mendoza',
            'documento' => 'V-14445566',
            'telefono' => '0416-3334455',
            'email' => 'zmendoza@inn.gob.ve',
            'cargo' => 'Coordinadora Regional',
            'activo' => true,
            'institucion_id' => $inn->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ INN creado con 2 departamentos');

        // ============================================
        // 13. INSTITUTO NACIONAL DE LA MUJER (INAMUJER) - YARACUY
        // ============================================
        $inamujer = Institucion::create([
            'nombre' => 'Inamujer - Yaracuy',
            'informacion' => 'Instituto Nacional de la Mujer',
            'representante' => 'Coordinadora Regional',
            'estado_id' => $estadoYaracuy->id,
            'municipio_id' => $municipioSanFelipe->id,
            'parroquia_id' => $parroquiaSanFelipe->id,
            'activo' => true
        ]);

        $defensoriaMujer = Departamento::create([
            'nombre' => 'Defensoría de la Mujer',
            'informacion' => 'Atención legal y psicológica a mujeres',
            'representante' => 'Defensora',
            'ubicacion' => 'Sede Regional - Planta Baja',
            'activo' => true,
            'institucion_id' => $inamujer->id
        ]);

        $formacionMujer = Departamento::create([
            'nombre' => 'Formación y Capacitación',
            'informacion' => 'Programas de formación para mujeres',
            'representante' => 'Coordinadora de Formación',
            'ubicacion' => 'Sede Regional - Piso 1',
            'activo' => true,
            'institucion_id' => $inamujer->id
        ]);

        Responsable::create([
            'nombre' => 'Abg. Marisol Cordero',
            'documento' => 'V-15556677',
            'telefono' => '0412-9998877',
            'email' => 'mcordero@inamujer.gob.ve',
            'cargo' => 'Coordinadora Regional',
            'activo' => true,
            'institucion_id' => $inamujer->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ Inamujer creado con 2 departamentos');

        // ============================================
        // 14. INSTITUTO NACIONAL DE CAPACITACIÓN Y EDUCACIÓN SOCIALISTA (INCES) - YARACUY
        // ============================================
        $inces = Institucion::create([
            'nombre' => 'INCES - Yaracuy',
            'informacion' => 'Formación técnica y capacitación laboral',
            'representante' => 'Gerente Regional',
            'estado_id' => $estadoYaracuy->id,
            'municipio_id' => $municipioSanFelipe->id,
            'parroquia_id' => $parroquiaSanFelipe->id,
            'activo' => true
        ]);

        $formacionInces = Departamento::create([
            'nombre' => 'Formación Profesional',
            'informacion' => 'Cursos y talleres de formación técnica',
            'representante' => 'Coordinador de Formación',
            'ubicacion' => 'Centro de Formación - Piso 1',
            'activo' => true,
            'institucion_id' => $inces->id
        ]);

        $administracionInces = Departamento::create([
            'nombre' => 'Administración',
            'informacion' => 'Departamento administrativo',
            'representante' => 'Jefe de Administración',
            'ubicacion' => 'Sede Regional - Piso 2',
            'activo' => true,
            'institucion_id' => $inces->id
        ]);

        Responsable::create([
            'nombre' => 'Lic. José Rojas',
            'documento' => 'V-16667788',
            'telefono' => '0414-1112233',
            'email' => 'jrojas@inces.gob.ve',
            'cargo' => 'Gerente Regional',
            'activo' => true,
            'institucion_id' => $inces->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ INCES creado con 2 departamentos');

        // ============================================
        // 15. INSTITUTO NACIONAL DE ESTADÍSTICA (INE) - YARACUY
        // ============================================
        $ine = Institucion::create([
            'nombre' => 'Instituto Nacional de Estadística - Yaracuy',
            'informacion' => 'Recolección y procesamiento de datos estadísticos',
            'representante' => 'Coordinador Regional',
            'estado_id' => $estadoYaracuy->id,
            'municipio_id' => $municipioSanFelipe->id,
            'parroquia_id' => $parroquiaSanFelipe->id,
            'activo' => true
        ]);

        $censos = Departamento::create([
            'nombre' => 'Censos y Encuestas',
            'informacion' => 'Operativos censales y encuestas',
            'representante' => 'Coordinador de Censos',
            'ubicacion' => 'Sede Regional - Piso 1',
            'activo' => true,
            'institucion_id' => $ine->id
        ]);

        $procesamientoDatos = Departamento::create([
            'nombre' => 'Procesamiento de Datos',
            'informacion' => 'Análisis y procesamiento de información',
            'representante' => 'Jefe de Procesamiento',
            'ubicacion' => 'Sala de Servidores - Piso 2',
            'activo' => true,
            'institucion_id' => $ine->id
        ]);

        Responsable::create([
            'nombre' => 'Est. Pedro Aponte',
            'documento' => 'V-17778899',
            'telefono' => '0416-7778899',
            'email' => 'paponte@ine.gob.ve',
            'cargo' => 'Coordinador Regional',
            'activo' => true,
            'institucion_id' => $ine->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ INE creado con 2 departamentos');

        // ============================================
        // 16. INSTITUTO NACIONAL DE PARQUES (INPARQUES) - YARACUY
        // ============================================
        $inparques = Institucion::create([
            'nombre' => 'Inparques - Yaracuy',
            'informacion' => 'Administración de parques y monumentos naturales',
            'representante' => 'Jefe Regional',
            'estado_id' => $estadoYaracuy->id,
            'municipio_id' => $municipioSanFelipe->id,
            'parroquia_id' => $parroquiaSanFelipe->id,
            'activo' => true
        ]);

        $guarderia = Departamento::create([
            'nombre' => 'Guardería Ambiental',
            'informacion' => 'Vigilancia y protección de parques',
            'representante' => 'Jefe de Guardería',
            'ubicacion' => 'Parque Nacional Yurubí',
            'activo' => true,
            'institucion_id' => $inparques->id
        ]);

        $recreacion = Departamento::create([
            'nombre' => 'Recreación y Turismo',
            'informacion' => 'Programas recreativos y turísticos',
            'representante' => 'Coordinador de Recreación',
            'ubicacion' => 'Sede Regional',
            'activo' => true,
            'institucion_id' => $inparques->id
        ]);

        Responsable::create([
            'nombre' => 'TSU. Luis Bravo',
            'documento' => 'V-18889900',
            'telefono' => '0412-2223344',
            'email' => 'lbravo@inparques.gob.ve',
            'cargo' => 'Jefe Regional',
            'activo' => true,
            'institucion_id' => $inparques->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ Inparques creado con 2 departamentos');

        // ============================================
        // 17. INSTITUTO NACIONAL DE TRANSPORTE TERRESTRE (INTT) - YARACUY
        // ============================================
        $intt = Institucion::create([
            'nombre' => 'INTT - Yaracuy',
            'informacion' => 'Instituto Nacional de Transporte Terrestre',
            'representante' => 'Jefe Regional',
            'estado_id' => $estadoYaracuy->id,
            'municipio_id' => $municipioSanFelipe->id,
            'parroquia_id' => $parroquiaSanFelipe->id,
            'activo' => true
        ]);

        $licencias = Departamento::create([
            'nombre' => 'Licencias y Certificados',
            'informacion' => 'Emisión de licencias de conducir',
            'representante' => 'Jefe de Licencias',
            'ubicacion' => 'Sede Regional - Planta Baja',
            'activo' => true,
            'institucion_id' => $intt->id
        ]);

        $fiscalizacion = Departamento::create([
            'nombre' => 'Fiscalización y Control',
            'informacion' => 'Fiscalización del transporte terrestre',
            'representante' => 'Jefe de Fiscalización',
            'ubicacion' => 'Sede Regional - Piso 1',
            'activo' => true,
            'institucion_id' => $intt->id
        ]);

        Responsable::create([
            'nombre' => 'Ing. Ramón Gutiérrez',
            'documento' => 'V-19990011',
            'telefono' => '0414-3334455',
            'email' => 'rgutierrez@intt.gob.ve',
            'cargo' => 'Jefe Regional',
            'activo' => true,
            'institucion_id' => $intt->id,
            'departamento_id' => null
        ]);

        $this->command->info('✅ INTT creado con 2 departamentos');

        // ============================================
        // 18. SERVICIO NACIONAL INTEGRADO DE ADMINISTRACIÓN ADUANERA Y TRIBUTARIA (SENIAT) - YARACUY
        // ============================================
        $seniat = Institucion::create([
            'nombre' => 'SENIAT - Yaracuy',
            'informacion' => 'Administración aduanera y tributaria',
            'representante' => 'Gerente Regional',
            'estado_id' => $estadoYaracuy->id,
            'municipio_id' => $municipioSanFelipe->id,
            'parroquia_id' => $parroquiaSanFelipe->id,
            'activo' => true
        ]);

        $tributos = Departamento::create([
            'nombre' => 'Tributos Internos',
            'informacion' => 'Recaudación y fiscalización de tributos',
            'representante' => 'Gerente de Tributos',
            'ubicacion' => 'Sede Regional - Piso 1',
            'activo' => true,
            'institucion_id' => $seniat->id
        ]);

        $aduanas = Departamento::create([
            'nombre' => 'Aduanas',
            'informacion' => 'Control aduanero regional',
            'representante' => 'Gerente de Aduanas',
            'ubicacion' => 'Sede Regional - Piso 2',
            'activo' => true,
            'institucion_id' => $seniat->id
        ]);

        $informaticaSeniat = Departamento::create([
            'nombre' => 'Informática',
            'informacion' => 'Soporte tecnológico del SENIAT',
            'representante' => 'Jefe de Informática',
            'ubicacion' => 'Sala de Sistemas - Piso 3',
            'activo' => true,
            'institucion_id' => $seniat->id
        ]);

        Responsable::create([
            'nombre' => 'Lic. Carmen Nieves',
            'documento' => 'V-20001122',
            'telefono' => '0416-4445566',
            'email' => 'cnieves@seniat.gob.ve',
            'cargo' => 'Gerente Regional',
            'activo' => true,
            'institucion_id' => $seniat->id,
            'departamento_id' => null
        ]);

        Responsable::create([
            'nombre' => 'Ing. Alexander Pérez',
            'documento' => 'V-21002233',
            'telefono' => '0412-5556677',
            'email' => 'aperez@seniat.gob.ve',
            'cargo' => 'Jefe de Informática',
            'activo' => true,
            'institucion_id' => $seniat->id,
            'departamento_id' => $informaticaSeniat->id
        ]);

        $this->command->info('✅ SENIAT creado con 3 departamentos');
    }

}