<?php

namespace Buzzex\Console\Commands;

use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeTransaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BzxBalanceSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snapshots:bzx-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get a snapshot of BZX balances of all users';

    /**
     * Determines whether or not debugging is on
     * @var bool
     */
    protected  $debug = true;

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
        $time = Carbon::now()->timestamp;

        $items = (new ExchangeItem())->newQuery()->where('symbol','BZX')->first();

        if(!$items){
            if($this->debug) echo "There's no item with BZX symbol.";
            return false;
        }

        if($this->debug){
            print_r($items);
        }


        $transactions = (new ExchangeTransaction())->newQuery()
            ->selectRaw('user_id, sum(amount) as total')
            ->where('user_id','<>',parameter('external_exchange_order_user_id', 1))
            ->where('item_id',$items->item_id)
            ->where('cancelled', 0)
            ->where(function ($query) use ($time) {
                $query->where('released', '>', 0)
                    ->where('released', '<=', $time)
                    ->orWhere('type', 'withdrawal-request');
            })
            ->groupBy('user_id')
            ->get();

        $data = [];

        foreach ($transactions as $transaction) {
            $data[$transaction->user_id] = $transaction->total;
            DB::table("bzx_balance_snapshots")->insert([
                "user_id" => $transaction->user_id,
                "amount" => $transaction->total,
                "added_by" => 0, //0 = system
                "time" => $time,
            ]);
        }

        if($this->debug){
            echo "Done!";
            print_r($data);
        }
    }
}
