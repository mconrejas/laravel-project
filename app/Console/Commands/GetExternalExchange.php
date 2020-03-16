<?php

namespace Buzzex\Console\Commands;

use Buzzex\Crypto\Exchanges\ExternalExchangeServiceFactory;
use Buzzex\Jobs\DownloadExternalOrderBook;
use Buzzex\Models\ExchangePair;
use Buzzex\Services\ExchangeService;
use Illuminate\Console\Command;

class GetExternalExchange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'external-exchange:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get external-exchange';

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
     */
    public function handle()
    {
        $exchanges = ExternalExchangeServiceFactory::getExternalExchangeServices();

        foreach ($exchanges as $exchange) {
            $this->downloadExternalOrderBook($exchange);
        }
    }

    /**
     * @param string $exchangeService
     */
    protected function downloadExternalOrderBook($exchangeService)
    {
        $pairs = ExchangePair::getPairs();

        foreach ($pairs as $pair) {
            DownloadExternalOrderBook::dispatch(
                $exchangeService::create(['pair_stat' => $pair->exchangePairStat])
            );
        }
    }
}
