<?php
// app/Http/Middleware/KeepSessionAlive.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class KeepSessionAlive
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            // Actualizar last_activity con cada petición
            $request->session()->put('last_activity', time());
            
            // Extender la vida de la sesión en cada petición (opcional)
            // Esto ayuda a mantener la sesión viva en servidores con gc agresivo
            if (method_exists($request->session(), 'setExpiration')) {
                // Si el driver de sesión soporta extensión
            }
        }
        
        return $next($request);
    }
}