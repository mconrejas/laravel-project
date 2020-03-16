<?php

namespace Buzzex\Jobs;

use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Crypto\Currency\Coins\Exceptions\CoinNotFoundException;
use Buzzex\Models\ExchangePairStat;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tolawho\Loggy\Facades\Loggy;

class DownloadCoinExternalDeposits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private $coinSymbol = null;

    /**
     * @var string
     */
    private $source = 'binance';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($coinSymbol, $source)
    {
        $this->coinSymbol = $coinSymbol;
        $this->source = $source;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws CoinNotFoundException
     */
    public function handle(Marketable $markets)
    {
        if (!is_null($this->coinSymbol) && !$markets->isValidCoin($this->coinSymbol)) {
            return; //throw new CoinNotFoundException(__('Invalid coin symbol.'));
        }

        $service_name = "\\Buzzex\\Services\\".ucfirst($this->source)."Service";

        if (class_exists($service_name)) {
            $service = $service_name::create(['pair_stat' => new ExchangePairStat()]);

            if (method_exists($service, 'downloadExternalDeposits')) {
                $service->downloadExternalDeposits($this->coinSymbol);
            }

            Loggy::info('exchange', json_encode(['message' => 'External download deposit run on '.Carbon::now()->format('Y-m-d H:i:s')]));
        }
    }
}
