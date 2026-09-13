<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Trabajador;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PrimerRegistroController extends Controller
{
    public function showForm()
    {
        // Si ya hay usuarios, redirigir al login
        if (Usuario::count() > 0) {
            return redirect()->route('login');
        }

        // Ya no mostramos el selector de roles - será admin por defecto
        return view('auth.primer-registro');
    }

    public function register(Request $request)
    {
        // Validar que no existan usuarios
        if (Usuario::count() > 0) {
            abort(403, 'El sistema ya ha sido inicializado.');
        }

        // Buscar o crear el rol ADMIN
        $adminRol = Rol::firstOrCreate(
            ['nombre' => 'admin'],
            ['descripcion' => 'Administrador del sistema con acceso total']
        );

        // Asegurar que el rol admin tenga TODOS los permisos
        // Esto se ejecutará después de que los permisos existan
        // Lo haremos en un seeder aparte

        $validated = $request->validate([
            // Datos del trabajador
            'cedula' => ['required', 'string', 'max:20', 'unique:trabajadores,cedula'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'departamento' => ['required', 'string', 'max:100'],
            'cargo' => ['required', 'string', 'max:100'],
            'especialidad' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],

            // Datos del usuario
            'usuario' => ['required', 'string', 'max:50', 'unique:usuarios,usuario'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Crear trabajador
        $trabajador = Trabajador::create([
            'cedula' => $validated['cedula'],
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'departamento' => $validated['departamento'],
            'cargo' => $validated['cargo'],
            'especialidad' => $validated['especialidad'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
        ]);

        // Crear usuario con rol ADMIN (forzado)
        Usuario::create([
            'usuario' => $validated['usuario'],
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
            'status' => 'activo',
            'trabajador_id' => $trabajador->id,
            'rol_id' => $adminRol->id,  // Forzamos el rol admin
        ]);

        return redirect()->route('login')
            ->with('status', 'Sistema inicializado correctamente. Ya puede iniciar sesión como Administrador.');
    }
}
