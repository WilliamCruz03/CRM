<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Reemplaza a Authenticate.php Y HandleSessionExpiration.php.
 * Unico punto de verdad para autenticacion + expiracion de sesion.
 *
 * IMPORTANTE: si aplicas este middleware, quita 'auth' de tus
 * grupos de rutas en web.php y usa este en su lugar, para no
 * tener dos middlewares evaluando lo mismo dos veces.
 */
class AuthenticateSession
{
    protected array $rutasPublicas = [
        'login',
        'login.post',
        'logout',
        'user.session.ping',   // endpoint unificado (antes user.check.status + keep-alive)
        'api.refresh-csrf',
        'notificaciones.cotizaciones',
    ];

    public function handle(Request $request, Closure $next)
    {
        if ($this->esRutaPublica($request)) {
            return $next($request);
        }

        if (!Auth::check()) {
            return $this->manejarNoAutenticado($request);
        }

        if (!Auth::user()->Activo) {
            return $this->manejarUsuarioInactivo($request);
        }

        // ==========================================
        // THROTTLE: Solo escribir en sesión cada 15 segundos como máximo
        // Esto evita que múltiples peticiones concurrentes escriban al mismo tiempo
        // ==========================================
        $ultimaEscritura = session()->get('last_activity', 0);
        $ahora = time();
        if ($ahora - $ultimaEscritura > 15) {
            session()->put('last_activity', $ahora);
        }

        return $next($request);
    }

    private function esRutaPublica(Request $request): bool
    {
        return $request->routeIs(...$this->rutasPublicas);
    }

    private function manejarNoAutenticado(Request $request)
    {
        Log::info('Sesión no autenticada', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
        ]);

        // Guardar a donde iba el usuario, igual que hace el
        // middleware 'auth' nativo de Laravel, para regresarlo
        // ahi despues de loguearse de nuevo.
        if (!$request->ajax() && !$request->expectsJson()) {
            session()->put('url.intended', $request->fullUrl());
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión expirada. Por favor inicia sesión nuevamente.',
                'reason' => 'session_expired',
                'requires_login' => true,
            ], 401);
        }

        return redirect()->route('login')
            ->with('error', 'Sesión expirada. Inicia sesión nuevamente.');
    }

    private function manejarUsuarioInactivo(Request $request)
    {
        $user = Auth::user();

        Log::warning('Usuario desactivado', [
            'user_id' => $user->getKey(),
            'usuario' => $user->usuario ?? null,
            'ip' => $request->ip(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
                'reason' => 'user_inactive',
            ], 403);
        }

        return redirect()->route('login')
            ->with('error', 'Tu cuenta ha sido desactivada.');
    }
}