<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class KeepSessionAlive
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            // Solo mantener la sesión activa, sin expiración por inactividad
            $request->session()->put('last_activity', time());
        }
        
        return $next($request);
    }
}