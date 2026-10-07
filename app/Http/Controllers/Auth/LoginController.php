<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        // ============================================================
        // LOG DE DEPURACIÓN
        // ============================================================
        Log::info('=== INTENTO DE LOGIN ===', [
            'usuario' => $credentials['usuario'],
            'password_length' => strlen($credentials['password']),
            'ip' => $request->ip(),
        ]);

        // ============================================================
        // VERIFICAR QUE EL USUARIO EXISTA
        // ============================================================
        $usuario = Usuario::where('usuario', $credentials['usuario'])->first();

        if (!$usuario) {
            Log::warning('❌ Usuario NO encontrado', [
                'usuario' => $credentials['usuario'],
            ]);

            $this->auditoriaService->registrarEvento(
                'login_fallido',
                'auth',
                'Intento de inicio de sesión con usuario inexistente: ' . $credentials['usuario']
            );

            throw ValidationException::withMessages([
                'usuario' => 'Las credenciales proporcionadas son incorrectas.',
            ]);
        }

        // ============================================================
        // VERIFICAR CONTRASEÑA MANUALMENTE (para debug)
        // ============================================================
        $passwordValida = Hash::check($credentials['password'], $usuario->password);

        Log::info('🔐 Verificación de contraseña', [
            'usuario' => $usuario->usuario,
            'password_valida' => $passwordValida,
            'hash_guardado_inicia_con' => substr($usuario->password, 0, 7),
            'hash_guardado_longitud' => strlen($usuario->password),
        ]);

        if (!$passwordValida) {
            Log::warning('❌ Contraseña incorrecta', [
                'usuario' => $usuario->usuario,
            ]);

            $this->auditoriaService->registrarEvento(
                'login_fallido',
                'auth',
                'Contraseña incorrecta para usuario: ' . $credentials['usuario']
            );

            throw ValidationException::withMessages([
                'usuario' => 'Las credenciales proporcionadas son incorrectas.',
            ]);
        }

        // ============================================================
        // VERIFICAR QUE LA CUENTA ESTÉ ACTIVA
        // ============================================================
        if ($usuario->status !== 'activo') {
            Log::warning('❌ Usuario inactivo', [
                'usuario' => $usuario->usuario,
                'status' => $usuario->status,
            ]);

            $this->auditoriaService->registrarEvento(
                'login_fallido',
                'auth',
                'Intento de inicio de sesión de usuario inactivo: ' . $usuario->usuario
            );

            throw ValidationException::withMessages([
                'usuario' => 'Su cuenta está inactiva. Contacte al administrador.',
            ]);
        }

        // ============================================================
        // LOGIN EXITOSO — Iniciar sesión manualmente
        // ============================================================
        Auth::login($usuario, $request->boolean('remember'));
        $request->session()->regenerate();

        Log::info('✅ LOGIN EXITOSO', [
            'usuario' => $usuario->usuario,
            'usuario_id' => $usuario->id,
            'rol' => $usuario->rol?->nombre,
        ]);

        $this->auditoriaService->registrarEvento(
            'login',
            'auth',
            'Inicio de sesión exitoso: ' . $usuario->usuario
        );

        $usuario->ultimo_login = now();
        $usuario->save();

        // Redirección según estado
        if ($usuario->must_change_password) {
            return redirect()->route('password.change');
        }

        return redirect()->intended(route('dashboard'));
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
        $request->session()->flush();

        return redirect()->route('login')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => 'Fri, 01 Jan 1990 00:00:00 GMT',
            ]);
    }
}