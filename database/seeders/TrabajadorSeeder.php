<?php

namespace Database\Seeders;

use App\Models\Trabajador;
use Illuminate\Database\Seeder;

class TrabajadorSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👥 Creando 20 trabajadores de demostración...');

        $trabajadores = [
            // ============ 1. ADMINISTRADOR PRINCIPAL ============
            [
                'cedula' => 'V-12345678',
                'nombre' => 'Administrador',
                'apellido' => 'Sistema',
                'departamento' => 'Informática',
                'cargo' => 'Jefe de Departamento',
                'especialidad' => 'Gestión de sistemas y redes',
                'telefono' => '0412-1234567',
                'email' => 'admin@gobernacion.gob.ve',
            ],

            // ============ 2. INGENIERO DE SISTEMAS ============
            [
                'cedula' => 'V-30776710',
                'nombre' => 'Yorhan',
                'apellido' => 'Melo',
                'departamento' => 'Informática',
                'cargo' => 'Ingeniero de Sistemas',
                'especialidad' => 'Desarrollo y soporte técnico',
                'telefono' => '0416-7654321',
                'email' => 'yorhanjose2004@gmail.com',
            ],

            // ============ 3. TÉCNICO DE SOPORTE ============
            [
                'cedula' => 'V-15456789',
                'nombre' => 'Carlos',
                'apellido' => 'Rodríguez',
                'departamento' => 'Informática',
                'cargo' => 'Técnico de Soporte',
                'especialidad' => 'Mantenimiento de hardware',
                'telefono' => '0414-5556677',
                'email' => 'crodriguez@gobernacion.gob.ve',
            ],

            // ============ 4. TÉCNICO DE REDES ============
            [
                'cedula' => 'V-17890123',
                'nombre' => 'María',
                'apellido' => 'González',
                'departamento' => 'Informática',
                'cargo' => 'Técnico de Redes',
                'especialidad' => 'Configuración de redes y servidores',
                'telefono' => '0424-9876543',
                'email' => 'mgonzalez@gobernacion.gob.ve',
            ],

            // ============ 5. DESARROLLADOR ============
            [
                'cedula' => 'V-20123456',
                'nombre' => 'Luis',
                'apellido' => 'Pérez',
                'departamento' => 'Informática',
                'cargo' => 'Desarrollador Full Stack',
                'especialidad' => 'Desarrollo web y bases de datos',
                'telefono' => '0412-3334455',
                'email' => 'lperez@gobernacion.gob.ve',
            ],

            // ============ 6. ANALISTA DE SISTEMAS ============
            [
                'cedula' => 'V-22334455',
                'nombre' => 'Ana',
                'apellido' => 'Martínez',
                'departamento' => 'Informática',
                'cargo' => 'Analista de Sistemas',
                'especialidad' => 'Análisis y documentación',
                'telefono' => '0416-1112233',
                'email' => 'amartinez@gobernacion.gob.ve',
            ],

            // ============ 7. RECURSOS HUMANOS ============
            [
                'cedula' => 'V-18987654',
                'nombre' => 'Pedro',
                'apellido' => 'Ramírez',
                'departamento' => 'Recursos Humanos',
                'cargo' => 'Director de RRHH',
                'especialidad' => 'Gestión de talento humano',
                'telefono' => '0414-7778899',
                'email' => 'pramirez@gobernacion.gob.ve',
            ],

            // ============ 8. ANALISTA DE RRHH ============
            [
                'cedula' => 'V-15678901',
                'nombre' => 'Carmen',
                'apellido' => 'Torres',
                'departamento' => 'Recursos Humanos',
                'cargo' => 'Analista de Personal',
                'especialidad' => 'Nómina y prestaciones',
                'telefono' => '0426-5556677',
                'email' => 'ctorres@gobernacion.gob.ve',
            ],

            // ============ 9. FINANZAS ============
            [
                'cedula' => 'V-13765432',
                'nombre' => 'José',
                'apellido' => 'Fernández',
                'departamento' => 'Finanzas',
                'cargo' => 'Director de Finanzas',
                'especialidad' => 'Administración financiera',
                'telefono' => '0412-8889900',
                'email' => 'jfernandez@gobernacion.gob.ve',
            ],

            // ============ 10. CONTADOR ============
            [
                'cedula' => 'V-19543210',
                'nombre' => 'Elena',
                'apellido' => 'Sánchez',
                'departamento' => 'Finanzas',
                'cargo' => 'Contador Público',
                'especialidad' => 'Contabilidad general',
                'telefono' => '0414-2223344',
                'email' => 'esanchez@gobernacion.gob.ve',
            ],

            // ============ 11. PRESUPUESTO ============
            [
                'cedula' => 'V-21111222',
                'nombre' => 'Roberto',
                'apellido' => 'Castro',
                'departamento' => 'Finanzas',
                'cargo' => 'Analista de Presupuesto',
                'especialidad' => 'Control presupuestario',
                'telefono' => '0416-4445566',
                'email' => 'rcastro@gobernacion.gob.ve',
            ],

            // ============ 12. ADMINISTRACIÓN ============
            [
                'cedula' => 'V-16543210',
                'nombre' => 'Laura',
                'apellido' => 'Díaz',
                'departamento' => 'Administración',
                'cargo' => 'Director Administrativo',
                'especialidad' => 'Gestión administrativa',
                'telefono' => '0412-6667788',
                'email' => 'ldiaz@gobernacion.gob.ve',
            ],

            // ============ 13. SECRETARIA ============
            [
                'cedula' => 'V-17890123',
                'nombre' => 'Patricia',
                'apellido' => 'Mendoza',
                'departamento' => 'Administración',
                'cargo' => 'Secretaria Ejecutiva',
                'especialidad' => 'Atención al público',
                'telefono' => '0414-3334455',
                'email' => 'pmendoza@gobernacion.gob.ve',
            ],

            // ============ 14. LOGÍSTICA ============
            [
                'cedula' => 'V-14456789',
                'nombre' => 'Miguel',
                'apellido' => 'Hernández',
                'departamento' => 'Logística',
                'cargo' => 'Coordinador de Logística',
                'especialidad' => 'Distribución y almacén',
                'telefono' => '0424-1112233',
                'email' => 'mhernandez@gobernacion.gob.ve',
            ],

            // ============ 15. ALMACÉN ============
            [
                'cedula' => 'V-19012345',
                'nombre' => 'Sofía',
                'apellido' => 'Rojas',
                'departamento' => 'Logística',
                'cargo' => 'Encargada de Almacén',
                'especialidad' => 'Control de inventario físico',
                'telefono' => '0412-9998877',
                'email' => 'srojas@gobernacion.gob.ve',
            ],

            // ============ 16. MANTENIMIENTO ============
            [
                'cedula' => 'V-12345098',
                'nombre' => 'Jorge',
                'apellido' => 'Méndez',
                'departamento' => 'Mantenimiento',
                'cargo' => 'Jefe de Mantenimiento',
                'especialidad' => 'Mantenimiento preventivo y correctivo',
                'telefono' => '0416-5556677',
                'email' => 'jmendez@gobernacion.gob.ve',
            ],

            // ============ 17. ELECTRICISTA ============
            [
                'cedula' => 'V-20123456',
                'nombre' => 'Alberto',
                'apellido' => 'Vargas',
                'departamento' => 'Mantenimiento',
                'cargo' => 'Electricista',
                'especialidad' => 'Instalaciones eléctricas',
                'telefono' => '0414-4443322',
                'email' => 'avargas@gobernacion.gob.ve',
            ],

            // ============ 18. PLOMERO ============
            [
                'cedula' => 'V-17654321',
                'nombre' => 'Ricardo',
                'apellido' => 'Ortiz',
                'departamento' => 'Mantenimiento',
                'cargo' => 'Plomero',
                'especialidad' => 'Instalaciones sanitarias',
                'telefono' => '0426-7778899',
                'email' => 'rortiz@gobernacion.gob.ve',
            ],

            // ============ 19. ATENCIÓN AL CIUDADANO ============
            [
                'cedula' => 'V-15987654',
                'nombre' => 'Valentina',
                'apellido' => 'Silva',
                'departamento' => 'Atención al Ciudadano',
                'cargo' => 'Coordinadora de Atención',
                'especialidad' => 'Servicio al ciudadano',
                'telefono' => '0412-2223344',
                'email' => 'vsilva@gobernacion.gob.ve',
            ],

            // ============ 20. ORIENTADOR ============
            [
                'cedula' => 'V-21345678',
                'nombre' => 'Daniel',
                'apellido' => 'Rivas',
                'departamento' => 'Atención al Ciudadano',
                'cargo' => 'Orientador',
                'especialidad' => 'Información y trámites',
                'telefono' => '0416-8889900',
                'email' => 'drivas@gobernacion.gob.ve',
            ],
        ];

        $creados = 0;
        $existentes = 0;

        foreach ($trabajadores as $data) {
            $trabajador = Trabajador::firstOrCreate(
                ['cedula' => $data['cedula']],
                $data
            );

            if ($trabajador->wasRecentlyCreated) {
                $creados++;
            } else {
                $existentes++;
            }
        }

        // ============================================
        // RESUMEN
        // ============================================
        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('✅ SEEDER DE TRABAJADORES COMPLETADO');
        $this->command->info('========================================');
        $this->command->info("   • Nuevos trabajadores creados: {$creados}");
        $this->command->info("   • Ya existentes (omitidos): {$existentes}");
        $this->command->info('   • Total en sistema: ' . Trabajador::count());
        $this->command->newLine();

        // Mostrar tabla de trabajadores
        $this->command->table(
            ['Cédula', 'Nombre Completo', 'Departamento', 'Cargo'],
            Trabajador::orderBy('id')
                ->get()
                ->map(fn($t) => [
                    $t->cedula,
                    $t->nombre . ' ' . $t->apellido,
                    $t->departamento,
                    $t->cargo,
                ])
                ->toArray()
        );

        // Departamentos únicos
        $departamentos = Trabajador::distinct()
            ->pluck('departamento')
            ->filter()
            ->sort()
            ->values();

        $this->command->newLine();
        $this->command->info('📁 Departamentos con trabajadores:');
        foreach ($departamentos as $depto) {
            $count = Trabajador::where('departamento', $depto)->count();
            $this->command->line("   • {$depto}: {$count} trabajador(es)");
        }
    }
}