<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Trabajador;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    // ============================================================
    // INDEX
    // ============================================================
       public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-usuarios')) {
            abort(403, 'No tienes permiso para ver usuarios');
        }

        // ✅ Traer TODOS los usuarios con sus relaciones (sin paginar)
        $usuarios = Usuario::with(['trabajador', 'rol'])
            ->orderBy('usuario', 'asc')
            ->get();

        $roles = Rol::all();

        $totalActivos = Usuario::where('status', 'activo')->count();
        $totalInactivos = Usuario::where('status', 'inactivo')->count();
        $pendientesCambio = Usuario::where('must_change_password', true)->count();
        $nuncaLogeados = Usuario::whereNull('ultimo_login')->count();

        return view('admin.usuarios.index', compact(
            'usuarios',
            'roles',
            'totalActivos',
            'totalInactivos',
            'pendientesCambio',
            'nuncaLogeados'
        ));
    }

    // ============================================================
    // STORE
    // ============================================================
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-usuario')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para crear usuarios'], 403);
            }
            abort(403, 'No tienes permiso para crear usuarios');
        }

        try {
            $validated = $request->validate([
                'trabajador_id' => ['required', 'exists:trabajadores,id', 'unique:usuarios,trabajador_id'],
                'usuario' => ['required', 'string', 'max:50', 'unique:usuarios,usuario'],
                'rol_id' => ['required', 'exists:roles,id'],
            ]);

            // ============================================================
            // GENERAR CONTRASEÑA TEMPORAL SEGURA
            // ============================================================
            $password = $this->generarPasswordTemporal(12);

            // ============================================================
            // CREAR USUARIO
            // ============================================================
            $usuario = Usuario::create([
                'usuario' => $validated['usuario'],
                'password' => Hash::make($password),
                'must_change_password' => true,
                'status' => 'activo',
                'trabajador_id' => $validated['trabajador_id'],
                'rol_id' => $validated['rol_id'],
            ]);

            // ============================================================
            // VERIFICAR QUE EL HASH CORRESPONDA (debug)
            // ============================================================
            $verificacion = Hash::check($password, $usuario->fresh()->password);

            Log::info('Usuario creado', [
                'usuario' => $usuario->usuario,
                'password_temporal' => $password,
                'verificacion_hash' => $verificacion,
            ]);

            if (!$verificacion) {
                Log::error('❌ El hash NO corresponde a la contraseña generada', [
                    'usuario' => $usuario->usuario,
                    'password_generada' => $password,
                    'hash_guardado' => $usuario->fresh()->password,
                ]);
            }

            // ============================================================
            // NOTIFICACIÓN DE BIENVENIDA (opcional)
            // ============================================================
            try {
                $this->enviarNotificacionBienvenida($usuario, $password);
            } catch (\Exception $e) {
                Log::error('Error al enviar notificación de bienvenida: ' . $e->getMessage());
            }

            // ============================================================
            // RESPUESTA
            // ============================================================
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario creado exitosamente. Guarde la contraseña temporal.',
                    'data' => $usuario,
                    'new_password' => $password,
                    'new_usuario' => $usuario->usuario,
                    'verificacion' => $verificacion,
                ]);
            }

            return redirect()->route('admin.usuarios.index')
                ->with('success', 'Usuario creado exitosamente.')
                ->with('new_password', $password)
                ->with('new_usuario', $usuario->usuario);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error al crear usuario: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear usuario: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Error al crear usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ============================================================
    // UPDATE
    // ============================================================
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('editar-usuario')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para editar usuarios'], 403);
            }
            abort(403, 'No tienes permiso para editar usuarios');
        }

        try {
            $usuario = Usuario::findOrFail($id);

            $validated = $request->validate([
                'usuario' => ['required', 'string', 'max:50', 'unique:usuarios,usuario,' . $id],
                'rol_id' => ['required', 'exists:roles,id'],
                'status' => ['required', 'in:activo,inactivo'],
            ]);

            $usuario->update($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario actualizado exitosamente',
                    'data' => $usuario
                ]);
            }

            return redirect()->route('admin.usuarios.index')
                ->with('success', 'Usuario actualizado exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error al actualizar usuario: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar usuario: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Error al actualizar usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ============================================================
    // DESTROY
    // ============================================================
    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('eliminar-usuario')) {
            abort(403, 'No tienes permiso para eliminar usuarios');
        }

        $usuario = Usuario::findOrFail($id);

        if ($usuario->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($usuario->isRole('admin')) {
            $totalAdmins = Usuario::whereHas('rol', function ($q) {
                $q->where('nombre', 'admin');
            })->where('status', 'activo')->count();

            if ($totalAdmins <= 1) {
                return back()->with('error', 'No puedes eliminar al único administrador del sistema.');
            }
        }

        $nombreUsuario = $usuario->usuario;
        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario "' . $nombreUsuario . '" eliminado permanentemente.');
    }

    // ============================================================
    // TOGGLE STATUS
    // ============================================================
    public function toggleStatus(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('activar-desactivar-usuario')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para cambiar el estado de usuarios'], 403);
            }
            abort(403, 'No tienes permiso para cambiar el estado de usuarios');
        }

        try {
            $usuario = Usuario::findOrFail($id);

            if ($usuario->id === Auth::id() && $usuario->status === 'activo') {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'No puedes desactivar tu propio usuario.'], 422);
                }
                return back()->with('error', 'No puedes desactivar tu propio usuario.');
            }

            $usuario->status = $usuario->status === 'activo' ? 'inactivo' : 'activo';
            $usuario->save();

            $estado = $usuario->status === 'activo' ? 'activado' : 'desactivado';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario "' . $usuario->usuario . '" ' . $estado . '.',
                    'status' => $usuario->status
                ]);
            }

            return back()->with('success', 'Usuario "' . $usuario->usuario . '" ' . $estado . '.');

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de usuario: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cambiar estado: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error al cambiar estado: ' . $e->getMessage());
        }
    }

    // ============================================================
    // RESET PASSWORD
    // ============================================================
    public function resetPassword(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('resetear-password-usuario')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para resetear contraseñas'], 403);
            }
            abort(403, 'No tienes permiso para resetear contraseñas');
        }

        try {
            $usuario = Usuario::findOrFail($id);

            // Usar la misma función segura
            $password = $this->generarPasswordTemporal(12);

            $usuario->password = Hash::make($password);
            $usuario->must_change_password = true;
            $usuario->save();

            // Verificar
            $verificacion = Hash::check($password, $usuario->fresh()->password);

            Log::info('Contraseña reseteada', [
                'usuario' => $usuario->usuario,
                'password_temporal' => $password,
                'verificacion' => $verificacion,
            ]);

            try {
                $this->enviarNotificacionResetPassword($usuario, $password);
            } catch (\Exception $e) {
                Log::error('Error al enviar notificación de reset: ' . $e->getMessage());
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contraseña reseteada exitosamente',
                    'new_password' => $password,
                    'usuario' => $usuario->usuario,
                    'verificacion' => $verificacion,
                ]);
            }

            return redirect()->route('admin.usuarios.index')
                ->with('success', 'Contraseña reseteada exitosamente.')
                ->with('reset_password', $password)
                ->with('reset_usuario', $usuario->usuario);

        } catch (\Exception $e) {
            Log::error('Error al resetear contraseña: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al resetear contraseña: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error al resetear contraseña: ' . $e->getMessage());
        }
    }

    // ============================================================
    // SHOW
    // ============================================================
    public function show($id)
    {
        if (!auth()->user()->hasPermission('ver-usuarios')) {
            abort(403, 'No tienes permiso para ver usuarios');
        }

        $usuario = Usuario::with(['trabajador', 'rol'])->findOrFail($id);

        return response()->json([
            'usuario' => $usuario->usuario,
            'status' => $usuario->status,
            'must_change_password' => $usuario->must_change_password,
            'ultimo_login' => $usuario->ultimo_login ? $usuario->ultimo_login->format('d/m/Y H:i:s') : 'Nunca',
            'created_at' => $usuario->created_at->format('d/m/Y H:i:s'),
            'rol' => ucfirst($usuario->rol->nombre),
            'trabajador' => [
                'cedula' => $usuario->trabajador->cedula,
                'nombre_completo' => $usuario->trabajador->nombre . ' ' . $usuario->trabajador->apellido,
                'departamento' => $usuario->trabajador->departamento,
                'cargo' => $usuario->trabajador->cargo,
                'especialidad' => $usuario->trabajador->especialidad ?? 'No asignada',
                'telefono' => $usuario->trabajador->telefono ?? 'No registrado',
                'email' => $usuario->trabajador->email ?? 'No registrado',
            ]
        ]);
    }

    // ============================================================
    // HELPERS PRIVADOS
    // ============================================================

    /**
     * Genera una contraseña temporal con caracteres seguros.
     * Evita caracteres que causan problemas al copiar/pegar.
     */
    private function generarPasswordTemporal(int $longitud = 12): string
    {
        // Caracteres seguros: solo letras, números y unos pocos símbolos
        $mayusculas = 'ABCDEFGHJKLMNPQRSTUVWXYZ';  // Sin I, O (confunden)
        $minusculas = 'abcdefghijkmnopqrstuvwxyz';  // Sin l (confunde con 1)
        $numeros = '23456789';                      // Sin 0, 1 (confunden)
        $simbolos = '!@#$%&*';                      // Solo símbolos seguros

        $todos = $mayusculas . $minusculas . $numeros . $simbolos;

        // Asegurar al menos un carácter de cada tipo
        $password = '';
        $password .= $mayusculas[random_int(0, strlen($mayusculas) - 1)];
        $password .= $minusculas[random_int(0, strlen($minusculas) - 1)];
        $password .= $numeros[random_int(0, strlen($numeros) - 1)];
        $password .= $simbolos[random_int(0, strlen($simbolos) - 1)];

        // Rellenar el resto
        for ($i = 4; $i < $longitud; $i++) {
            $password .= $todos[random_int(0, strlen($todos) - 1)];
        }

        // Mezclar
        return str_shuffle($password);
    }

    /**
     * Envía notificación de bienvenida con la contraseña temporal.
     */
    private function enviarNotificacionBienvenida(Usuario $usuario, string $password): void
    {
        try {
            $notificacionService = app(\App\Services\NotificacionService::class);

            $notificacionService->enviarAUsuario(
                $usuario,
                '🎉 ¡Bienvenido al Sistema!',
                "Se ha creado tu usuario en el Sistema de Gestión de Inventario.\n\n" .
                "Usuario: {$usuario->usuario}\n" .
                "Contraseña temporal: {$password}\n\n" .
                "Por favor, cambia tu contraseña en el primer inicio de sesión.",
                'sistema',
                route('login'),
                true
            );
        } catch (\Exception $e) {
            Log::error('Error en enviarNotificacionBienvenida: ' . $e->getMessage());
        }
    }

    /**
     * Envía notificación de reset de contraseña.
     */
    private function enviarNotificacionResetPassword(Usuario $usuario, string $password): void
    {
        try {
            $notificacionService = app(\App\Services\NotificacionService::class);

            $notificacionService->enviarAUsuario(
                $usuario,
                '🔑 Contraseña Reseteada',
                "Tu contraseña ha sido reseteada.\n\n" .
                "Usuario: {$usuario->usuario}\n" .
                "Nueva contraseña temporal: {$password}\n\n" .
                "Por favor, cambia tu contraseña en el primer inicio de sesión.",
                'sistema',
                route('login'),
                true
            );
        } catch (\Exception $e) {
            Log::error('Error en enviarNotificacionResetPassword: ' . $e->getMessage());
        }
    }
}