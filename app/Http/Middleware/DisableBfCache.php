<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableBfCache
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Deshabilitar bfcache para todas las páginas
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
        
        // También deshabilitar bfcache con el header específico de Chrome
        $response->headers->set('Permissions-Policy', 'unload=()');
        
        return $response;
    }
}