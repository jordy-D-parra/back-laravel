<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected AuditoriaService $auditoriaService;

    public function __construct(AuditoriaService $auditoriaService)
    {
        $this->auditoriaService = $auditoriaService;
    }

    public function showLoginForm()
    {
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
                $this->auditoriaService->registrarEvento(
                    'login_fallido',
                    'auth',
                    'Intento de inicio de sesión de usuario inactivo: ' . $usuario->usuario
                );
                throw ValidationException::withMessages([
                    'usuario' => 'Su cuenta está inactiva. Contacte al administrador.',
                ]);
            }

            // Registrar login exitoso
            $this->auditoriaService->registrarEvento(
                'login',
                'auth',
                'Inicio de sesión exitoso: ' . $usuario->usuario
            );

            $usuario->ultimo_login = now();
            $usuario->save();

            $request->session()->regenerate();

            if ($usuario->must_change_password) {
                return redirect()->route('password.change');
            }

            return redirect()->intended(route('dashboard'));
        }

        // Registrar login fallido
        $usuarioInput = $request->input('usuario');
        $this->auditoriaService->registrarEvento(
            'login_fallido',
            'auth',
            'Intento de inicio de sesión fallido para usuario: ' . $usuarioInput
        );

        throw ValidationException::withMessages([
            'usuario' => 'Las credenciales proporcionadas son incorrectas.',
        ]);
    }

    public function logout(Request $request)
    {
        $usuario = Auth::user();
        if ($usuario) {
            $this->auditoriaService->registrarEvento(
                'logout',
                'auth',
                'Cierre de sesión de: ' . $usuario->usuario
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}