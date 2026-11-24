<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
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
        $exceptions->render(function (\Throwable $exception, Request $request) {
            // Conserva los flujos nativos de autenticación, validación y respuestas explícitas.
            if ($exception instanceof \Illuminate\Auth\AuthenticationException
                || $exception instanceof \Illuminate\Validation\ValidationException
                || $exception instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                return null;
            }

            $status = $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            if ($status === 419) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'reason' => 'csrf_invalid',
                        'requires_login' => true,
                        'message' => 'Tu sesión expiró. Intenta de nuevo.',
                    ], 419)->header('Cache-Control', 'no-store');
                }

                return response()->view('errors.sesion-expirada', [], 419)
                    ->header('Cache-Control', 'no-store');
            }

            if ($status !== 500) {
                return null;
            }

            $errorSesion = $exception instanceof \ErrorException
                && str_contains($exception->getMessage(), 'file_put_contents(')
                && str_contains(str_replace('\\', '/', $exception->getMessage()), '/storage/framework/sessions/');
            $mensaje = $errorSesion
                ? 'No se pudo guardar la sesión en este momento. Intenta acceder de nuevo.'
                : 'No se pudo completar la solicitud. Intenta de nuevo en unos momentos.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $mensaje,
                    'reason' => $errorSesion ? 'session_storage_error' : 'server_error',
                ], 500)->header('Cache-Control', 'no-store');
            }

            return response()->view('errors.solicitud-fallida', [
                'mensaje' => $mensaje,
                'puedeReintentar' => $request->isMethod('GET'),
                'rutaReintento' => '/' . ltrim($request->getRequestUri(), '/'),
            ], 500)->header('Cache-Control', 'no-store');
        });

    })->create();
