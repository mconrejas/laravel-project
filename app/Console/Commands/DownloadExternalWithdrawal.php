<?php

namespace Buzzex\Console\Commands;

use Illuminate\Console\Command;
use Buzzex\Jobs\DownloadCoinExternalWithdrawals;

class DownloadExternalWithdrawal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'external_withdrawals:download {source?} {coinSymbol?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download external withdrawals';

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
        
        $coin = is_null($this->argument('coinSymbol')) ? null : strtoupper($this->argument('coinSymbol'));
        
        if (!is_null($coin)) {
            $this->runAll($coin, $source);
            return;
        }

        DownloadCoinExternalWithdrawals::dispatch($coin, $source);
    }

    /**
     * Run all from coins specified params
     */
    protected function runAll($coins = "", $source)
    {
        $coins = explode(',', $coins);

        foreach ($coins as $coinSymbol) {
            DownloadCoinExternalWithdrawals::dispatch($coinSymbol, $source);
        }
    }
}
