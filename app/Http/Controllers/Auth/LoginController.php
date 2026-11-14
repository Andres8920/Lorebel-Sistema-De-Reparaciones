<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Inicio y cierre de sesión. Los eventos Login / Logout / Failed que dispara
 * Laravel son capturados en AppServiceProvider para la auditoría de sesiones.
 */
class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credenciales = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Ingrese su email.',
            'email.email'       => 'El email no tiene un formato válido.',
            'password.required' => 'Ingrese su contraseña.',
        ]);

        // Solo usuarios activos pueden ingresar.
        $credenciales['activo'] = true;

        if (! Auth::attempt($credenciales, $request->boolean('recordar'))) {
            throw ValidationException::withMessages([
                'email' => 'Credenciales incorrectas o usuario inactivo.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('ordenes.index'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }
}
