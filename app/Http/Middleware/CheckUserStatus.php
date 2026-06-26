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
            
            // Verificar si el usuario está activo
            if (!$user->Activo) {
                Log::info('Usuario desactivado', ['user_id' => $user->id, 'usuario' => $user->usuario]);
                
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return $this->handleUnauthorized($request, 'Tu sesión a caducado. Si cree que esto es un error contacte al administrador.');
            }
            
            // ============================================
            // VERIFICACIÓN DE SESIÓN POR INACTIVIDAD
            // ============================================
            // Solo verificar si hay una última actividad registrada
            $lastActivity = $request->session()->get('last_activity');
            
            if ($lastActivity !== null) {
                $sessionLifetime = config('session.lifetime') * 60; // Convertir a segundos
                
                // Si la sesión ha expirado por inactividad
                if (time() - $lastActivity > $sessionLifetime) {
                    Log::info('Sesión expirada por inactividad', ['user_id' => $user->id]);
                    
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    return $this->handleUnauthorized($request, 'Sesión expirada. Por favor inicie sesión nuevamente.');
                }
            }
            
            // Actualizar última actividad SIEMPRE
            $request->session()->put('last_activity', time());
            
        } else {
            // Usuario no autenticado - sesión expirada
            Log::debug('Sesión expirada o usuario no autenticado', ['url' => $request->fullUrl()]);
            
            // Para peticiones AJAX/API
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesión expirada. Por favor inicie sesión nuevamente.',
                    'reason' => 'session_expired'
                ], 401);
            }
            
            // Guardar la URL a la que intentaba acceder
            session()->put('url.intended', $request->fullUrl());
            
            // NO invalidar la sesión aquí para evitar el error 500
            // Solo redirigir al login
            return redirect()->route('login')->with('error', 'Sesión expirada. Por favor inicie sesión nuevamente.');
        }
        
        return $next($request);
    }
    
    private function handleUnauthorized(Request $request, $message)
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'reason' => 'unauthorized'
            ], 403);
        }
        
        return redirect()->route('login')->with('error', $message);
    }
}