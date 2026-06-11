<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Authenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            Log::debug('Usuario no autenticado', ['url' => $request->fullUrl()]);
            
            // Para peticiones AJAX/API
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado. Por favor inicie sesión.',
                    'reason' => 'unauthenticated'
                ], 401);
            }
            
            // Guardar la URL a la que intentaba acceder
            session()->put('url.intended', $request->fullUrl());
            
            return redirect()->route('login');
        }

        return $next($request);
    }
}