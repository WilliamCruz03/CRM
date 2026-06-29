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
            // Extender la sesión con cada petición
            $request->session()->put('last_activity', time());
        }
        return $next($request);
    }
}