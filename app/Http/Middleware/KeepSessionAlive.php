<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class KeepSessionAlive
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            // Renovar la sesión periódicamente
            $request->session()->put('last_activity', time());
        }
        
        return $next($request);
    }
}