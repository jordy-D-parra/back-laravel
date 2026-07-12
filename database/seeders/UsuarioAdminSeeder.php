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
        $adminTrabajador = Trabajador::where('cedula', 'V-12345678')->first();
        $adminRol = Rol::where('nombre', 'admin')->first();

        Usuario::create([
            'usuario' => 'jordy',
            'password' => Hash::make('Mortadela1$'),
            'must_change_password' => false, // Forzará cambio en primer login
            'status' => 'activo',
            'trabajador_id' => $adminTrabajador->id,
            'rol_id' => $adminRol->id,
        ]);
    }
}
