<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificarPermisoModulo
{
    public function handle(Request $request, Closure $next, $modulo, $accion = 'ver')
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Verificar si tiene algún permiso
        if (!$user->tieneAlgunPermiso()) {
            return redirect()->route('dashboard.sin-acceso');
        }

        // Verificar permiso específico
        $metodo = "tieneAccesoAModulo";
        if (!$user->$metodo($modulo)) {
            abort(403, 'No tienes permiso para acceder a este módulo');
        }

        return $next($request);
    }
}