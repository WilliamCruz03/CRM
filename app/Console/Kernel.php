<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
         // Tarea de prueba
        $schedule->exec('echo "test"')->everyMinute();
        // Programar cancelación automática de cotizaciones - cada hora entre 7 AM y 9 PM
        $schedule->command('cotizaciones:cancelar-vencidas')
        ->hourly()
        ->between('7:00', '21:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}