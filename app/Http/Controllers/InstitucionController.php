<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstitucionController extends Controller
{
    // Obtener el responsable de una institución
    public function getResponsable($id)
    {
        try {
            $institucion = Institucion::with('responsable')->find($id);

            if (!$institucion) {
                return response()->json(['responsable' => null]);
            }

            return response()->json(['responsable' => $institucion->responsable]);

        } catch (\Exception $e) {
            Log::error('Error en getResponsable de institucion: ' . $e->getMessage());
            return response()->json(['responsable' => null, 'error' => $e->getMessage()], 500);
        }
    }

    // Actualizar o crear responsable de una institución
    public function updateResponsable(Request $request, $id)
    {
        try {
            $institucion = Institucion::findOrFail($id);

            $request->validate([
                'nombre' => 'required|string|max:255',
                'cargo' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'responsable_id' => 'nullable|exists:responsable,id'
            ]);

            // Si ya tiene un responsable asociado, actualizarlo
            if ($institucion->responsable_id) {
                $responsable = Responsable::find($institucion->responsable_id);
                if ($responsable) {
                    $responsable->update([
                        'nombre' => $request->nombre,
                        'departamento' => $request->cargo,
                        'telefono' => $request->telefono,
                        'email' => $request->email,
                    ]);
                } else {
                    // Si el responsable no existe, crear uno nuevo
                    $responsable = Responsable::create([
                        'nombre' => $request->nombre,
                        'departamento' => $request->cargo,
                        'telefono' => $request->telefono,
                        'email' => $request->email,
                        'tipo' => 'externo'
                    ]);
                    $institucion->responsable_id = $responsable->id;
                    $institucion->save();
                }
            }
            // Si viene un responsable_id en la petición
            elseif ($request->responsable_id) {
                $institucion->responsable_id = $request->responsable_id;
                $institucion->save();

                $responsable = Responsable::find($request->responsable_id);
                if ($responsable) {
                    $responsable->update([
                        'nombre' => $request->nombre,
                        'departamento' => $request->cargo,
                        'telefono' => $request->telefono,
                        'email' => $request->email,
                    ]);
                }
            }
            // Si no tiene responsable, crear uno nuevo
            else {
                $responsable = Responsable::create([
                    'nombre' => $request->nombre,
                    'departamento' => $request->cargo,
                    'telefono' => $request->telefono,
                    'email' => $request->email,
                    'tipo' => 'externo'
                ]);
                $institucion->responsable_id = $responsable->id;
                $institucion->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Responsable actualizado correctamente',
                'responsable' => $responsable ?? $institucion->responsable
            ]);

        } catch (\Exception $e) {
            Log::error('Error en updateResponsable de institucion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }
}
