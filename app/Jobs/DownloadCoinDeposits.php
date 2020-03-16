<?php

namespace Buzzex\Jobs;

use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Contracts\User\Tradable;
use Buzzex\Crypto\Currency\Coins\Exceptions\CoinNotFoundException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DownloadCoinDeposits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShouldNotifyDevelopers;

    /**
     * @var string
     */
    private  $coinSymbol;

    /**
     * Determines if debugging is on or off
     * @var bool
     */
    private $debug = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($coinSymbol)
    {
        $this->coinSymbol = $coinSymbol;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws CoinNotFoundException
     */
    public function handle(Tradable $trading, Marketable $markets)
    {
        if (!$markets->isValidCoin($this->coinSymbol)) {
            throw new CoinNotFoundException(__('Invalid coin symbol.'));
        }

        echo $this->debug?"TRUE":"FALSE";
        if($this->debug) echo "<hr/>Going to trading->downloadDeposits($this->coinSymbol)";

        $trading->downloadDeposits($this->coinSymbol,$this->debug);
    }
}
