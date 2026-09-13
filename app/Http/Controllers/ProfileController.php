<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    // Mostrar perfil del usuario
    public function index()
    {
        // Verificar si el usuario tiene rol asignado
        $user = Auth::user();
        
        // Si no tiene rol, mostrar advertencia y opción de solicitar rol
        if (!$user->rol) {
            session()->flash('role_warning', 'No tienes un rol asignado. Por favor, contacta al administrador.');
        }
        
        return view('profile.index'); // Asegúrate que tu vista esté en resources/views/profile.blade.php
    }

    // Actualizar información personal
    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'cargo' => 'nullable|string|max:255',
        ]);

        // Actualizar solo los campos que existen en tu modelo
        $user->nombre = $request->nombre;
        $user->apellido = $request->apellido;
        $user->departamento = $request->departamento;
        $user->cargo = $request->cargo;
        $user->save();

        Log::info('Perfil actualizado', ['user_id' => $user->id, 'cedula' => $user->cedula]);

        return redirect()->route('profile.index')
            ->with('profile_success', 'Información actualizada correctamente.');
    }

    // Cambiar contraseña
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->route('profile.index')
                ->with('password_error', 'La contraseña actual es incorrecta.');
        }

        // Actualizar contraseña
        $user->password = Hash::make($request->new_password);
        $user->save();

        Log::info('Contraseña actualizada', ['user_id' => $user->id, 'cedula' => $user->cedula]);

        return redirect()->route('profile.index')
            ->with('password_success', 'Contraseña actualizada correctamente.');
    }

    // Actualizar preguntas de seguridad
    public function updateSecurity(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'pregunta_seguridad_1' => 'required|string',
            'respuesta_1' => 'required|string',
            'pregunta_seguridad_2' => 'required|string',
            'respuesta_2' => 'required|string',
        ]);

        // Actualizar preguntas y respuestas directamente en la tabla usuario
        $user->pregunta_seguridad_1 = $request->pregunta_seguridad_1;
        $user->respuesta_1 = Hash::make(strtolower(trim($request->respuesta_1)));
        $user->pregunta_seguridad_2 = $request->pregunta_seguridad_2;
        $user->respuesta_2 = Hash::make(strtolower(trim($request->respuesta_2)));
        $user->save();

        Log::info('Preguntas de seguridad actualizadas', ['user_id' => $user->id]);

        return redirect()->route('profile.index')
            ->with('security_success', 'Preguntas de seguridad actualizadas correctamente.');
    }
}