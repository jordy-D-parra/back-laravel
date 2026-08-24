<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Trabajador;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-usuarios')) {
            abort(403, 'No tienes permiso para ver usuarios');
        }

        $query = Usuario::with(['trabajador', 'rol']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('usuario', 'ilike', "%{$search}%")
                  ->orWhereHas('trabajador', function ($q2) use ($search) {
                      $q2->where('nombre', 'ilike', "%{$search}%")
                         ->orWhere('apellido', 'ilike', "%{$search}%")
                         ->orWhere('cedula', 'ilike', "%{$search}%");
                  });
            });
        }

        if ($request->filled('rol')) {
            $query->where('rol_id', $request->rol);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('must_change')) {
            $query->where('must_change_password', $request->must_change === '1');
        }

        $usuarios = $query->paginate(15)->withQueryString();
        $trabajadoresDisponibles = Trabajador::doesntHave('usuario')->get();
        $roles = Rol::all();

        $totalActivos = Usuario::where('status', 'activo')->count();
        $totalInactivos = Usuario::where('status', 'inactivo')->count();
        $pendientesCambio = Usuario::where('must_change_password', true)->count();
        $nuncaLogeados = Usuario::whereNull('ultimo_login')->count();

        return view('admin.usuarios.index', compact(
            'usuarios',
            'trabajadoresDisponibles',
            'roles',
            'totalActivos',
            'totalInactivos',
            'pendientesCambio',
            'nuncaLogeados'
        ));
    }

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
                'email' => ['required', 'email', 'max:100', 'unique:usuarios,email'],
                'rol_id' => ['required', 'exists:roles,id'],
            ]);

            $password = Str::random(12);

            $usuario = Usuario::create([
                'usuario' => $validated['usuario'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'must_change_password' => true,
                'status' => 'activo',
                'trabajador_id' => $validated['trabajador_id'],
                'rol_id' => $validated['rol_id'],
            ]);

            // Enviar notificación - SOLO UNA VEZ
            try {
                $this->enviarNotificacionBienvenida($usuario, $password);
            } catch (\Exception $e) {
                Log::error('Error al enviar notificación de bienvenida: ' . $e->getMessage());
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario creado exitosamente',
                    'data' => $usuario
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
                'email' => ['required', 'email', 'max:100', 'unique:usuarios,email,' . $id],
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

    private function enviarNotificacionBienvenida(Usuario $usuario, string $password)
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
            $password = Str::random(12);
            $usuario->password = Hash::make($password);
            $usuario->must_change_password = true;
            $usuario->save();

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
                    'usuario' => $usuario->usuario
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

    private function enviarNotificacionResetPassword(Usuario $usuario, string $password)
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
            ]
        ]);
    }
}