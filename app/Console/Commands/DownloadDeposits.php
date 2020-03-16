<?php

namespace Buzzex\Console\Commands;

use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Contracts\User\Tradable;
use Buzzex\Crypto\Currency\Coins\Exceptions\CoinNotFoundException;
use Buzzex\Jobs\DownloadCoinDeposits;
use Illuminate\Console\Command;

class DownloadDeposits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deposits:download {coinSymbol=null}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download deposits';

    /**
     * Determines if debugging is on or off
     * @var bool
     */
    private $debug = false;

    /**
     * Determines whether to queue or not to queue the coin deposits donwload
     * @var bool
     */
    private $queue = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->queue = (parameter("deposits.queue_deposits_download", 0) == 1);
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Tradable $trading, Marketable $markets)
    {
        if ($this->argument('coinSymbol') === null || $this->argument('coinSymbol') === 'null') {
            $this->runAll($trading, $markets);

            return;
        }

        if ($this->debug) {
            echo $this->queue?"QUEUE":"NOQUEUE";
        }
        if ($this->queue) {
            DownloadCoinDeposits::dispatch($this->argument('coinSymbol'));
        } else {
            $this->downloadCoinDeposits($trading, $markets, $this->argument('coinSymbol'));
        }
    }

    /**
     * Run all from coins specified in config
     */
    protected function runAll(Tradable $trading, Marketable $markets)
    {
        $coins = explode(',', config('coins.download_coin_deposits'));

        foreach ($coins as $coinSymbol) {
            if ($this->debug) {
                echo "Downloading Deposits for $coinSymbol...";
            }
            if ($this->queue) {
                DownloadCoinDeposits::dispatch($coinSymbol);
            } else {
                $this->downloadCoinDeposits($trading, $markets, $coinSymbol);
            }
        }
    }

    protected function downloadCoinDeposits(Tradable $trading, Marketable $markets, $coinSymbol)
    {
        if ($markets->isValidCoin($coinSymbol)) {
            echo $this->debug?"TRUE":"FALSE";
            
            if ($this->debug) {
                echo "<hr/>Going to trading->downloadDeposits($coinSymbol)";
            }

            $trading->downloadDeposits($coinSymbol, $this->debug);
        }
    }
}
