<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SessionDebugLogger
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Registrar el estado de la sesión ANTES de procesar
        $this->logSessionState('ANTES', $request);
        
        // 2. Procesar la petición
        $response = $next($request);
        
        // 3. Registrar el estado de la sesión DESPUÉS de procesar
        $this->logSessionState('DESPUES', $request);
        
        return $response;
    }
    
    private function logSessionState($momento, $request)
    {
        $sessionId = session()->getId();
        $isAuthenticated = Auth::check();
        $user = Auth::user();
        
        // Obtener todas las variables de sesión (sin datos sensibles)
        $sessionData = session()->all();
        $sessionKeys = array_keys($sessionData);
        
        // Obtener información del archivo de sesión (si es file)
        $sessionFile = null;
        $sessionFileExists = false;
        $sessionFileSize = null;
        $sessionFileModified = null;
        
        if (config('session.driver') === 'file') {
            $sessionPath = config('session.files');
            $sessionFile = $sessionPath . '/' . $sessionId;
            $sessionFileExists = file_exists($sessionFile);
            if ($sessionFileExists) {
                $sessionFileSize = filesize($sessionFile);
                $sessionFileModified = date('Y-m-d H:i:s', filemtime($sessionFile));
            }
        }
        
        // Obtener información de la tabla de sesiones (si es database)
        $sessionDbRecord = null;
        if (config('session.driver') === 'database') {
            try {
                $sessionDbRecord = \DB::table(config('session.table', 'sessions'))
                    ->where('id', $sessionId)
                    ->first();
            } catch (\Exception $e) {
                $sessionDbRecord = 'Error: ' . $e->getMessage();
            }
        }
        
        // Obtener información de la cookie
        $cookieName = config('session.cookie');
        $cookieValue = $request->cookie($cookieName);
        
        // Log detallado
        Log::channel('daily')->debug('🔍 SESION DEBUG - ' . $momento, [
            'session_id' => $sessionId,
            'is_authenticated' => $isAuthenticated,
            'user_id' => $user ? $user->id : null,
            'usuario' => $user ? $user->usuario : null,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_keys' => $sessionKeys,
            'session_has_token' => session()->has('_token'),
            'session_has_last_activity' => session()->has('last_activity'),
            'last_activity' => session()->get('last_activity'),
            'time_now' => time(),
            'time_since_last_activity' => session()->has('last_activity') ? (time() - session()->get('last_activity')) : null,
            'session_driver' => config('session.driver'),
            'session_lifetime' => config('session.lifetime'),
            'session_expire_on_close' => config('session.expire_on_close'),
            'session_lottery' => config('session.lottery'),
            'session_file' => $sessionFile,
            'session_file_exists' => $sessionFileExists,
            'session_file_size' => $sessionFileSize,
            'session_file_modified' => $sessionFileModified,
            'session_db_record' => $sessionDbRecord ? 'EXISTS' : 'NOT FOUND',
            'session_db_payload' => $sessionDbRecord ? substr($sessionDbRecord->payload ?? '', 0, 100) : null,
            'session_db_last_activity' => $sessionDbRecord ? $sessionDbRecord->last_activity : null,
            'cookie_name' => $cookieName,
            'cookie_value' => $cookieValue ? 'SET' : 'NOT SET',
            'cookie_value_preview' => $cookieValue ? substr($cookieValue, 0, 20) : null,
        ]);
    }
}