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
        // ============================================
        // 1. TRABAJADOR ADMIN (jordy)
        // ============================================
        $adminTrabajador = Trabajador::firstOrCreate(
            ['cedula' => 'V-12345678'],
            [
                'nombre' => 'Administrador',
                'apellido' => 'Sistema',
                'departamento' => 'Informática',
                'cargo' => 'Jefe de Departamento',
                'especialidad' => 'Gestión de sistemas y redes',
                'telefono' => '0412-1234567',
                'email' => 'admin@gobernacion.gob.ve',
            ]
        );

        // ============================================
        // 2. TRABAJADOR MELO (NUEVO)
        // ============================================
        $meloTrabajador = Trabajador::firstOrCreate(
            ['cedula' => 'V-30776710'],
            [
                'nombre' => 'Yorhan',
                'apellido' => 'Melo',
                'departamento' => 'Informática',
                'cargo' => 'Ingeniero de Sistemas',
                'especialidad' => 'Desarrollo y soporte técnico',
                'telefono' => '0416-7654321',
                'email' => 'yorhanjose2004@gmail.com',
            ]
        );

        // ============================================
        // 3. OBTENER ROL ADMIN
        // ============================================
        $adminRol = Rol::where('nombre', 'admin')->first();

        // ============================================
        // 4. CREAR USUARIO ADMIN (jordy) - SIN EMAIL
        // ============================================
        Usuario::updateOrCreate(
            ['usuario' => 'jordy'],
            [
                // ❌ ELIMINADO: 'email' => 'jordy@gobernacion.gob.ve',
                'password' => Hash::make('Mortadela1$'),
                'must_change_password' => false,
                'status' => 'activo',
                'trabajador_id' => $adminTrabajador->id,
                'rol_id' => $adminRol->id,
            ]
        );

        // ============================================
        // 5. CREAR USUARIO MELO - SIN EMAIL
        // ============================================
        Usuario::updateOrCreate(
            ['usuario' => 'melo'],
            [
                // ❌ ELIMINADO: 'email' => 'yorhanjose2004@gmail.com',
                'password' => Hash::make('Melo2004$'),
                'must_change_password' => false,
                'status' => 'activo',
                'trabajador_id' => $meloTrabajador->id,
                'rol_id' => $adminRol->id,
            ]
        );

        // ============================================
        // 6. MENSAJE DE CONFIRMACIÓN
        // ============================================
        $this->command->info('✅ Usuarios creados (sin email en tabla usuarios):');
        $this->command->info('   - jordy / Mortadela1$ (Admin)');
        $this->command->info('   - melo / Melo2004$ (Admin)');
        $this->command->info('');
        $this->command->info('📧 Los emails se toman del trabajador asociado:');
        $this->command->info('   - jordy: ' . $adminTrabajador->email);
        $this->command->info('   - melo: ' . $meloTrabajador->email);
        $this->command->info('');
        $this->command->info('🔑 Ambos usuarios pueden iniciar sesión directamente.');
    }
}