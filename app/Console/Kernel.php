<?php

namespace Buzzex\Console;

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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('exchange-pair-status:update')
            ->daily();

        $schedule->command('deposits:download BTC')
            ->everyMinute()
            ->runInBackground();

        $schedule->command('blockchain-confirmations:update BTC')
            ->everyFiveMinutes()
            ->runInBackground();

        $schedule->command('deposits:download BZX')
            ->everyMinute()
            ->runInBackground();

        $schedule->command('blockchain-confirmations:update BZX')
            ->everyFiveMinutes()
            ->runInBackground();

        //<!-- ETC, ETH, and ERC20s - don't run in Background while we are using Ethplorer API
        $schedule->command('deposits:download GX')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('blockchain-confirmations:update GX')
            ->everyFiveMinutes()
            ->withoutOverlapping();
        // ETC, ETH, and ERC20s - don't run in Background while we are using Ethplorer API -->

        /*$schedule->command('deposits:download')
            ->everyMinute();*/ //we'll schedule it per coin

        $schedule->command('exchange-item-prices:update')
            ->everyFiveMinutes()
            ->runInBackground();

        $schedule->command('external_deposits:download')
            ->everyFiveMinutes()
            ->runInBackground();

        $schedule->command('external_withdrawals:download')
            ->everyFiveMinutes()
            ->runInBackground();
            
        $schedule->command('external_withdrawals_fee:download')
            ->daily()
            ->runInBackground();
            
        //@noted this is disabled, already on manual triggers
        //$schedule->command('addresses:invalidate')
        //    ->everyMinute();

        //@noted no need for this feature, already on websocket implementation
        //$schedule->command('external-exchange:run')
        //    ->everyMinute();

        $schedule->command('withdrawals:validate')
            ->everyMinute()
            ->runInBackground();

        $schedule->command('check-milestone:run')
            ->everyMinute()
            ->runInBackground();

        $schedule->command('alt_withdrawals:release')
            ->everyMinute()
            ->runInBackground();

        $schedule->command('request:wallets-check')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('delete-old-logs:run')
            ->dailyAt('13:00')
            ->runInBackground();
            
        $schedule->command('update-external-limits:run')
            ->twiceDaily(1, 13)
            ->runInBackground();

        $schedule->command('snapshots:bzx-balance')
            ->hourly()
            ->runInBackground();

        $schedule->command('dividends:disburse')
            ->dailyAt('13:00')
            ->runInBackground();

        //update maxmind geoip database
        $schedule->command('geoip:update')
            ->weekly()
            ->withoutOverlapping();
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
