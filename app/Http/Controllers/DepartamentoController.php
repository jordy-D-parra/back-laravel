<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DepartamentoController extends Controller
{
    public function getResponsable($id)
    {
        try {
            $departamento = Departamento::with('responsable')->find($id);

            if (!$departamento) {
                return response()->json(['responsable' => null]);
            }

            return response()->json(['responsable' => $departamento->responsable]);

        } catch (\Exception $e) {
            Log::error('Error en getResponsable de departamento: ' . $e->getMessage());
            return response()->json(['responsable' => null], 500);
        }
    }

    public function updateResponsable(Request $request, $id)
    {
        try {
            $departamento = Departamento::findOrFail($id);

            $request->validate([
                'nombre' => 'required|string|max:255',
                'cargo' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'responsable_id' => 'nullable|exists:responsable,id'
            ]);

            // Si ya tiene un responsable asociado
            if ($departamento->responsable_id) {
                $responsable = Responsable::find($departamento->responsable_id);
                if ($responsable) {
                    $responsable->update([
                        'nombre' => $request->nombre,
                        'departamento' => $request->cargo,
                        'telefono' => $request->telefono,
                        'email' => $request->email,
                    ]);
                } else {
                    $responsable = Responsable::create([
                        'nombre' => $request->nombre,
                        'departamento' => $request->cargo,
                        'telefono' => $request->telefono,
                        'email' => $request->email,
                        'tipo' => 'interno'
                    ]);
                    $departamento->responsable_id = $responsable->id;
                    $departamento->save();
                }
            }
            // Si viene un responsable_id en la petición
            elseif ($request->responsable_id) {
                $departamento->responsable_id = $request->responsable_id;
                $departamento->save();

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
                    'tipo' => 'interno'
                ]);
                $departamento->responsable_id = $responsable->id;
                $departamento->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Responsable actualizado correctamente',
                'responsable' => $responsable ?? $departamento->responsable
            ]);

        } catch (\Exception $e) {
            Log::error('Error en updateResponsable de departamento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }
}
