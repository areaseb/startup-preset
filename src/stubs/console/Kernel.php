<?php

namespace App\Console;

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
        Commands\CalendarIcs::class,
        Commands\LogClean::class,
        Commands\RestartQueueIf::class,
        Commands\InvoiceSync::class,
        Commands\InvoiceReceive::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->command('invoice:sync')
                 ->weekdays()
                 ->hourly()
                 ->between('8:00', '20:00');

        $schedule->command('invoice:receive')
                 ->weekdays()
                 ->hourly()
                 ->between('8:00', '20:00');

        $schedule->command('calendar:ics')
                 ->everyFiveMinutes();

        $schedule->command('log:clean')
                ->daily();

        $schedule->command('myqueue:restart')
                 ->everyMinute();
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
