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
        // Archiver les comptes bloqués expirés tous les jours à minuit
        $schedule->command('comptes:archive-expired-blocked')
            ->dailyAt('00:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Désarchiver les comptes bloqués expirés tous les jours à 0h30 du matin
        $schedule->command('comptes:unarchive-expired-blocked')
            ->dailyAt('00:30')
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
