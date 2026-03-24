<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserActivo
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Si el usuario está inactivo
            if ($user->Activo == 0) {

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'usuario' => 'Tu sesion ha caducado. Dudas o aclaraciones favor de comunicarse al area de TICS.'
                ]);
            }
        }

        return $next($request);
    }
}