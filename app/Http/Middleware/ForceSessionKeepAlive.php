<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceSessionKeepAlive
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            // Forzar que la sesion no expire
            $request->session()->put('last_activity', time());
            
            // Si la sesion esta marcada para expirar, remover esa marca
            if ($request->session()->has('_expire_on_close')) {
                $request->session()->forget('_expire_on_close');
            }
        }
        
        return $next($request);
    }
}