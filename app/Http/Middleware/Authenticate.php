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
        // Excluir rutas de verificación
        if ($request->routeIs('user.check.status')) {
            return $next($request);
        }

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
            
            // Limpiar la sesión de forma segura
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')->with('error', 'Sesión expirada. Por favor inicie sesión nuevamente.');
        }

        return $next($request);
    }
}