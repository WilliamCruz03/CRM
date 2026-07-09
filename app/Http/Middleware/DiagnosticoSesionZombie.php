<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DiagnosticoSesionZombie
{
    public function handle(Request $request, Closure $next)
    {
        $cookieName = config('session.cookie');
        $tieneCookie = $request->cookie($cookieName) !== null;
        $autenticado = Auth::check();

        // Solo nos interesa el caso raro: hay cookie de sesion valida
        // pero Auth::check() dice que no hay usuario logueado.
        if ($tieneCookie && !$autenticado) {
            $guardName = 'web';
            $authKeyName = 'login_' . $guardName . '_' . sha1(\Illuminate\Auth\SessionGuard::class);

            Log::channel('daily')->warning('SESION ZOMBIE detectada', [
                'session_id' => session()->getId(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'tiene_auth_key' => session()->has($authKeyName),
                'session_keys' => array_keys(session()->all()),
                'ip' => $request->ip(),
                'timestamp_ms' => round(microtime(true) * 1000),
            ]);
        }

        return $next($request);
    }
}