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

        $user = PersonalEmpresa::where('usuario', $credentials['usuario'])
                                ->where('Activo', 1)
                                ->first();

        if ($user && Hash::check($credentials['password'], $user->passw)) {
            Auth::loginUsingId($user->id_personal_empresa);
            $request->session()->regenerate();
            
            // Redirigir al dashboard
            return redirect()->route('dashboard.index');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden.',
        ])->onlyInput('usuario');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}