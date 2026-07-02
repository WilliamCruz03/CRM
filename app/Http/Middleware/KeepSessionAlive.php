<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KeepSessionAlive
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            // Actualizar last_activity
            $request->session()->put('last_activity', time());
            
            // Log cada 10 minutos para verificar que el heartbeat funciona
            $lastHeartbeatLog = session()->get('last_heartbeat_log', 0);
            if (time() - $lastHeartbeatLog > 600) { // 10 minutos
                Log::info('Heartbeat - Sesión activa', [
                    'user_id' => auth()->user()->id,
                    'usuario' => auth()->user()->usuario,
                    'session_id' => session()->getId(),
                    'last_activity' => session()->get('last_activity')
                ]);
                session()->put('last_heartbeat_log', time());
            }
        }
        
        return $next($request);
    }
}