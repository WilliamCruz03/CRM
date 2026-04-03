<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAjaxSession
{
    public function handle(Request $request, Closure $next)
    {
        // Verificar para todas las peticiones AJAX/JSON
        if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tu sesión ha caducado. Por favor, vuelve a iniciar sesión.',
                    'redirect' => route('login')
                ], 401);
            }
        }

        return $next($request);
    }
}