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
        // Excluir rutas específicas
        $excludedRoutes = ['login', 'logout', 'user.check.status', 'api.refresh-csrf'];
        if ($request->routeIs(...$excludedRoutes)) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            
            // Verificar si el usuario está activo
            if (!$user->Activo) {
                Log::info('Usuario desactivado', ['user_id' => $user->id, 'usuario' => $user->usuario]);
                
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Usar 403 para "usuario desactivado" (no es error de autenticación)
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Usuario desactivado. Contacte al administrador.',
                        'reason' => 'user_inactive'
                    ], 403);
                }
                
                return redirect()->route('login')->with('error', 'Usuario desactivado. Contacte al administrador.');
            }
            
            // Verificar si la sesión expiró por inactividad
            $maxLifetime = (int) config('session.lifetime') * 60; // segundos
            $lastActivity = $request->session()->get('last_activity');
            
            if ($lastActivity && (time() - $lastActivity) > $maxLifetime) {
                Log::info('Sesión expirada por inactividad', [
                    'user_id' => $user->id,
                    'last_activity' => $lastActivity,
                    'inactive_seconds' => time() - $lastActivity
                ]);
                
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Para peticiones AJAX/API, devolver JSON
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sesión expirada por inactividad. Vuelva a iniciar sesión.',
                        'reason' => 'session_expired'
                    ], 401);
                }
                
                return redirect()->route('login')->with('error', 'Sesión expirada por inactividad.');
            }
            
            // ACTUALIZAR última actividad (importante)
            $request->session()->put('last_activity', time());
            Log::debug('Last activity updated', ['user_id' => $user->id, 'time' => time()]);
        }
        
        return $next($request);
    }
}