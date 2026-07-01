<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        // Solo verificar si el usuario está autenticado
        if (Auth::check()) {
            $user = Auth::user();
            
            // Verificar si está activo
            if (!$user->Activo) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
                        'reason' => 'user_inactive'
                    ], 403);
                }
                
                return redirect()->route('login')->with('error', 'Tu cuenta ha sido desactivada.');
            }
        }
        
        return $next($request);
    }
}