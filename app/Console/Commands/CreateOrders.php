<?php

namespace Buzzex\Console\Commands;

use Illuminate\Console\Command;
use Buzzex\Models\ExchangePair;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Models\ExchangeApi;
use Buzzex\Models\ExchangeOrder; 
use Buzzex\Services\ExchangeService;
use Buzzex\Services\BinanceService; 
use Buzzex\Services\TradingService;
use Buzzex\Crypto\Exchanges\ExternalExchangeServiceFactory;
use Carbon\Carbon;

class CreateOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:orders {service} {price} {amount} {pair} {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle(TradingService $trading)
    {
        $orderBook = [];
        $pairtext = $this->argument('pair');
        $type = $this->argument('type');
        $service = $this->argument('service');

        $order = [
            'price' => $this->argument('price'),
            'quantity' => $this->argument('amount')
        ];

        
        $services = ExternalExchangeServiceFactory::getExternalExchangeServices();
        $exchangeService = $services[$service];
        $exchange = ExchangeApi::where('name', '=', $service)->first();
        $pair = ExchangePairStat::where('pair_text','=',$pairtext)->first();
        $api = $exchangeService::create(['pair_stat' => $pair]);

        $orderbook_user_id = parameter('external_exchange_order_user_id'); 
        $profitMargin = $api->getProfitMargin();

 
        $price = ($type === 'BUY')
            ? $order['price'] - ($order['price'] * $profitMargin)
            : $order['price'] + ($order['price'] * $profitMargin);

        if ($order['quantity'] > 0) {
            $orderBook[] = [
                'action'     => $type,
                'price'      => $price,
                'amount'     => $order['quantity'],
                'pair_id'    => $pair->pair_id,
                'module_id'  => $exchange->id,
                'module'     => $service,
                'user_id'    => $orderbook_user_id,
                'created'    => Carbon::now()->timestamp,
                'ip_address' => request()->ip(),
            ];
        }

        $api->deleteOldEntries();
        $trading->insertExternalOrderBook($orderBook, $api, false) ? "Done" : "Error";
    }


}
