<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DiagnosticoSesionZombie
{
    // Rutas donde NO estar autenticado es normal y esperado.
    // En estas, un guest legitimo siempre dara "tiene_auth_key: false"
    // y no queremos ruido de falsos positivos.
    protected array $rutasPublicas = ['login', 'login.post', 'logout', 'api.refresh-csrf'];

    public function handle(Request $request, Closure $next)
    {
        if ($request->routeIs(...$this->rutasPublicas)) {
            return $next($request);
        }

        $cookieName = config('session.cookie');
        $tieneCookie = $request->cookie($cookieName) !== null;
        $autenticado = Auth::check();

        // Aqui SI es la señal real: estamos en una ruta protegida
        // (requiere auth), hay cookie de sesion, pero de repente
        // Auth::check() dice que no hay usuario logueado.
        if ($tieneCookie && !$autenticado) {
            $guardName = 'web';
            $authKeyName = 'login_' . $guardName . '_' . sha1(\Illuminate\Auth\SessionGuard::class);

            Log::channel('daily')->warning('SESION ZOMBIE detectada', [
                'session_id' => session()->getId(),
                'url' => $request->fullUrl(),
                'ruta_nombre' => optional($request->route())->getName(),
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