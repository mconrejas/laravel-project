<?php

namespace Buzzex\Http\Controllers\External;

use Buzzex\Crypto\Exchanges\ExternalExchangeServiceFactory;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\ExchangeApi;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\ExchangePair;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Services\BinanceService;
use Buzzex\Services\ExchangeService;
use Buzzex\Services\TradingService;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Storage;

class WebsocketApiController extends Controller
{
    protected $limit;

    /**
     * WebsocketApiController constructor.
     *
     */
    public function __construct()
    {
        $this->limit = 1000;
    }

    /**
    * @return mixed
    */
    public function getSnapShot(Request $request)
    {
        if (Cache::has('binance_snapshot_'.$request->pairtext)) {
            return Cache::get('binance_snapshot_'.$request->pairtext, '{}');
        }
        //json on string format
        $snapshots = file_get_contents('https://www.binance.com/api/v1/depth?symbol='.$request->pairtext.'&limit=100');

        $expiresAt = now()->addSeconds(5);
        Cache::put('binance_snapshot_'.$request->pairtext, $snapshots, $expiresAt);

        return $snapshots;
    }

    /**
     * @return void
     */
    public function getOrderbookItems(Request $request, TradingService $trading)
    {
        // delete all
        if (isset($request->service)) {
            ExchangeOrder::where('module', $request->service)->delete();
        }
        
        $orderBook = [];

        $exchange = ExchangeApi::where('name', '=', $request->service)->first();
        $pairtext = formatMarket($request->pair_text, '_', '', true);
        $pair = ExchangePairStat::where('pair_text', '=', $request->pair_text)->first();
        $services = ExternalExchangeServiceFactory::getExternalExchangeServices();
        $exchangeService = $services[$request->service];
        $api = $exchangeService::create(['pair_stat' => $pair]);

        $orderbookURL = $api->getOrderbookEndpoint().'?symbol='.$pairtext.'&limit='.$this->limit;
        $orderbook = json_decode(file_get_contents($orderbookURL));
        $lastUpdateId = $orderbook->lastUpdateId;
        
        $data = [
            'BUY'  => $orderbook->bids ?: [],
            'SELL' => $orderbook->asks ?: [],
        ];

         
        $orderbook_user_id = parameter('external_exchange_order_user_id');
        $profitMargin = $api->getProfitMargin();

        foreach ($data as $type => $orders) {
            if ($orders) {
                foreach ($orders as $order) {
                    $price = ($type === 'BUY')
                        ? $order[0] - ($order[0] * $profitMargin)
                        : $order[0] + ($order[0] * $profitMargin);

                    $orderBook[] = [
                        '', // order_id
                        $request->service, // module
                        $exchange->id, // module_id
                        $orderbook_user_id, // user_id
                        $order[1], // amount
                        '0.00000000', // fee
                        $type, // type
                        $price, // price
                        null, // stop_price
                        null, // limit_price
                        '0000-00-00 00:00:00', // stop_limit_execution_time
                        $pair->pair_id, // pair_id
                        'limit', // form_type
                        '0.01527649', // target_amount @todo:how is this calculated :price*amount
                        '0.00000000', // fulfilled_amount
                        '0.00000000', // fulfilled_target_amount
                        '0.00000000', // fulfilled_amount_reg
                        '0.00000000', // fulfilled_target_amount_reg
                        request()->ip(), // ip_address
                        '0', // completed
                        Carbon::now()->timestamp // created
                    ];
                }
            }
        }

        $csvFile = $lastUpdateId.'-'.$pairtext.'.csv';

        if (Storage::put('/orderbooks/'.$csvFile, $this->generateCsv($orderBook), 'public')) {
            if ($this->loadMysqlDataLocal($csvFile)) {
                return Storage::delete('/orderbooks/'.$csvFile) ? 'Done':'Error';
            }
        }
    }

    public function generateCsv($data, $delimiter = ',', $enclosure = '"')
    {
        $contents = '';
        $handle = fopen('php://temp', 'r+');
        foreach ($data as $line) {
            fputcsv($handle, $line, $delimiter, $enclosure);
        }
        rewind($handle);
        while (!feof($handle)) {
            $contents .= fread($handle, 8192);
        }
        fclose($handle);
        return $contents;
    }

    public function loadMysqlDataLocal($file)
    {
        $pdo = DB::connection()->getPdo();
        $path = addslashes(storage_path("app\orderbooks\\".$file));

        try {
            return $pdo->exec("LOAD DATA LOCAL INFILE '".$path."' INTO TABLE exchange_orders FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' SET order_id = NULL");
        } catch (Exception $e) {
        }
    }
}
