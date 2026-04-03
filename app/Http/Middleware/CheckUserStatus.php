<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        // Verificar si la ruta es de login (para no bloquear)
        if ($request->routeIs('login') || $request->routeIs('logout')) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            
            if (!$user->Activo) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->with('error', 'Tu sesión ha caducado. Contacta al administrador.');
            }
        }
        
        return $next($request);
    }
}