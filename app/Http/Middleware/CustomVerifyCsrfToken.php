<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;

class CustomVerifyCsrfToken extends VerifyCsrfToken
{
    /**
     * Manejar peticiones con token CSRF inválido
     */
    protected function addCookieToResponse($request, $response)
    {
        // Siempre agregar el cookie CSRF
        parent::addCookieToResponse($request, $response);
        return $response;
    }

    /**
     * Personalizar respuesta para errores CSRF
     */
    protected function render($request, $exception)
    {
        // Para peticiones AJAX/API, devolver 419 con información útil
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Token CSRF inválido o expirado',
                'reason' => 'csrf_invalid',
                'requires_login' => true
            ], 419);
        }

        return parent::render($request, $exception);
    }
}