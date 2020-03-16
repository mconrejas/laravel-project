<?php

namespace Buzzex\Console\Commands;

use Buzzex\Crypto\Currency\CoinFactory;
use Buzzex\Jobs\InvalidateAddressesByCoin;
use Buzzex\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InvalidateAddressesNotOurs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'addresses:invalidate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invalidate addresses from [coin]_addresses table if not ours.';

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
     * @throws \Buzzex\Crypto\Currency\Coins\Exceptions\CoinUnsetPropertyException
     */
    public function handle()
    {
        $users = (new User())->newQuery()
            ->get();

        foreach($users as $user) {
            if (Cache::has('get-new-address-trigger-' . $user->id)) {
                $exchangeItem = Cache::get('get-new-address-trigger-' . $user->id);

                InvalidateAddressesByCoin::dispatch($exchangeItem);

                Cache::forget('get-new-address-trigger-' . $user->id);
            }
        }
    }
}
