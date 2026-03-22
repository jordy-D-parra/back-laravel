<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\SecurityAnswer;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Mostrar formulario de login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Procesar el login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            // Registrar actividad de login
            ActivityHelper::log('login', 'Usuario inició sesión');

            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    // Mostrar formulario de registro
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // LOG 1: Ver todos los datos que llegan
        Log::info('===== INICIO REGISTRO =====');
        Log::info('Datos completos:', $request->all());

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6|confirmed',
                'security_question_1' => 'required|string',
                'security_answer_1' => 'required|string',
                'security_question_2' => 'required|string',
                'security_answer_2' => 'required|string',
            ]);

            Log::info('Validación pasada correctamente');

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'security_question_1' => $request->security_question_1,
                'security_question_2' => $request->security_question_2,
                'role' => User::count() === 0 ? 'super_admin' : 'user',
                'is_admin' => User::count() === 0 ? true : false,
            ]);

            Log::info('Usuario creado ID: ' . $user->id);
            Log::info('Pregunta 1: ' . $user->security_question_1);
            Log::info('Pregunta 2: ' . $user->security_question_2);

            // Guardar respuestas
            SecurityAnswer::create([
                'user_id' => $user->id,
                'question_number' => 1,
                'answer_hash' => Hash::make(strtolower(trim($request->security_answer_1))),
            ]);

            SecurityAnswer::create([
                'user_id' => $user->id,
                'question_number' => 2,
                'answer_hash' => Hash::make(strtolower(trim($request->security_answer_2))),
            ]);

            Log::info('Respuestas guardadas correctamente');

            // Registrar actividad de registro
            ActivityHelper::log('register', 'Nuevo usuario registrado', null, [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]);

            Auth::login($user);

            Log::info('===== REGISTRO EXITOSO =====');

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            Log::error('ERROR EN REGISTRO: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->withErrors(['error' => 'Error al registrar: ' . $e->getMessage()]);
        }
    }

    // Cerrar sesión
    public function logout(Request $request)
    {
        // Registrar actividad de logout
        ActivityHelper::log('logout', 'Usuario cerró sesión');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // Dashboard después del login
    public function dashboard()
    {
        return view('dashboard');
    }

    // Mostrar formulario para recuperar contraseña (wizard)
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // ========== MÉTODOS PARA RECUPERACIÓN CON PREGUNTAS (AJAX) ==========

    // Verificar email para recuperación (AJAX)
    public function verifyEmailForRecovery(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        if (!$user->security_question_1 || !$user->security_question_2) {
            return response()->json([
                'success' => false,
                'message' => 'Este usuario no tiene preguntas de seguridad configuradas.'
            ]);
        }

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'question1' => $user->security_question_1,
            'question2' => $user->security_question_2
        ]);
    }

    // Verificar respuestas (AJAX)
    public function verifyAnswers(Request $request)
    {
        Log::info('===== VERIFY ANSWERS DEBUG =====');
        Log::info('User ID: ' . $request->user_id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'answer_1' => 'required|string',
            'answer_2' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);

        $answers = SecurityAnswer::where('user_id', $user->id)->get();

        $answer1 = strtolower(trim($request->answer_1));
        $answer2 = strtolower(trim($request->answer_2));

        $valid1 = Hash::check($answer1, $answers->where('question_number', 1)->first()->answer_hash);
        $valid2 = Hash::check($answer2, $answers->where('question_number', 2)->first()->answer_hash);

        Log::info('Answer 1 valid: ' . ($valid1 ? 'YES' : 'NO'));
        Log::info('Answer 2 valid: ' . ($valid2 ? 'YES' : 'NO'));

        if ($valid1 && $valid2) {
            // Guardar el ID del usuario en sesión para el siguiente paso
            session(['reset_user_id' => $user->id]);
            Log::info('reset_user_id saved to session: ' . session('reset_user_id'));

            // Registrar intento de recuperación exitoso
            ActivityHelper::log('password_recovery', 'Usuario verificó preguntas de seguridad correctamente', null, ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'user_id' => $user->id
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Respuestas incorrectas. Intenta nuevamente.'
        ]);
    }

    // Mostrar formulario para nueva contraseña (después de verificar respuestas)
    public function showResetPasswordForm()
    {
        if (!session('reset_user_id')) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password-questions');
    }

    // Restablecer contraseña por preguntas
    public function resetPasswordByQuestions(Request $request)
    {
        // DEPURACIÓN: Registrar todo
        Log::info('===== RESET PASSWORD DEBUG =====');
        Log::info('Request data:', $request->all());
        Log::info('Session reset_user_id: ' . session('reset_user_id'));
        Log::info('Session all: ', session()->all());

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $userId = session('reset_user_id');

        if (!$userId) {
            Log::error('No reset_user_id in session');
            return redirect()->route('password.request')
                ->withErrors(['error' => 'Sesión expirada. Por favor, inicia el proceso nuevamente.']);
        }

        Log::info('User ID found: ' . $userId);

        $user = User::findOrFail($userId);
        Log::info('User found: ' . $user->email);

        // Registrar cambio de contraseña por recuperación
        ActivityHelper::log('password_reset', 'Usuario restableció su contraseña mediante preguntas de seguridad', null, ['user_id' => $user->id]);

        $user->password = Hash::make($request->password);
        $user->save();

        Log::info('Password updated successfully');

        // Limpiar la sesión
        session()->forget('reset_user_id');

        return redirect()->route('login')->with('status', '✅ Contraseña actualizada correctamente. Inicia sesión con tu nueva contraseña.');
    }

    // ========== MÉTODOS PARA ADMINISTRADOR ==========

    // Mostrar panel de administración
    public function adminUsers()
    {
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos de administrador.');
        }

        $users = User::all();
        return view('admin.users', compact('users'));
    }

    // Admin cambiar contraseña
    public function adminResetPassword(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_password' => 'required|min:6',
        ]);

        $user = User::find($request->user_id);
        $oldPasswordHash = $user->password;

        $user->password = Hash::make($request->new_password);
        $user->save();

        // Registrar cambio de contraseña por admin
        ActivityHelper::log('change_password', "Admin cambió contraseña del usuario {$user->name}", null, ['user_id' => $user->id]);

        return back()->with('success', "Contraseña de {$user->name} actualizada correctamente.");
    }

    /**
 * Cambiar rol de un usuario
 */
public function changeUserRole(Request $request, $id)
{
    try {
        // Verificar que el usuario actual es Super Admin
        if (!auth()->user()->isSuperAdmin()) {
            Log::warning('Intento de cambio de rol sin permisos', [
                'user_id' => auth()->id(),
                'target_user_id' => $id
            ]);
            return redirect()->route('admin.users')->with('error', 'No tienes permisos para realizar esta acción.');
        }

        $user = User::findOrFail($id);

        // No permitir cambiar rol del Super Admin principal
        if ($user->isSuperAdmin() && $user->id !== auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'No puedes cambiar el rol de otro Super Administrador.');
        }

        $oldRole = $user->role;
        $newRole = $request->input('role');

        // Validar que el nuevo rol es válido
        if (!in_array($newRole, ['super_admin', 'worker', 'user'])) {
            return redirect()->route('admin.users')->with('error', 'Rol no válido.');
        }

        // Actualizar el rol
        $user->role = $newRole;
        $user->save();

        // Registrar la actividad con más detalles
        $description = "Rol cambiado de " . ucfirst($oldRole) . " a " . ucfirst($newRole) . " para usuario {$user->name} (ID: {$user->id})";

        $result = ActivityHelper::log(
            'change_role',
            $description,
            ['role' => $oldRole, 'user_id' => $user->id, 'user_name' => $user->name],
            ['role' => $newRole, 'user_id' => $user->id, 'user_name' => $user->name]
        );

        // Verificar si se registró correctamente
        if (!$result) {
            Log::error('Fallo al registrar cambio de rol en activity_logs', [
                'user_id' => $user->id,
                'old_role' => $oldRole,
                'new_role' => $newRole,
                'changed_by' => auth()->id()
            ]);
        }

        return redirect()->route('admin.users')->with('success', "Rol de {$user->name} actualizado a " . ucfirst($newRole) . " correctamente.");

    } catch (\Exception $e) {
        Log::error('Error en changeUserRole', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return redirect()->route('admin.users')->with('error', 'Error al cambiar el rol: ' . $e->getMessage());
    }
}
}
