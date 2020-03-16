<?php

namespace Buzzex\Console\Commands;

use Buzzex\Events\ExternalBalanceUpdatedEvent;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Services\BinanceService;
use Illuminate\Console\Command;

class GetExternalBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-external-balance:run {tickers} {source?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the balance of a ticker (comma separated) from specified external sources';

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
        $source = $this->argument('source') ?? 'binance';
        $tickers = $this->argument('tickers') ?? '';

        $pairStat = new ExchangePairStat();

        if ($pairStat) {
            $response = [];

            if ($source == 'binance') {
                $service = BinanceService::create(['pair_stat' => $pairStat]);
                $response = $service->checkBalance();
            }
            
            if (!empty($response)) {
                $tickers = explode(',', $tickers);
                foreach ($tickers as $key => $ticker) {
                    if (array_key_exists(strtoupper($ticker), $response)) {
                        broadcast(new ExternalBalanceUpdatedEvent(strtoupper($ticker), $response[strtoupper($ticker)]['free']));
                    }
                }
            }
        }
    }
}
