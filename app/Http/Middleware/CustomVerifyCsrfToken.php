<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

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
}