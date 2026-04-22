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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // ========== LOGIN Y REGISTRO ==========
    
    // Mostrar formulario de login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Procesar el login (usando cédula)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'cedula' => 'required|string',
            'password' => 'required',
        ]);

        // Buscar por cédula usando el modelo User
        $user = User::where('cedula', $credentials['cedula'])->first();

        if (!$user) {
            return back()->withErrors([
                'cedula' => 'La cédula no está registrada en el sistema.',
            ])->onlyInput('cedula');
        }

        // Verificar estado del usuario
        if ($user->estado_usuario !== 'activo') {
            return back()->withErrors([
                'cedula' => 'Tu cuenta no está activa. Contacta al administrador.',
            ])->onlyInput('cedula');
        }

        // Intentar autenticar
        if (Auth::attempt(['cedula' => $credentials['cedula'], 'password' => $credentials['password']], $request->remember)) {
            ActivityHelper::log('login', 'Usuario inició sesión');

            // Actualizar último login
            $user->ultimo_login = now();
            $user->save();

            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'password' => 'Contraseña incorrecta.',
        ])->onlyInput('cedula');
    }

    // Mostrar formulario de registro
    public function showRegister()
    {
        return view('auth.register');
    }

    // Procesar registro
    public function register(Request $request)
    {
        Log::info('===== INICIO REGISTRO =====');
        Log::info('Datos:', $request->all());

        try {
            $request->validate([
                'nombre' => 'required|string|max:100',
                'apellido' => 'required|string|max:100',
                'cedula' => 'required|string|unique:usuario,cedula',
                'departamento' => 'nullable|string|max:100',
                'cargo' => 'nullable|string|max:100',
                'password' => 'required|min:6|confirmed',
                'pregunta_seguridad_1' => 'required|string',
                'respuesta_1' => 'required|string',
                'pregunta_seguridad_2' => 'required|string',
                'respuesta_2' => 'required|string',
            ]);

            // Obtener roles dinámicamente desde la base de datos
            $superAdminRol = DB::table('rol')->where('nombre', 'super_admin')->first();
            $usuarioRol = DB::table('rol')->where('nombre', 'usuario')->first();
            
            // Verificar que los roles existen en la BD
            if (!$superAdminRol) {
                Log::error('Rol super_admin no encontrado en la base de datos');
                return back()->withErrors(['error' => 'Error de configuración: Rol super_admin no existe. Contacta al administrador.']);
            }
            
            if (!$usuarioRol) {
                Log::error('Rol usuario no encontrado en la base de datos');
                return back()->withErrors(['error' => 'Error de configuración: Rol usuario no existe. Contacta al administrador.']);
            }
            
            // Determinar rol (el primer usuario es super_admin)
            $esPrimerUsuario = User::count() === 0;
            $idRol = $esPrimerUsuario ? $superAdminRol->id : $usuarioRol->id;
            $nombreRol = $esPrimerUsuario ? $superAdminRol->nombre : $usuarioRol->nombre;
            
            Log::info('Asignando rol:', [
                'es_primer_usuario' => $esPrimerUsuario,
                'id_rol' => $idRol,
                'nombre_rol' => $nombreRol
            ]);

            $user = User::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'cedula' => $request->cedula,
                'departamento' => $request->departamento,
                'cargo' => $request->cargo,
                'password' => Hash::make($request->password),
                'pregunta_seguridad_1' => $request->pregunta_seguridad_1,
                'respuesta_1' => Hash::make(strtolower(trim($request->respuesta_1))),
                'pregunta_seguridad_2' => $request->pregunta_seguridad_2,
                'respuesta_2' => Hash::make(strtolower(trim($request->respuesta_2))),
                'id_rol' => $idRol,
                'estado_usuario' => $esPrimerUsuario ? 'activo' : 'pendiente',
                'fecha_solicitud' => now(),
                'activo' => true,
            ]);

            ActivityHelper::log('register', 'Nuevo usuario registrado', null, [
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'cedula' => $user->cedula,
                'rol' => $nombreRol,
                'id_rol' => $idRol
            ]);

            if ($esPrimerUsuario) {
                Auth::login($user);
                return redirect()->route('dashboard')->with('success', '¡Bienvenido Super Administrador! El sistema ha sido configurado correctamente.');
            }

            return redirect()->route('login')->with('status', '✅ Registro exitoso. Tu cuenta será activada por un administrador.');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('ERROR DE BASE DE DATOS EN REGISTRO: ' . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                return back()->withErrors(['error' => 'Error de configuración: Roles no encontrados. Por favor ejecuta: php artisan db:seed']);
            }
            
            return back()->withErrors(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('ERROR EN REGISTRO: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Error al registrar: ' . $e->getMessage()]);
        }
    }

    // Cerrar sesión
    public function logout(Request $request)
    {
        ActivityHelper::log('logout', 'Usuario cerró sesión');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // Dashboard
    public function dashboard()
    {
        return view('dashboard');
    }

    // ========== RECUPERACIÓN DE CONTRASEÑA ==========
    
    // Mostrar formulario recuperar contraseña
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // Verificar cédula para recuperación (AJAX)
    public function verifyEmailForRecovery(Request $request)
    {
        $request->validate(['cedula' => 'required|string|exists:usuario,cedula']);

        $user = User::where('cedula', $request->cedula)->first();

        if (!$user->pregunta_seguridad_1 || !$user->pregunta_seguridad_2) {
            return response()->json([
                'success' => false,
                'message' => 'Este usuario no tiene preguntas de seguridad configuradas.'
            ]);
        }

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'question1' => $user->pregunta_seguridad_1,
            'question2' => $user->pregunta_seguridad_2
        ]);
    }

    // Verificar respuestas (AJAX)
    public function verifyAnswers(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:usuario,id',
            'answer_1' => 'required|string',
            'answer_2' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);

        $answer1 = strtolower(trim($request->answer_1));
        $answer2 = strtolower(trim($request->answer_2));

        $valid1 = Hash::check($answer1, $user->respuesta_1);
        $valid2 = Hash::check($answer2, $user->respuesta_2);

        if ($valid1 && $valid2) {
            session(['reset_user_id' => $user->id]);
            ActivityHelper::log('password_recovery', 'Usuario verificó preguntas de seguridad', null, ['user_id' => $user->id]);

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

    // Mostrar formulario nueva contraseña
    public function showResetPasswordForm()
    {
        if (!session('reset_user_id')) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password-questions');
    }

    // Recuperar contraseña en una sola página
    public function resetPasswordByQuestions(Request $request)
    {
        $request->validate([
            'cedula' => 'required|exists:usuario,cedula',
            'pregunta_seguridad_1' => 'required',
            'respuesta_1' => 'required',
            'pregunta_seguridad_2' => 'required',
            'respuesta_2' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('cedula', $request->cedula)->first();

        // Verificar respuestas
        $valid1 = Hash::check(strtolower(trim($request->respuesta_1)), $user->respuesta_1);
        $valid2 = Hash::check(strtolower(trim($request->respuesta_2)), $user->respuesta_2);

        if (!$valid1 || !$valid2) {
            return back()->withErrors(['error' => 'Respuestas de seguridad incorrectas.']);
        }

        // Cambiar contraseña
        $user->password = Hash::make($request->password);
        $user->save();

        ActivityHelper::log('password_reset', 'Usuario cambió contraseña mediante preguntas de seguridad', null, ['user_id' => $user->id]);

        return redirect()->route('login')->with('status', '✅ Contraseña actualizada correctamente.');
    }

    // Métodos para compatibilidad con sistema de email (opcional)
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuario,email'
        ]);
        
        // Redirigir a preguntas de seguridad si no hay email configurado
        return redirect()->route('password.request')
            ->with('info', 'Usa el sistema de recuperación por preguntas de seguridad.');
    }

    public function showResetForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        return redirect()->route('login')
            ->with('info', 'Usa el sistema de recuperación por preguntas de seguridad.');
    }

    // ========== PANEL DE ADMINISTRACIÓN ==========
    
    // Panel de administración de usuarios
    public function adminUsers()
    {
        // Verificar que el usuario actual es Super Admin o Admin
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        // Obtener todos los usuarios con su rol
        $users = User::with('rol')->orderBy('created_at', 'desc')->get();
        
        // Obtener todos los roles activos para el selector
        $roles = DB::table('rol')->where('es_activo', true)->get();

        return view('admin.users', compact('users', 'roles'));
    }

    // Admin cambiar contraseña de usuario (CON SOPORTE AJAX)
public function adminResetPassword(Request $request)
{
    try {
        // Verificar permisos
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No tienes permisos para cambiar contraseñas.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'No tienes permisos.');
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:usuario,id',
            'new_password' => 'required|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return back()->withErrors($validator);
        }

        $user = User::find($request->user_id);
        
        // No permitir cambiar la contraseña de uno mismo
        if ($user->id === auth()->id()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usa la sección de perfil para cambiar tu propia contraseña.'
                ]);
            }
            return back()->with('error', 'Usa la sección de perfil para cambiar tu propia contraseña.');
        }
        
        $user->password = Hash::make($request->new_password);
        $user->save();

        ActivityHelper::log('change_password', "Admin cambió contraseña de {$user->nombre}", null, ['user_id' => $user->id]);

        $message = "Contraseña de {$user->nombre} actualizada correctamente.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return back()->with('success', $message);
        
    } catch (\Exception $e) {
        Log::error('Error en adminResetPassword: ' . $e->getMessage());
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar la contraseña: ' . $e->getMessage()
            ], 500);
        }
        
        return back()->with('error', 'Error al cambiar la contraseña: ' . $e->getMessage());
    }
}

    // Cambiar rol de usuario (CON SOPORTE AJAX)
    public function changeUserRole(Request $request, $id)
    {
        try {
            // Verificar permisos
            if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'No tienes permisos para cambiar roles.'
                    ], 403);
                }
                return redirect()->route('admin.users')->with('error', 'No tienes permisos para cambiar roles.');
            }

            $validator = Validator::make($request->all(), [
                'id_rol' => 'required|exists:rol,id'
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first()
                    ], 422);
                }
                return redirect()->route('admin.users')->with('error', $validator->errors()->first());
            }

            $user = User::findOrFail($id);

            // No permitir cambiar el rol de uno mismo
            if ($user->id === auth()->id()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'No puedes cambiar tu propio rol.'
                    ]);
                }
                return redirect()->route('admin.users')->with('error', 'No puedes cambiar tu propio rol.');
            }

            $oldRoleId = $user->id_rol;
            $newRoleId = $request->input('id_rol');

            $oldRol = DB::table('rol')->where('id', $oldRoleId)->first();
            $newRol = DB::table('rol')->where('id', $newRoleId)->first();

            if (!$newRol) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Rol no válido.'
                    ]);
                }
                return redirect()->route('admin.users')->with('error', 'Rol no válido.');
            }

            $user->id_rol = $newRoleId;
            $user->save();
            
            // Recargar la relación del rol
            $user->load('rol');

            ActivityHelper::log('change_role', "Rol cambiado de " . ($oldRol->nombre ?? 'NINGUNO') . " a {$newRol->nombre} para {$user->nombre}",
                ['id_rol' => $oldRoleId], ['id_rol' => $newRoleId]);

            $mensaje = "Rol de {$user->nombre} actualizado a {$newRol->nombre}.";

            // Si es petición AJAX, devolver JSON
            if ($request->ajax() || $request->wantsJson()) {
                $badgeHtml = $this->generateRoleBadge($newRol);
                
                return response()->json([
                    'success' => true,
                    'message' => $mensaje,
                    'user' => [
                        'id' => $user->id,
                        'nombre' => $user->nombre . ' ' . $user->apellido,
                        'rol_id' => $user->id_rol,
                        'rol_nombre' => $newRol->nombre,
                        'rol_nivel' => $newRol->nivel ?? null,
                        'badge_html' => $badgeHtml
                    ]
                ]);
            }

            return redirect()->route('admin.users')->with('success', $mensaje);

        } catch (\Exception $e) {
            Log::error('Error en changeUserRole: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cambiar el rol: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('admin.users')->with('error', 'Error al cambiar el rol: ' . $e->getMessage());
        }
    }

    // Cambiar estado de usuario (CON SOPORTE AJAX)
    public function changeUserStatus(Request $request)
    {
        try {
            // Verificar permisos
            if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'No tienes permisos para cambiar estados.'
                    ], 403);
                }
                return redirect()->route('admin.users')->with('error', 'No tienes permisos para cambiar estados.');
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:usuario,id',
                'estado_usuario' => 'required|in:activo,pendiente,inactivo,suspendido',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first()
                    ], 422);
                }
                return redirect()->route('admin.users')->with('error', $validator->errors()->first());
            }

            $user = User::findOrFail($request->user_id);

            // No permitir cambiar el estado de uno mismo
            if ($user->id === auth()->id()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'No puedes cambiar tu propio estado.'
                    ]);
                }
                return redirect()->route('admin.users')->with('error', 'No puedes cambiar tu propio estado.');
            }

            $oldEstado = $user->estado_usuario;
            $user->estado_usuario = $request->estado_usuario;
            $user->save();

            ActivityHelper::log('change_status', "Estado cambiado de {$oldEstado} a {$request->estado_usuario} para {$user->nombre}",
                ['estado_anterior' => $oldEstado], ['estado_nuevo' => $request->estado_usuario]);

            $mensaje = "Estado de {$user->nombre} actualizado a " . ucfirst($request->estado_usuario);
            
            if ($oldEstado === 'pendiente' && $request->estado_usuario === 'activo') {
                $mensaje .= ". El usuario ya puede iniciar sesión.";
            }

            // Si es petición AJAX, devolver JSON
            if ($request->ajax() || $request->wantsJson()) {
                $badgeHtml = $this->generateStatusBadge($user->estado_usuario);
                
                return response()->json([
                    'success' => true,
                    'message' => $mensaje,
                    'user' => [
                        'id' => $user->id,
                        'estado' => $user->estado_usuario,
                        'badge_html' => $badgeHtml
                    ]
                ]);
            }

            return redirect()->route('admin.users')->with('success', $mensaje);
            
        } catch (\Exception $e) {
            Log::error('Error en changeUserStatus: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cambiar el estado: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('admin.users')->with('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }
    
    // Activar múltiples usuarios pendientes
    public function activatePendingUsers(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos.');
        }
        
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:usuario,id'
        ]);
        
        $count = User::whereIn('id', $request->user_ids)
            ->where('estado_usuario', 'pendiente')
            ->update(['estado_usuario' => 'activo']);
        
        ActivityHelper::log('activate_users', "Admin activó {$count} usuarios pendientes");
        
        return redirect()->route('admin.users')->with('success', "Se activaron {$count} usuario(s) correctamente.");
    }

    // ========== MÉTODOS PARA API Y ESTADÍSTICAS ==========
    
    // Obtener estadísticas de usuarios (para dashboard)
    public function getUserStats()
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        
        $stats = [
            'total' => User::count(),
            'activos' => User::where('estado_usuario', 'activo')->count(),
            'pendientes' => User::where('estado_usuario', 'pendiente')->count(),
            'inactivos' => User::where('estado_usuario', 'inactivo')->count(),
            'suspendidos' => User::where('estado_usuario', 'suspendido')->count(),
            'super_admins' => User::whereHas('rol', function($q) {
                $q->where('nombre', 'super_admin');
            })->count(),
            'admins' => User::whereHas('rol', function($q) {
                $q->where('nombre', 'admin');
            })->count(),
            'workers' => User::whereHas('rol', function($q) {
                $q->where('nombre', 'worker');
            })->count(),
            'users' => User::whereHas('rol', function($q) {
                $q->where('nombre', 'usuario');
            })->count(),
        ];
        
        return response()->json($stats);
    }

    // Obtener lista de usuarios (para selectores y APIs)
    public function getUsersList(Request $request)
    {
        try {
            if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            
            $query = User::with('rol');
            
            // Filtro por búsqueda
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'LIKE', "%{$search}%")
                      ->orWhere('apellido', 'LIKE', "%{$search}%")
                      ->orWhere('cedula', 'LIKE', "%{$search}%");
                });
            }
            
            // Filtro por rol
            if ($request->has('rol_id')) {
                $query->where('id_rol', $request->rol_id);
            }
            
            // Filtro por estado
            if ($request->has('estado')) {
                $query->where('estado_usuario', $request->estado);
            }
            
            $users = $query->orderBy('nombre')->get();
            
            return response()->json([
                'success' => true,
                'users' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'nombre_completo' => $user->nombre . ' ' . $user->apellido,
                        'cedula' => $user->cedula,
                        'rol' => $user->rol ? $user->rol->nombre : null,
                        'rol_id' => $user->id_rol,
                        'estado' => $user->estado_usuario,
                        'badge_rol' => $this->generateRoleBadge($user->rol),
                        'badge_estado' => $this->generateStatusBadge($user->estado_usuario)
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getUsersList: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    // Obtener detalles de un usuario específico
    public function getUserDetails($id)
    {
        try {
            if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            
            $user = User::with('rol')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'apellido' => $user->apellido,
                    'nombre_completo' => $user->nombre . ' ' . $user->apellido,
                    'cedula' => $user->cedula,
                    'departamento' => $user->departamento,
                    'cargo' => $user->cargo,
                    'email' => $user->email ?? null,
                    'rol_id' => $user->id_rol,
                    'rol_nombre' => $user->rol ? $user->rol->nombre : null,
                    'estado_usuario' => $user->estado_usuario,
                    'fecha_solicitud' => $user->fecha_solicitud ? Carbon::parse($user->fecha_solicitud)->format('d/m/Y') : null,
                    'ultimo_login' => $user->ultimo_login ? Carbon::parse($user->ultimo_login)->format('d/m/Y H:i') : 'Nunca',
                    'preguntas_seguridad' => [
                        'pregunta_1' => $user->pregunta_seguridad_1,
                        'pregunta_2' => $user->pregunta_seguridad_2
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getUserDetails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }
    }

    // ========== MÉTODOS AUXILIARES ==========
    
    /**
     * Genera el badge HTML para el rol
     */
    private function generateRoleBadge($rol)
    {
        if (!$rol) {
            return '<span class="badge bg-danger fs-6 p-2">⚠️ SIN ROL</span>';
        }
        
        $badgeClass = match($rol->nombre) {
            'super_admin' => 'bg-danger',
            'admin' => 'bg-warning text-dark',
            'worker' => 'bg-primary',
            'user', 'usuario' => 'bg-success',
            default => 'bg-secondary'
        };
        
        $roleIcon = match($rol->nombre) {
            'super_admin' => '👑',
            'admin' => '⚙️',
            'worker' => '🔧',
            'user', 'usuario' => '👤',
            default => '❓'
        };
        
        $nivelTexto = $rol->nivel ? " <small>(Nv.{$rol->nivel})</small>" : '';
        
        return '<span class="badge ' . $badgeClass . ' fs-6 p-2">' . $roleIcon . ' ' . ucfirst($rol->nombre) . $nivelTexto . '</span>';
    }

    /**
     * Genera el badge HTML para el estado
     */
    private function generateStatusBadge($estado)
    {
        $estadoColors = [
            'activo' => 'success',
            'pendiente' => 'warning',
            'inactivo' => 'danger',
            'suspendido' => 'secondary'
        ];
        $color = $estadoColors[$estado] ?? 'secondary';
        
        $estadoIcon = match($estado) {
            'activo' => '✅',
            'pendiente' => '⏳',
            'inactivo' => '❌',
            'suspendido' => '⚠️',
            default => ''
        };
        
        return '<span class="badge bg-' . $color . ' fs-6 p-2">' . $estadoIcon . ' ' . ucfirst($estado) . '</span>';
    }
}