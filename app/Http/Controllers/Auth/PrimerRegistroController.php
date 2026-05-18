<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Trabajador;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PrimerRegistroController extends Controller
{
    public function showForm()
    {
        // Si ya hay usuarios, redirigir al login
        if (Usuario::count() > 0) {
            return redirect()->route('login');
        }

        $roles = Rol::all();
        return view('auth.primer-registro', compact('roles'));
    }

    public function register(Request $request)
    {
        // Validar que no existan usuarios
        if (Usuario::count() > 0) {
            abort(403, 'El sistema ya ha sido inicializado.');
        }

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
            'rol_id' => ['required', 'exists:roles,id'],
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

        // Crear usuario
        Usuario::create([
            'usuario' => $validated['usuario'],
            'password' => Hash::make($validated['password']),
            'must_change_password' => false, // Ya eligió su contraseña
            'status' => 'activo',
            'trabajador_id' => $trabajador->id,
            'rol_id' => $validated['rol_id'],
        ]);

        return redirect()->route('login')
            ->with('status', 'Sistema inicializado correctamente. Ya puede iniciar sesión.');
    }
}
