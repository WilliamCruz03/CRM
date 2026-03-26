<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Verificar si el usuario está inactivo (Activo = 0)
            if (!$user->Activo) {
                // Cerrar sesión y redirigir al login con mensaje
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->with('error', 'Tu sesion ha caducado. Contacta al administrador.');
            }
        }
        
        return $next($request);
    }
}