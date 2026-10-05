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
        Commands\ExpiredUser::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('enamad:verify')
            ->daily();

        // surface verifier failures to a human: summarises the enamad log and
        // emails the admin, with sent-state dedup so unchanged news is not
        // re-emailed by every cron tick
        $schedule->command('enamad:digest')
            ->dailyAt('08:00');

        // same treatment for the payment gateway log: unknown callbacks,
        // rejected verifications, SOAP faults and failed refunds are all
        // recovered from in-request, so without this a gateway can be broken
        // for a whole day and nobody would know
        $schedule->command('payment:digest')
            ->dailyAt('08:15');

        $schedule->command('expire:user')
            ->daily();
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
