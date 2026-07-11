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

        return $next($request);
    }
}