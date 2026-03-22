<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SecurityAnswer;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    // Mostrar perfil del usuario
    public function index()
    {
        return view('profile.index');
    }

    // Actualizar información personal
    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

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

        return redirect()->route('profile.index')
            ->with('password_success', 'Contraseña actualizada correctamente.');
    }

    // Actualizar preguntas de seguridad
    public function updateSecurity(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'security_question_1' => 'required|string',
            'security_answer_1' => 'required|string',
            'security_question_2' => 'required|string',
            'security_answer_2' => 'required|string',
        ]);

        // Actualizar preguntas en users
        $user->security_question_1 = $request->security_question_1;
        $user->security_question_2 = $request->security_question_2;
        $user->save();

        // Actualizar o crear respuestas en security_answers
        // Respuesta 1
        SecurityAnswer::updateOrCreate(
            [
                'user_id' => $user->id,
                'question_number' => 1
            ],
            [
                'answer_hash' => Hash::make(strtolower(trim($request->security_answer_1)))
            ]
        );

        // Respuesta 2
        SecurityAnswer::updateOrCreate(
            [
                'user_id' => $user->id,
                'question_number' => 2
            ],
            [
                'answer_hash' => Hash::make(strtolower(trim($request->security_answer_2)))
            ]
        );

        return redirect()->route('profile.index')
            ->with('security_success', 'Preguntas de seguridad actualizadas correctamente.');
    }
}
