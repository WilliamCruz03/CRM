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
        return view('auth.login');
    }

    public function login(Request $request)
    {
        Log::info('=== INTENTO DE LOGIN ===');
        
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        // Buscar usuario
        $user = PersonalEmpresa::where('usuario', $credentials['usuario'])->first();

        // Usuario no existe
        if (!$user) {
            Log::warning('Usuario no encontrado', ['usuario' => $credentials['usuario']]);
            return back()->withErrors(['usuario' => 'Las credenciales no coinciden.'])->onlyInput('usuario');
        }

        // Usuario inactivo
        if ($user->Activo == 0) {
            Log::warning('Usuario inactivo', ['usuario' => $user->usuario, 'id' => $user->id]);
            return back()->withErrors([
                'usuario' => 'Tu sesion ha caducado. Dudas o aclaraciones favor de comunicarse al area de TICS.',
            ])->onlyInput('usuario');
        }

        // Contraseña incorrecta
        if (!Hash::check($credentials['password'], $user->passw)) {
            Log::warning('Contraseña incorrecta', ['usuario' => $user->usuario]);
            return back()->withErrors(['usuario' => 'Contraseña incorrecta.'])->onlyInput('usuario');
        }

        // Login correcto
        Auth::login($user);
        $request->session()->regenerate();
        
        // Verificar que el usuario está autenticado
        Log::info('Usuario autenticado correctamente', [
            'usuario' => $user->usuario,
            'id' => $user->id,
            'auth_check' => Auth::check() ? 'true' : 'false',
            'session_id' => session()->getId()
        ]);
        
        // IMPORTANTE: Inicializar last_activity
        $request->session()->put('last_activity', time());
        $request->session()->put('last_renewal', time());
        
        Log::info('Sesión inicializada', [
            'last_activity' => $request->session()->get('last_activity'),
            'session_id' => session()->getId()
        ]);

        return redirect()->route('dashboard.index');
    }

    public function logout(Request $request)
    {
        Log::info('=== LOGOUT ===', ['user_id' => Auth::id()]);
        
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