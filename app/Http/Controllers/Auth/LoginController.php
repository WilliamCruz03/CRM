<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PersonalEmpresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Log: Visitando página de login
        Log::info('Accediendo a página de login', [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Log: Intento de login
        Log::info('Intento de login', [
            'usuario' => $request->usuario,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $credentials = $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        // Buscar usuario
        $user = PersonalEmpresa::where('usuario', $credentials['usuario'])->first();

        // Usuario no existe
        if (!$user) {
            Log::warning('Login fallido - Usuario no existe', [
                'usuario' => $credentials['usuario'],
                'ip' => $request->ip()
            ]);
            return back()->withErrors(['usuario' => 'Las credenciales no coinciden.'])->onlyInput('usuario');
        }

        // Usuario inactivo
        if ($user->Activo == 0) {
            Log::warning('Login fallido - Usuario inactivo', [
                'usuario' => $user->usuario,
                'id_personal_empresa' => $user->id,
                'ip' => $request->ip()
            ]);
            return back()->withErrors([
                'usuario' => 'Tu sesion ha caducado. Dudas o aclaraciones favor de comunicarse al area de TICS.',
            ])->onlyInput('usuario');
        }

        // Contraseña incorrecta
        if (!Hash::check($credentials['password'], $user->passw)) {
            Log::warning('Login fallido - Contraseña incorrecta', [
                'usuario' => $user->usuario,
                'id_personal_empresa' => $user->id,
                'ip' => $request->ip()
            ]);
            return back()->withErrors(['usuario' => 'Contraseña incorrecta.'])->onlyInput('usuario');
        }

        // Login correcto
        Auth::login($user);
        $request->session()->regenerate();
        
        // IMPORTANTE: Inicializar last_activity y last_renewal
        $request->session()->put('last_activity', time());
        $request->session()->put('last_renewal', time());
        
        // Log: Login exitoso
        Log::info('Login exitoso', [
            'id_personal_empresa' => $user->id,
            'usuario' => $user->usuario,
            'email' => $user->email ?? 'N/A',
            'ip' => $request->ip(),
            'session_id' => session()->getId(),
            'last_activity' => session()->get('last_activity'),
            'last_renewal' => session()->get('last_renewal')
        ]);

        return redirect()->route('dashboard.index');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log: Cierre de sesión
        Log::info('Cierre de sesión', [
            'id_personal_empresa' => $user?->id,
            'usuario' => $user?->usuario,
            'session_id' => session()->getId(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Cerrar sesión del usuario
        Auth::logout();
        
        // Invalidar la sesión actual
        $request->session()->invalidate();
        
        // Regenerar token para la próxima solicitud
        $request->session()->regenerateToken();
        
        // Redirigir al login
        return redirect('/login');
    }
}