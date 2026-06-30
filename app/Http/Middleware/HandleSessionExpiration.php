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
        $excludedRoutes = ['login', 'logout', 'user.check.status', 'api.refresh-csrf', 'notificaciones.cotizaciones'];
        if ($request->routeIs(...$excludedRoutes)) {
            return $next($request);
        }

        // Verificar autenticación
        if (!Auth::check()) {
            Log::info('Sesión expirada', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip()
            ]);

            // Para peticiones AJAX/API - devolver 401 con información clara
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesión expirada. Por favor inicie sesión nuevamente.',
                    'reason' => 'session_expired',
                    'requires_login' => true
                ], 401);
            }

            // Para peticiones normales - redirigir al login
            session()->put('url.intended', $request->fullUrl());
            return redirect()->route('login');
        }

        // Verificar si el usuario está activo (solo si autenticado)
        $user = Auth::user();
        if (!$user->Activo) {
            Log::info('Usuario desactivado', [
                'user_id' => $user->id,
                'usuario' => $user->usuario
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
                    'reason' => 'user_inactive',
                    'requires_login' => true
                ], 403);
            }

            return redirect()->route('login')->with('error', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
        }

        // Actualizar last_activity
        $request->session()->put('last_activity', time());

        return $next($request);
    }
}