<?php

namespace Buzzex\Console\Commands;

use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeMarket;
use Buzzex\Services\RevenueService;
use Illuminate\Console\Command;

class DisburseDividends extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dividends:disburse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disburse Dividends';

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
        $markets = ExchangeMarket::get();
        if(!$markets){
            return false;
        }
        $revenueService = new RevenueService(true);
        foreach($markets as $market){
            $exchangeItem = ExchangeItem::where('item_id',$market->item_id)->first();
            if(!$exchangeItem){
                continue;
            }
            $revenueService->setCurrency($exchangeItem->symbol);
            $revenueService->run("daily");
        }
    }
}
