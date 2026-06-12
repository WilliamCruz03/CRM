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
            
            // ACTUALIZAR última actividad (importante)
            $request->session()->put('last_activity', time());
            Log::debug('Last activity updated', ['user_id' => $user->id, 'time' => time()]);
        }
        
        return $next($request);
    }
}