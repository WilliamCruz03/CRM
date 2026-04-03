<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventBackHistory
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Headers anti-caché más agresivos
        return $response->header('Cache-Control', 'no-cache, no-store, must-revalidate, private')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT')
                        ->header('X-Content-Type-Options', 'nosniff');
    }
}