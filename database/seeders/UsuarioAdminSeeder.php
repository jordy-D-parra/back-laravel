<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Trabajador;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👤 Creando usuarios del sistema...');

        // ============================================
        // 1. VERIFICAR/CREAR ROLES
        // ============================================
        $roles = [
            'admin' => Rol::firstOrCreate(
                ['nombre' => 'admin'],
                ['descripcion' => 'Administrador del sistema con acceso total']
            ),
            'ingeniero' => Rol::firstOrCreate(
                ['nombre' => 'ingeniero'],
                ['descripcion' => 'Ingeniero del departamento - Control total sobre inventario y equipos']
            ),
            'tecnico' => Rol::firstOrCreate(
                ['nombre' => 'tecnico'],
                ['descripcion' => 'Técnico de soporte - Puede crear y editar activos, pero no eliminar']
            ),
            'secretaria' => Rol::firstOrCreate(
                ['nombre' => 'secretaria'],
                ['descripcion' => 'Secretaria del departamento - Solo lectura en inventario, puede gestionar trabajadores']
            ),
        ];

        // ============================================
        // 2. DEFINICIÓN DE USUARIOS
        // ============================================
        // Cada usuario se asocia a un trabajador por CÉDULA
        $usuariosData = [
            // ========== 1-2: ADMINISTRADORES PRINCIPALES ==========
            [
                'cedula_trabajador' => 'V-12345678',
                'usuario' => 'jordy',
                'password' => 'Mortadela1$',
                'rol' => 'admin',
                'status' => 'activo',
                'must_change_password' => false,
            ],
            [
                'cedula_trabajador' => 'V-30776710',
                'usuario' => 'melo',
                'password' => 'Melo2004$',
                'rol' => 'admin',
                'status' => 'activo',
                'must_change_password' => false,
            ],

            // ========== 3: INGENIERO ==========
            [
                'cedula_trabajador' => 'V-17890123',
                'usuario' => 'mgonzalez',
                'password' => 'Ingeniero2024$',
                'rol' => 'ingeniero',
                'status' => 'activo',
                'must_change_password' => false,
            ],

            // ========== 4-6: TÉCNICOS ==========
            [
                'cedula_trabajador' => 'V-15456789',
                'usuario' => 'crodriguez',
                'password' => 'Tecnico2024$',
                'rol' => 'tecnico',
                'status' => 'activo',
                'must_change_password' => false,
            ],
            [
                'cedula_trabajador' => 'V-20123456',
                'usuario' => 'lperez',
                'password' => 'Tecnico2024$',
                'rol' => 'tecnico',
                'status' => 'activo',
                'must_change_password' => false,
            ],
            [
                'cedula_trabajador' => 'V-22334455',
                'usuario' => 'amartinez',
                'password' => 'Tecnico2024$',
                'rol' => 'tecnico',
                'status' => 'activo',
                'must_change_password' => false,
            ],

            // ========== 7-8: SECRETARIAS ==========
            [
                'cedula_trabajador' => 'V-17890123', // ⚠️ Ya existe, pero usaremos otro trabajador
                'usuario' => 'pmendoza',
                'password' => 'Secretaria2024$',
                'rol' => 'secretaria',
                'status' => 'activo',
                'must_change_password' => false,
            ],
            [
                'cedula_trabajador' => 'V-15987654',
                'usuario' => 'vsilva',
                'password' => 'Secretaria2024$',
                'rol' => 'secretaria',
                'status' => 'activo',
                'must_change_password' => false,
            ],

            // ========== 9-12: MÁS USUARIOS (roles variados) ==========
            [
                'cedula_trabajador' => 'V-18987654',
                'usuario' => 'pramirez',
                'password' => 'Usuario2024$',
                'rol' => 'ingeniero',
                'status' => 'activo',
                'must_change_password' => false,
            ],
            [
                'cedula_trabajador' => 'V-13765432',
                'usuario' => 'jfernandez',
                'password' => 'Usuario2024$',
                'rol' => 'ingeniero',
                'status' => 'activo',
                'must_change_password' => false,
            ],
            [
                'cedula_trabajador' => 'V-16543210',
                'usuario' => 'ldiaz',
                'password' => 'Usuario2024$',
                'rol' => 'secretaria',
                'status' => 'activo',
                'must_change_password' => false,
            ],
            [
                'cedula_trabajador' => 'V-12345098',
                'usuario' => 'jmendez',
                'password' => 'Usuario2024$',
                'rol' => 'tecnico',
                'status' => 'activo',
                'must_change_password' => false,
            ],

            // ========== 13: USUARIO INACTIVO (para pruebas) ==========
            [
                'cedula_trabajador' => 'V-19012345',
                'usuario' => 'srojas',
                'password' => 'Usuario2024$',
                'rol' => 'secretaria',
                'status' => 'inactivo',
                'must_change_password' => false,
            ],

            // ========== 14: USUARIO CON CAMBIO DE CONTRASEÑA PENDIENTE ==========
            [
                'cedula_trabajador' => 'V-14456789',
                'usuario' => 'mhernandez',
                'password' => 'Temporal2024$',
                'rol' => 'tecnico',
                'status' => 'activo',
                'must_change_password' => true, // ← Se le pedirá cambiarla al primer login
            ],

            // ========== 15: NUNCA HA INICIADO SESIÓN ==========
            [
                'cedula_trabajador' => 'V-20123456', // ⚠️ Ya usado, buscar otro abajo
                'usuario' => 'avargas',
                'password' => 'Usuario2024$',
                'rol' => 'tecnico',
                'status' => 'activo',
                'must_change_password' => false,
            ],
        ];

        // ============================================
        // 3. CREAR USUARIOS
        // ============================================
        $creados = 0;
        $actualizados = 0;
        $errores = 0;

        foreach ($usuariosData as $data) {
            try {
                // Buscar trabajador por cédula
                $trabajador = Trabajador::where('cedula', $data['cedula_trabajador'])->first();

                if (!$trabajador) {
                    $this->command->warn("⚠️ Trabajador con cédula {$data['cedula_trabajador']} no encontrado. Omitiendo usuario '{$data['usuario']}'.");
                    $errores++;
                    continue;
                }

                // Verificar si el trabajador ya tiene un usuario
                $usuarioExistente = Usuario::where('trabajador_id', $trabajador->id)->first();

                if ($usuarioExistente) {
                    // Actualizar el existente
                    $usuarioExistente->update([
                        'usuario' => $data['usuario'],
                        'password' => Hash::make($data['password']),
                        'rol_id' => $roles[$data['rol']]->id,
                        'status' => $data['status'],
                        'must_change_password' => $data['must_change_password'],
                    ]);
                    $actualizados++;
                    continue;
                }

                // Crear nuevo usuario
                Usuario::create([
                    'usuario' => $data['usuario'],
                    'password' => Hash::make($data['password']),
                    'must_change_password' => $data['must_change_password'],
                    'status' => $data['status'],
                    'trabajador_id' => $trabajador->id,
                    'rol_id' => $roles[$data['rol']]->id,
                ]);
                $creados++;

            } catch (\Exception $e) {
                $this->command->error("❌ Error al crear usuario '{$data['usuario']}': " . $e->getMessage());
                $errores++;
            }
        }

        // ============================================
        // 4. RESUMEN
        // ============================================
        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('✅ SEEDER DE USUARIOS COMPLETADO');
        $this->command->info('========================================');
        $this->command->info("   • Nuevos usuarios creados: {$creados}");
        $this->command->info("   • Usuarios actualizados: {$actualizados}");
        $this->command->info("   • Errores/omitidos: {$errores}");
        $this->command->info('   • Total en sistema: ' . Usuario::count());
        $this->command->newLine();

        // Tabla de usuarios creados
        $this->command->table(
            ['Usuario', 'Nombre Completo', 'Email', 'Rol', 'Status'],
            Usuario::with(['trabajador', 'rol'])
                ->orderBy('id')
                ->get()
                ->map(fn($u) => [
                    $u->usuario,
                    ($u->trabajador->nombre ?? '') . ' ' . ($u->trabajador->apellido ?? ''),
                    $u->trabajador->email ?? 'Sin email',
                    ucfirst($u->rol->nombre ?? 'Sin rol'),
                    $u->status,
                ])
                ->toArray()
        );

        // Credenciales de prueba
        $this->command->newLine();
        $this->command->info('🔑 CREDENCIALES DE PRUEBA:');
        $this->command->line('   ─────────────────────────────────────');
        $this->command->line('   ADMIN:      jordy / Mortadela1$');
        $this->command->line('   ADMIN:      melo / Melo2004$');
        $this->command->line('   INGENIERO:  mgonzalez / Ingeniero2024$');
        $this->command->line('   TÉCNICO:    crodriguez / Tecnico2024$');
        $this->command->line('   TÉCNICO:    lperez / Tecnico2024$');
        $this->command->line('   TÉCNICO:    amartinez / Tecnico2024$');
        $this->command->line('   SECRETARIA: pmendoza / Secretaria2024$');
        $this->command->line('   SECRETARIA: vsilva / Secretaria2024$');
        $this->command->line('   ─────────────────────────────────────');

        // Resumen por rol
        $this->command->newLine();
        $this->command->info('📊 USUARIOS POR ROL:');
        foreach ($roles as $nombre => $rol) {
            $count = Usuario::where('rol_id', $rol->id)->count();
            $this->command->line("   • " . ucfirst($nombre) . ": {$count}");
        }
    }
}