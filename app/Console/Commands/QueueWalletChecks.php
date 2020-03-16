<?php

namespace Buzzex\Console\Commands;

use Buzzex\Jobs\QueueUserWalletChecks;
use Illuminate\Console\Command;

class QueueWalletChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'request:wallets-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Request wallets deposits check for all users who have logged in the last X days';

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
        QueueUserWalletChecks::dispatch();
    }
}
