<?php
// app/Http/Middleware/ForceAppKey.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceAppKey
{
    public function handle(Request $request, Closure $next)
    {
        $appKey = 'base64:egKn4akqF+VoQKWm893L4WdtIGLpqiPot3PZhWgoIYM=';
        
        // FORZAR EN TODOS LOS LUGARES POSIBLES
        if (!isset($_ENV['APP_KEY']) || $_ENV['APP_KEY'] !== $appKey) {
            $_ENV['APP_KEY'] = $appKey;
            $_SERVER['APP_KEY'] = $appKey;
            putenv('APP_KEY=' . $appKey);
            
            // Forzar en la configuración
            config(['app.key' => $appKey]);
            
            // Log para debugging
            \Log::debug('ForceAppKey: APP_KEY forzada', [
                'env_key' => $_ENV['APP_KEY'] ?? 'NO',
                'config_key' => config('app.key')
            ]);
        }
        
        return $next($request);
    }
}