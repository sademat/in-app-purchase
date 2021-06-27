<?php

namespace App\Console;

use App\Console\Commands\GoogleWorkerCommand;
use App\Console\Commands\IosWorkerCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        IosWorkerCommand::class,
        GoogleWorkerCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // ios ve google command ile işletim sistemi bazında abonelikleri kontrol etmek için kuyruğa ekleyen sorgular çalışyor
        $schedule->command('command:iosworker')->everyMinute();
        $schedule->command('command:googleworker')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
