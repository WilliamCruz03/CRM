<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middlewares personalizados agregados al final del stack 'web'
        // (el stack default de Laravel ya incluye StartSession, CSRF, etc.)
        $middleware->web(append: [
            \App\Http\Middleware\PreventBackCache::class,
            \App\Http\Middleware\DisableBfCache::class,
            \App\Http\Middleware\DiagnosticoSesionZombie::class,
            \App\Http\Middleware\HandleSessionExpiration::class,
        ]);

        // Reemplaza el VerifyCsrfToken nativo por la version personalizada
        $middleware->web(replace: [
            ValidateCsrfToken::class => \App\Http\Middleware\CustomVerifyCsrfToken::class,
        ]);

        // NOTA: no se registra ningun alias 'check.activo' porque
        // CheckUserStatus fue eliminado (era redundante con
        // HandleSessionExpiration, que ya valida el usuario activo)
        // y ninguna ruta lo referencia.
    })
    ->withSchedule(function (Schedule $schedule) {
        // Cancelar cotizaciones vencidas - cada hora entre 7 AM y 9 PM
        $schedule->command('cotizaciones:cancelar-vencidas')
            ->hourly()
            ->between('7:00', '21:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejo explicito de token CSRF invalido/expirado (419)
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'reason' => 'csrf_invalid',
                    'requires_login' => true,
                    'message' => 'Tu sesión expiró. Intenta de nuevo.',
                ], 419);
            }

            return redirect()->route('login')
                ->with('error', 'Tu sesión expiró, por favor inicia sesión de nuevo.');
        });
    })->create();