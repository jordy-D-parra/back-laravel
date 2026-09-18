<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trabajador;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class TrabajadorController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-trabajadores')) {
            abort(403, 'No tienes permiso para ver trabajadores');
        }

        $query = Trabajador::with('usuario');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('cedula', 'ilike', "%{$search}%")
                    ->orWhere('nombre', 'ilike', "%{$search}%")
                    ->orWhere('apellido', 'ilike', "%{$search}%")
                    ->orWhere('cargo', 'ilike', "%{$search}%")
                    ->orWhere('especialidad', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('departamento')) {
            $query->where('departamento', 'ilike', "%{$request->departamento}%");
        }

        if ($request->filled('tiene_usuario')) {
            if ($request->tiene_usuario === 'si') {
                $query->has('usuario');
            } elseif ($request->tiene_usuario === 'no') {
                $query->doesntHave('usuario');
            }
        }

        $sortBy = $request->get('sort_by', 'apellido');
        $sortDir = $request->get('sort_dir', 'asc');
        $allowedSorts = ['cedula', 'nombre', 'apellido', 'departamento', 'cargo', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        $trabajadores = $query->paginate(15)->withQueryString();

        $totalTrabajadores = Trabajador::count();
        $conUsuario = Trabajador::has('usuario')->count();
        $sinUsuario = Trabajador::doesntHave('usuario')->count();
        $departamentos = Trabajador::distinct()->pluck('departamento')->filter()->count();
        $listaDepartamentos = Trabajador::distinct()
            ->pluck('departamento')
            ->filter()
            ->sort()
            ->values();

        return view('admin.trabajadores.index', compact(
            'trabajadores',
            'totalTrabajadores',
            'conUsuario',
            'sinUsuario',
            'departamentos',
            'listaDepartamentos'
        ));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('crear-trabajador')) {
            abort(403, 'No tienes permiso para crear trabajadores');
        }

        try {
            $validated = $request->validate([
                'cedula' => ['required', 'string', 'max:20', 'unique:trabajadores,cedula'],
                'nombre' => ['required', 'string', 'max:100'],
                'apellido' => ['required', 'string', 'max:100'],
                'departamento' => ['required', 'string', 'max:100'],
                'cargo' => ['required', 'string', 'max:100'],
                'especialidad' => ['nullable', 'string', 'max:255'],
                'telefono' => ['nullable', 'string', 'max:20'],
                'email' => ['nullable', 'email', 'max:100'],
            ]);

            $trabajador = Trabajador::create($validated);

            return redirect()->route('admin.trabajadores.index')
                ->with('success', 'Trabajador "' . $trabajador->nombre . ' ' . $trabajador->apellido . '" registrado exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Error de validación: ' . collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            Log::error('Error al crear trabajador: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar el trabajador: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Trabajador $trabajador)
    {
        if (!auth()->user()->hasPermission('editar-trabajador')) {
            abort(403, 'No tienes permiso para editar trabajadores');
        }

        try {
            $validated = $request->validate([
                'cedula' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('trabajadores', 'cedula')->ignore($trabajador->id),
                ],
                'nombre' => ['required', 'string', 'max:100'],
                'apellido' => ['required', 'string', 'max:100'],
                'departamento' => ['required', 'string', 'max:100'],
                'cargo' => ['required', 'string', 'max:100'],
                'especialidad' => ['nullable', 'string', 'max:255'],
                'telefono' => ['nullable', 'string', 'max:20'],
                'email' => ['nullable', 'email', 'max:100'],
            ]);

            $trabajador->update($validated);

            return redirect()->route('admin.trabajadores.index')
                ->with('success', 'Trabajador "' . $trabajador->nombre . ' ' . $trabajador->apellido . '" actualizado exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Error de validación: ' . collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            Log::error('Error al actualizar trabajador: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el trabajador: ' . $e->getMessage());
        }
    }

    public function destroy(Trabajador $trabajador)
    {
        if (!auth()->user()->hasPermission('eliminar-trabajador')) {
            abort(403, 'No tienes permiso para eliminar trabajadores');
        }

        if ($trabajador->usuario) {
            return redirect()->route('admin.trabajadores.index')
                ->with('error', 'No se puede eliminar: el trabajador tiene un usuario vinculado. Elimine primero el usuario.');
        }

        $usuarioActual = Auth::user();
        if ($usuarioActual && $usuarioActual->trabajador_id === $trabajador->id) {
            return redirect()->route('admin.trabajadores.index')
                ->with('error', 'No puedes eliminar tu propio registro de trabajador.');
        }

        $nombre = $trabajador->nombre . ' ' . $trabajador->apellido;

        try {
            $trabajador->delete();
            return redirect()->route('admin.trabajadores.index')
                ->with('success', 'Trabajador "' . $nombre . '" eliminado permanentemente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar trabajador: ' . $e->getMessage());
            return redirect()->route('admin.trabajadores.index')
                ->with('error', 'Error al eliminar el trabajador: ' . $e->getMessage());
        }
    }

    public function buscarPorCedula($cedula)
    {
        $trabajador = Trabajador::where('cedula', $cedula)->first();

        if (!$trabajador) {
            return response()->json(['encontrado' => false]);
        }

        $tieneUsuario = Usuario::where('trabajador_id', $trabajador->id)->exists();

        return response()->json([
            'encontrado' => true,
            'tiene_usuario' => $tieneUsuario,
            'trabajador' => [
                'id' => $trabajador->id,
                'cedula' => $trabajador->cedula,
                'nombre' => $trabajador->nombre,
                'apellido' => $trabajador->apellido,
                'departamento' => $trabajador->departamento,
                'cargo' => $trabajador->cargo,
                'especialidad' => $trabajador->especialidad,
                'telefono' => $trabajador->telefono,
            ]
        ]);
    }

    public function show(Trabajador $trabajador)
    {
        $trabajador->load('usuario.rol');

        return response()->json([
            'trabajador' => [
                'id' => $trabajador->id,
                'cedula' => $trabajador->cedula,
                'nombre_completo' => $trabajador->nombre . ' ' . $trabajador->apellido,
                'email' => $trabajador->email ?? 'No registrado',
                'departamento' => $trabajador->departamento,
                'cargo' => $trabajador->cargo,
                'especialidad' => $trabajador->especialidad ?? 'No asignada',
                'telefono' => $trabajador->telefono ?? 'No registrado',
                'created_at' => $trabajador->created_at->format('d/m/Y H:i'),
                'tiene_usuario' => !is_null($trabajador->usuario),
                'usuario' => $trabajador->usuario ? [
                    'nombre' => $trabajador->usuario->usuario,
                    'rol' => ucfirst($trabajador->usuario->rol->nombre),
                    'status' => $trabajador->usuario->status === 'activo' ? 'Activo' : 'Inactivo',
                    'ultimo_login' => $trabajador->usuario->ultimo_login
                        ? $trabajador->usuario->ultimo_login->format('d/m/Y H:i')
                        : 'Nunca',
                ] : null,
            ]
        ]);
    }
}