<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        // Solo excluir rutas de login/logout
        if ($request->routeIs('login') || $request->routeIs('logout')) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            
            if (!$user->Activo) {
                Log::info('Usuario desactivado', ['user_id' => $user->id, 'usuario' => $user->usuario]);
                
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Usuario desactivado. Contacte al administrador.',
                        'reason' => 'user_inactive'
                    ], 403);
                }
                
                return redirect()->route('login')->with('error', 'Usuario desactivado. Contacte al administrador.');
            }
        }
        
        return $next($request);
    }
}