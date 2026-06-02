<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CambiarPasswordController extends Controller
{
    public function showChangeForm()
    {
        return view('auth.change-password');
    }

    public function change(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $usuario = Auth::user();

        if (!Hash::check($request->current_password, $usuario->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }

        $usuario->password = Hash::make($request->new_password);
        $usuario->must_change_password = false;
        $usuario->save();

        return redirect()->route('admin.dashboard')->with('status', 'Contraseña actualizada exitosamente.');
    }
}
