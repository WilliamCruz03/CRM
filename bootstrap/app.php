<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

// FUERZA LA APP_KEY A NIVEL GLOBAL
$appKey = 'base64:egKn4akqF+VoQKWm893L4WdtIGLpqiPot3PZhWgoIYM=';

$_ENV['APP_KEY'] = $appKey;
$_SERVER['APP_KEY'] = $appKey;
putenv('APP_KEY=' . $appKey);
$GLOBALS['_ENV']['APP_KEY'] = $appKey;
$GLOBALS['_SERVER']['APP_KEY'] = $appKey;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // NO registrar middlewares aquí estan en Kernel.php
        // Dejar vacío o solo comentar
    })
    ->withSchedule(function (Schedule $schedule) {
        // Cancelar cotizaciones vencidas - cada hora entre 7 AM y 9 PM
        $schedule->command('cotizaciones:cancelar-vencidas')
            ->hourly()
            ->between('7:00', '21:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();