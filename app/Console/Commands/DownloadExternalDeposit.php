<?php

namespace Buzzex\Console\Commands;

use Buzzex\Models\ExchangePairStat;
use Illuminate\Console\Command;
use Buzzex\Jobs\DownloadCoinExternalDeposits;
use Illuminate\Support\Facades\Cache;

class DownloadExternalDeposit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'external_deposits:download {source?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download external deposits';

    /**
     * Determines if debugging is on or not
     * @var bool
     */
    protected  $debug = true;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $source = is_null($this->argument('source')) ? 'binance' : $this->argument('source');

        if (Cache::has('external_deposit_history_request')) {
            if($this->debug) echo "\nTo avoid violating rate limit, this feature is temporarily unavailable. Please try again in 1 minute.\nExited!\n";
            return false;
        }

        Cache::put('external_deposit_history_request', 'pending', now()->addMinutes(1));

        $service = false;
        $exchange_api_name_service = "Buzzex\\Services\\".ucfirst($source)."Service";
        if (class_exists($exchange_api_name_service)) {
            $service = $exchange_api_name_service::create(['pair_stat' => new ExchangePairStat()]);
        }
        if (!$service) {
            if($this->debug) echo "--$source Service does not exist!";
            return false;
        }

        if($this->debug) echo "Downloading external deposits history from $source...";
        $service->downloadExternalDeposits();
        if($this->debug) echo "\nDONE!\n";
    }

}
