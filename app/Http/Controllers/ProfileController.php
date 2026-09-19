<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Trabajador;

class ProfileController extends Controller
{
    // Mostrar perfil del usuario
    public function index()
    {
        $user = Auth::user();

        if (!$user->rol) {
            session()->flash('role_warning', 'No tienes un rol asignado. Por favor, contacta al administrador.');
        }

        return view('profile.index');
    }

    // ============================================================
    // API: Obtener datos del perfil (JSON) — para el modal
    // ============================================================
    public function getData()
    {
        $user = Auth::user();
        $user->load('trabajador', 'rol');

        return response()->json([
            'success' => true,
            'data' => [
                'id'              => $user->id,
                'usuario'         => $user->usuario,
                'nombre'          => $user->trabajador?->nombre ?? '',
                'apellido'        => $user->trabajador?->apellido ?? '',
                'cedula'          => $user->trabajador?->cedula ?? '',
                'email'           => $user->trabajador?->email ?? '',
                'telefono'        => $user->trabajador?->telefono ?? '',
                'departamento'    => $user->trabajador?->departamento ?? '',
                'cargo'           => $user->trabajador?->cargo ?? '',
                'especialidad'    => $user->trabajador?->especialidad ?? '',
                'rol'             => $user->rol?->nombre ?? 'Sin rol',
                'status'          => $user->status,
                'ultimo_login'    => $user->ultimo_login?->format('d/m/Y H:i') ?? 'Nunca',
                'foto_perfil_url' => $user->foto_perfil_url,
                'tiene_foto'      => !empty($user->foto_perfil),
            ]
        ]);
    }

    // ============================================================
    // Actualizar información personal (nombre, apellido, email, teléfono, cargo)
    // ============================================================
    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nombre'       => 'required|string|max:100',
            'apellido'     => 'required|string|max:100',
            'email'        => [
                'nullable', 'email', 'max:100',
                Rule::unique('trabajadores', 'email')->ignore($user->trabajador_id)
            ],
            'telefono'     => 'nullable|string|max:20',
            'departamento' => 'nullable|string|max:100',
            'cargo'        => 'nullable|string|max:100',
            'especialidad' => 'nullable|string|max:255',
        ]);

        try {
            if ($user->trabajador) {
                $user->trabajador->update($validated);
            } else {
                // Si por alguna razón no tiene trabajador, crear uno mínimo
                $trabajador = Trabajador::create([
                    'cedula'     => $request->cedula ?? 'S/N-' . $user->id,
                    'nombre'     => $validated['nombre'],
                    'apellido'   => $validated['apellido'],
                    'departamento' => $validated['departamento'] ?? 'Informática',
                    'cargo'      => $validated['cargo'] ?? 'Usuario',
                    'email'      => $validated['email'] ?? null,
                    'telefono'   => $validated['telefono'] ?? null,
                ]);
                $user->trabajador_id = $trabajador->id;
                $user->save();
            }

            Log::info('Perfil actualizado', ['user_id' => $user->id]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Información actualizada correctamente.',
                    'data'    => [
                        'nombre'          => $user->fresh()->trabajador->nombre,
                        'apellido'        => $user->fresh()->trabajador->apellido,
                        'nombre_completo' => $user->fresh()->nombre_completo,
                    ]
                ]);
            }

            return redirect()->route('profile.index')
                ->with('profile_success', 'Información actualizada correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al actualizar perfil: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('profile_error', $e->getMessage());
        }
    }

    // ============================================================
    // Subir foto de perfil
    // ============================================================
    public function updateFoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = Auth::user();

        try {
            // Eliminar foto anterior si existe
            if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
                Storage::disk('public')->delete($user->foto_perfil);
            }

            // Guardar nueva foto
            $path = $request->file('foto')->store('perfiles', 'public');

            $user->foto_perfil = $path;
            $user->save();

            Log::info('Foto de perfil actualizada', ['user_id' => $user->id, 'path' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Foto de perfil actualizada correctamente.',
                'foto_url' => $user->fresh()->foto_perfil_url,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al subir foto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la foto: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // Eliminar foto de perfil
    // ============================================================
    public function deleteFoto()
    {
        $user = Auth::user();

        try {
            if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
                Storage::disk('public')->delete($user->foto_perfil);
            }

            $user->foto_perfil = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Foto eliminada correctamente.',
                'foto_url' => $user->fresh()->foto_perfil_url,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // Cambiar contraseña
    // ============================================================
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta.'
                ], 422);
            }
            return redirect()->route('profile.index')
                ->with('password_error', 'La contraseña actual es incorrecta.');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        Log::info('Contraseña actualizada', ['user_id' => $user->id]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada correctamente.'
            ]);
        }

        return redirect()->route('profile.index')
            ->with('password_success', 'Contraseña actualizada correctamente.');
    }

    // ============================================================
    // Actualizar preguntas de seguridad (se mantiene del original)
    // ============================================================
    public function updateSecurity(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'pregunta_seguridad_1' => 'required|string',
            'respuesta_1'          => 'required|string',
            'pregunta_seguridad_2' => 'required|string',
            'respuesta_2'          => 'required|string',
        ]);

        $user->pregunta_seguridad_1 = $request->pregunta_seguridad_1;
        $user->respuesta_1 = Hash::make(strtolower(trim($request->respuesta_1)));
        $user->pregunta_seguridad_2 = $request->pregunta_seguridad_2;
        $user->respuesta_2 = Hash::make(strtolower(trim($request->respuesta_2)));
        $user->save();

        Log::info('Preguntas de seguridad actualizadas', ['user_id' => $user->id]);

        return redirect()->route('profile.index')
            ->with('security_success', 'Preguntas de seguridad actualizadas correctamente.');
    }
}