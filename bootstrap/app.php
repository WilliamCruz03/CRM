<?php

use App\Http\Middleware\CheckUserActivo;
use App\Http\Middleware\CheckUserStatus;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.activo'=>CheckUserStatus::class,
        ]);
        //
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
