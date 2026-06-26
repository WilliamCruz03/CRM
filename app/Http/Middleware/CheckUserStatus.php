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
        // Excluir rutas de login/logout y verificación de estado
        $excludedRoutes = ['login', 'logout', 'user.check.status'];
        if ($request->routeIs(...$excludedRoutes)) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            
            // SOLO verificar si el usuario está activo
            if (!$user->Activo) {
                Log::info('Usuario desactivado', ['user_id' => $user->id, 'usuario' => $user->usuario]);
                
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
                
                return redirect()->route('login')->with('error', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
            }
            
            // Solo mantener la sesión activa, sin verificar inactividad
            $request->session()->put('last_activity', time());
            
        } else {
            // Usuario no autenticado
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesión expirada. Por favor inicie sesión nuevamente.',
                    'reason' => 'session_expired'
                ], 401);
            }
            
            // Guardar la URL a la que intentaba acceder
            session()->put('url.intended', $request->fullUrl());
            // Solo redirigir al login
            return redirect()->route('login');
        }
        
        return $next($request);
    }
}