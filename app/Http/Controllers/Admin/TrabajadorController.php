<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trabajador;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrabajadorController extends Controller
{
    public function index(Request $request)
    {
        $query = Trabajador::with('usuario');

        // Busqueda
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

        // Filtro por departamento
        if ($request->filled('departamento')) {
            $query->where('departamento', 'ilike', "%{$request->departamento}%");
        }

        // Filtro por estado de usuario
        if ($request->filled('tiene_usuario')) {
            if ($request->tiene_usuario === 'si') {
                $query->has('usuario');
            } elseif ($request->tiene_usuario === 'no') {
                $query->doesntHave('usuario');
            }
        }

        // Ordenacion
        $sortBy = $request->get('sort_by', 'apellido');
        $sortDir = $request->get('sort_dir', 'asc');
        $allowedSorts = ['cedula', 'nombre', 'apellido', 'departamento', 'cargo', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        $trabajadores = $query->paginate(15)->withQueryString();

        // Estadisticas
        $totalTrabajadores = Trabajador::count();
        $conUsuario = Trabajador::has('usuario')->count();
        $sinUsuario = Trabajador::doesntHave('usuario')->count();
        $departamentos = Trabajador::distinct()->pluck('departamento')->filter()->count();

        // Lista de departamentos unicos para el filtro
        $listaDepartamentos = Trabajador::distinct()->pluck('departamento')->filter()->sort()->values();

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
        $validated = $request->validate([
            'cedula' => ['required', 'string', 'max:20', 'unique:trabajadores,cedula'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'departamento' => ['required', 'string', 'max:100'],
            'cargo' => ['required', 'string', 'max:100'],
            'especialidad' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        $trabajador = Trabajador::create($validated);

        return redirect()->route('admin.trabajadores.index')
            ->with('success', 'Trabajador "' . $trabajador->nombre . ' ' . $trabajador->apellido . '" registrado exitosamente.');
    }

    public function update(Request $request, Trabajador $trabajador)
    {
        $validated = $request->validate([
            'cedula' => ['required', 'string', 'max:20', 'unique:trabajadores,cedula,' . $trabajador->id],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'departamento' => ['required', 'string', 'max:100'],
            'cargo' => ['required', 'string', 'max:100'],
            'especialidad' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        $trabajador->update($validated);

        return redirect()->route('admin.trabajadores.index')
            ->with('success', 'Trabajador actualizado exitosamente.');
    }

    public function destroy(Trabajador $trabajador)
    {
        // Verificar si tiene usuario vinculado
        if ($trabajador->usuario) {
            return back()->with('error', 'No se puede eliminar: el trabajador tiene un usuario vinculado. Elimine primero el usuario.');
        }

        // Verificar que no sea el trabajador del admin logueado
        $usuarioActual = Auth::user();
        if ($usuarioActual && $usuarioActual->trabajador_id === $trabajador->id) {
            return back()->with('error', 'No puedes eliminar tu propio registro de trabajador.');
        }

        $nombre = $trabajador->nombre . ' ' . $trabajador->apellido;
        $trabajador->delete();

        return redirect()->route('admin.trabajadores.index')
            ->with('success', 'Trabajador "' . $nombre . '" eliminado permanentemente.');
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
                    'ultimo_login' => $trabajador->usuario->ultimo_login ? $trabajador->usuario->ultimo_login->format('d/m/Y H:i') : 'Nunca',
                ] : null,
            ]
        ]);
    }
}
