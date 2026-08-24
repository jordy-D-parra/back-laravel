<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Si no hay usuarios, redirigir al registro inicial
        if (Usuario::count() === 0) {
            return redirect()->route('primer.registro');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $usuario = Auth::user();

            if ($usuario->status !== 'activo') {
                Auth::logout();
                throw ValidationException::withMessages([
                    'usuario' => 'Su cuenta está inactiva. Contacte al administrador.',
                ]);
            }

            $usuario->ultimo_login = now();
            $usuario->save();

            $request->session()->regenerate();

            if ($usuario->must_change_password) {
                return redirect()->route('password.change');
            }

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'usuario' => 'Las credenciales proporcionadas son incorrectas.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
