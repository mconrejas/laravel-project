<?php

namespace Buzzex\Console\Commands;

use Buzzex\Jobs\HandleDownloadExternalWithdrawalFees;
use Illuminate\Console\Command;

class DownloadExternalWithdrawalFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'external_withdrawals_fee:download {source?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download external withdrawal fees for each coins';

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
        
        HandleDownloadExternalWithdrawalFees::dispatch($source);
    }
}
