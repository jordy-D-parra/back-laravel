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

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('ver-usuarios')) {
            abort(403, 'No tienes permiso para ver usuarios');
        }

        $query = Usuario::with(['trabajador', 'rol']);

        // Filtro por búsqueda (nombre, cédula o usuario)
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

        // Filtro por rol
        if ($request->filled('rol')) {
            $query->where('rol_id', $request->rol);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por cambio de contraseña pendiente
        if ($request->filled('must_change')) {
            $query->where('must_change_password', $request->must_change === '1');
        }

        $usuarios = $query->paginate(15)->withQueryString();
        $trabajadoresDisponibles = Trabajador::doesntHave('usuario')->get();
        $roles = Rol::all();

        // Estadísticas para las tarjetas superiores
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
        // Verificar permiso
        if (!auth()->user()->hasPermission('crear-usuario')) {
            abort(403, 'No tienes permiso para crear usuarios');
        }

        $validated = $request->validate([
            'trabajador_id' => ['required', 'exists:trabajadores,id', 'unique:usuarios,trabajador_id'],
            'usuario' => ['required', 'string', 'max:50', 'unique:usuarios,usuario'],
            'rol_id' => ['required', 'exists:roles,id'],
        ]);

        $password = Str::random(12);

        $usuario = Usuario::create([
            'usuario' => $validated['usuario'],
            'password' => Hash::make($password),
            'must_change_password' => true,
            'status' => 'activo',
            'trabajador_id' => $validated['trabajador_id'],
            'rol_id' => $validated['rol_id'],
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado exitosamente.')
            ->with('new_password', $password)
            ->with('new_usuario', $usuario->usuario);
    }

    public function update(Request $request, Usuario $usuario)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('editar-usuario')) {
            abort(403, 'No tienes permiso para editar usuarios');
        }

        $validated = $request->validate([
            'usuario' => ['required', 'string', 'max:50', 'unique:usuarios,usuario,' . $usuario->id],
            'rol_id' => ['required', 'exists:roles,id'],
            'status' => ['required', 'in:activo,inactivo'],
        ]);

        $usuario->update($validated);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(Usuario $usuario)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('eliminar-usuario')) {
            abort(403, 'No tienes permiso para eliminar usuarios');
        }

        // No permitir eliminar al propio usuario
        if ($usuario->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // No permitir eliminar al ultimo admin
        if ($usuario->isRole('admin')) {
            $totalAdmins = Usuario::whereHas('rol', function ($q) {
                $q->where('nombre', 'admin');
            })->where('status', 'activo')->count();

            if ($totalAdmins <= 1) {
                return back()->with('error', 'No puedes eliminar al unico administrador del sistema.');
            }
        }

        $nombreUsuario = $usuario->usuario;
        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario "' . $nombreUsuario . '" eliminado permanentemente.');
    }

    public function toggleStatus(Usuario $usuario)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('activar-desactivar-usuario')) {
            abort(403, 'No tienes permiso para cambiar el estado de usuarios');
        }

        // No permitir desactivar al propio usuario
        if ($usuario->id === Auth::id() && $usuario->status === 'activo') {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $usuario->status = $usuario->status === 'activo' ? 'inactivo' : 'activo';
        $usuario->save();

        $estado = $usuario->status === 'activo' ? 'activado' : 'desactivado';
        return back()->with('success', 'Usuario "' . $usuario->usuario . '" ' . $estado . '.');
    }

    public function resetPassword(Usuario $usuario)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('resetear-password-usuario')) {
            abort(403, 'No tienes permiso para resetear contraseñas');
        }

        $password = Str::random(12);
        $usuario->password = Hash::make($password);
        $usuario->must_change_password = true;
        $usuario->save();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Contrasena reseteada exitosamente.')
            ->with('reset_password', $password)
            ->with('reset_usuario', $usuario->usuario);
    }

    public function show(Usuario $usuario)
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('ver-usuarios')) {
            abort(403, 'No tienes permiso para ver usuarios');
        }

        $usuario->load(['trabajador', 'rol']);
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
