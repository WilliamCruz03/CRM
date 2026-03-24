<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PersonalEmpresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        // Buscar usuario
        $user = PersonalEmpresa::where('usuario', $credentials['usuario'])->first();

        // Usuario no existe
        if (!$user) {
            return back()->withErrors([
                'usuario' => 'Las credenciales no coinciden.',
            ])->onlyInput('usuario');
        }

        // Usuario inactivo
        if ($user->Activo == 0) {
            return back()->withErrors([
                'usuario' => 'Tu sesion ha caducado. Dudas o aclaraciones favor de comunicarse al area de TICS.',
            ])->onlyInput('usuario');
        }

        // Contraseña incorrecta
        if (!Hash::check($credentials['password'], $user->passw)) {
            return back()->withErrors([
                'usuario' => 'Las credenciales no coinciden.',
            ])->onlyInput('usuario');
        }

        // Login correcto
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();  //Invalida la sesion actual
        $request->session()->regenerateToken();  // Regenera el token CSRF

        return redirect('/login');
    }
}