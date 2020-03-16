<?php

namespace Buzzex\Console\Commands;

use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Contracts\User\Tradable;
use Buzzex\Crypto\Currency\Coins\Exceptions\CoinNotFoundException;
use Illuminate\Console\Command;

class UpdateBlockchainConfirmations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blockchain-confirmations:update {coinSymbol=null}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the blockchain confirmations of blockchain deposits';

    /**
     * Determines if debugging is on or off
     * @var bool
     */
    private $debug = false;

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

    public function handle(Tradable $trading, Marketable $markets)
    {
        if ($this->argument('coinSymbol') === null || $this->argument('coinSymbol') === 'null') {
            $this->runAll($trading,$markets);

            return;
        }

        if($this->debug) echo "Updating deposits confirmations for ".$this->argument('coinSymbol');
        $this->updateConfirmations($trading,$markets,$this->argument('coinSymbol'));

    }

    /**
     * Run all from coins specified in config
     */
    protected function runAll(Tradable $trading, Marketable $markets)
    {
        $coins = explode(',', config('coins.download_coin_deposits'));

        foreach ($coins as $coinSymbol) {
            if($this->debug) echo "Updating deposits confirmations for $coinSymbol...";
            else $this->updateConfirmations($trading,$markets,$coinSymbol);
        }
    }

    protected function updateConfirmations(Tradable $trading, Marketable $markets, $coinSymbol){
        if (!$markets->isValidCoin($coinSymbol)) {
            throw new CoinNotFoundException(__('Invalid coin symbol.'));
        }

        echo $this->debug?"TRUE":"FALSE";
        if($this->debug) echo "<hr/>Going to trading->updateBlockchainConfirmations($coinSymbol)";
        $trading->updateBlockchainConfirmations($coinSymbol,0,20,$this->debug);
    }
}
