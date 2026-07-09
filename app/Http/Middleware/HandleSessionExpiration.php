<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HandleSessionExpiration
{
    public function handle(Request $request, Closure $next)
    {
        // Rutas excluidas (login, logout, check-status, refresh-csrf)
        $excludedRoutes = ['login', 'login.post', 'logout', 'user.check.status', 'api.refresh-csrf', 'notificaciones.cotizaciones'];
        if ($request->routeIs(...$excludedRoutes) || $request->is('login', 'logout')) {
            return $next($request);
        }

        // Verificar autenticación
        if (!Auth::check()) {
            // Calcular la clave de autenticacion de sesion correctamente
            $authKeyName = 'login_web_' . sha1(\Illuminate\Auth\SessionGuard::class);
            
            Log::warning('HandleSessionExpiration: Usuario no autenticado', [
                'url' => $request->fullUrl(),
                'session_id' => session()->getId(),
                'ajax' => $request->ajax(),
                'expectsJson' => $request->expectsJson(),
                'has_auth_key' => session()->has($authKeyName),
                'session_keys' => array_keys(session()->all())
            ]);

            Log::info('Sesion expirada detectada', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => session()->getId(),
                'has_auth_key' => session()->has($authKeyName),
                'has_token' => session()->has('_token')
            ]);

            // Para peticiones AJAX/API - devolver 401 con información clara
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesión expirada. Por favor inicie sesión nuevamente.',
                    'reason' => 'session_expired',
                    'requires_login' => true,
                    'session_id' => session()->getId()
                ], 401);
            }

            // Para peticiones normales - redirigir al login
            session()->put('url.intended', $request->fullUrl());
            return redirect()->route('login');
        }

        // Verificar si el usuario está activo
        $user = Auth::user();
        if (!$user->Activo) {
            Log::warning('Usuario desactivado', [
                'user_id' => $user->id,
                'usuario' => $user->usuario,
                'ip' => $request->ip()
            ]);

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

        // Actualizar last_activity
        $request->session()->put('last_activity', time());

        // Log de sesión activa (solo cada 5 minutos para no llenar logs)
        $lastLog = session()->get('last_log_time', 0);
        if (time() - $lastLog > 300) { // 5 minutos
            Log::info('Sesión activa', [
                'user_id' => $user->id,
                'usuario' => $user->usuario,
                'session_id' => session()->getId(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip()
            ]);
            session()->put('last_log_time', time());
        }

        return $next($request);
    }
}