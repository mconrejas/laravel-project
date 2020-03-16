<?php

namespace Buzzex\Services;

use GuzzleHttp\Client;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use ccxt\poloniex;
use Exception;

class PoloniexService extends ExchangeService
{
    /**
     * @var string
     */
    protected $service = 'poloniex';

    /**
     * PoloniexService constructor.
     *
     * @param NewsRepository $newsRepository
     */
    public function __construct()
    {
        $this->service = new poloniex([
            'apiKey' => config('external_exchanges.poloniex.api_key'),
            'secret' => config('external_exchanges.poloniex.secret_key'),
        ]);
    }

    /**
     * @return bool
     */
    public function trade(array $params)
    {
        $data = [];
        $params['type'] = 'limit';
        $params['settings'] = [];

        // this will allow us to simulate with dummy data
        if (strtolower(config('external_exchanges.status')) == 'live') {
            $data = [];
        }//$data = $this->create_order($this->service, $params);
        else {
            $data = $this->generateDummyResult();
        }


        if (isset($data->error)) {
            return false;
        }

        return $this->isTradingSuccessful($data);
    }

    /**
     * @param $data
     *
     * @return bool
     */
    protected function isTradingSuccessful($data)
    {
        if (isset($data['info']) && $data['info']['status'] == "open") {

            try {
                $open = $this->service->fetch_order_trades($data['id']); //
                dump('order found in trade history! status:completed');

                return true;
            } catch (Exception $e) {
                $status = $this->service->fetch_order_status($data['id']); // open orders

                if (strtolower($status) == 'open') {
                    dump('cancel_order');

                    //$this->service->cancel_order($data['id']);
                    return false;
                }
            }

        } else {
            return isset($data['info']) && $data['info']['status'] == "done";
        }
    }

    /**
     * @param $data
     *
     * @return bool
     */
    protected function cancelOrder($data)
    {
        if (isset($data['info']) && $data['info']['status'] == "open") {
            dd('canecel ni');
        } else {
            return isset($data['info']) && $data['info']['status'] == "done";
        }
    }

    /**
     * @param $limit
     *
     * @return array
     */
    public function getOrderbook($limit = 5)
    {
        $url = $this->getOrderbookEndpoint();
        $orderbook_user_id = parameter('external_exchange_order_user_id');
        $pair = $this->getExchangePairStat();
        $orderBook = [];

        $results = null;

        try {
            $results = $this->getData($url, $limit);
        } catch (GuzzleException $exception) {

        }

        if (!$results) {
            return [];
        }

        $data = [
            'BUY'  => $results->bids ?: [],
            'SELL' => $results->asks ?: [],
        ];

        $profitMargin = $this->getProfitMargin();

        foreach ($data as $type => $orders) {
            foreach ($orders as $order) {
                $price = ($type === 'BUY')
                    ? $order[0] - ($order[0] * $profitMargin)
                    : $order[0] + ($order[0] * $profitMargin);

                $orderBook[] = [
                    'action'     => $type,
                    'price'      => $price,
                    'amount'     => $order[1],
                    'pair_id'    => $pair->pair_id,
                    'module_id'  => $this->exchange->id,
                    'module'     => $this->service,
                    'user_id'    => $orderbook_user_id,
                    'created'    => Carbon::now()->timestamp,
                    'ip_address' => request()->ip(),
                ];

            }
        }

        return $orderBook;
    }

    /**
     * @param $url
     * @param $limit
     *
     * @return mixed
     * @throws GuzzleException
     */
    protected function getData($url, $limit)
    {
        $client = new Client;
        $pair_string = $this->getPairString();

        $response = $client->request('GET',
            $url,
            [
                'query' => [
                    'command'      => 'returnOrderBook',
                    'currencyPair' => $pair_string,
                    'depth'        => $limit,
                ],
            ]);

        return json_decode((string)$response->getBody());
    }

    /**
     * @return string
     */
    public function getPairString()
    {
        $pairstat = $this->getExchangePairStat();

        return $pairstat->pair_text;
    }

    /**
     *
     * @return array
     */
    public function generateDummyResult()
    {
        //failed
        // return [
        //   "info" => [
        //     "timestamp" => "1545377794388",
        //     "status" => "open",
        //     "type" => "LIMIT",
        //     "side" => "buy",
        //     "price" => 0.005,
        //     "amount" => 200,
        //     "orderNumber" => "134108399485",
        //     "resultingTrades" => [],
        //   ],
        //   "id" => "134117185690",
        //   "timestamp" => 1545377794388,
        //   "datetime" => "2018-12-21T07:36:34.388Z",
        //   "lastTradeTimestamp" => null,
        //   "status" => "open",
        //   "symbol" => "XRP/USDT",
        //   "type" => "LIMIT",
        //   "side" => "buy",
        //   "price" => 0.005,
        //   "cost" => 0.0,
        //   "amount" => 200.0,
        //   "filled" => 0.0,
        //   "remaining" => 200.0,
        //   "trades" => [],
        //   "fee" => null
        // ];

        //completed
        return [
            "info"               => [
                "timestamp"       => "1545382339051",
                "status"          => "open",
                "type"            => "LIMIT",
                "side"            => "buy",
                "price"           => 0.36721261,
                "amount"          => 3,
                "orderNumber"     => "134116237639",
                "resultingTrades" => [
                    0 => [
                        "amount"  => "3.00000000",
                        "date"    => "2018-12-21 08:52:21",
                        "rate"    => "0.36721261",
                        "total"   => "1.10163783",
                        "tradeID" => "6964680",
                        "type"    => "buy",
                    ],
                ],
            ],
            "id"                 => "134116237639",
            "timestamp"          => 1545382339051,
            "datetime"           => "2018-12-21T08:52:19.051Z",
            "lastTradeTimestamp" => null,
            "status"             => "open",
            "symbol"             => "XRP/USDT",
            "type"               => "LIMIT",
            "side"               => "buy",
            "price"              => 0.36721261,
            "cost"               => 0.0,
            "amount"             => 3.0,
            "filled"             => 0.0,
            "remaining"          => 3.0,
            "trades"             => [
                0 => [
                    "info"      => [
                        "amount"  => "3.00000000",
                        "date"    => "2018-12-21 08:52:21",
                        "rate"    => "0.36721261",
                        "total"   => "1.10163783",
                        "tradeID" => "6964680",
                        "type"    => "buy",
                    ],
                    "timestamp" => 1545382341000,
                    "datetime"  => "2018-12-21T08:52:21.000Z",
                    "symbol"    => "XRP/USDT",
                    "id"        => "6964680",
                    "order"     => null,
                    "type"      => "limit",
                    "side"      => "buy",
                    "price"     => 0.36721261,
                    "amount"    => 3.0,
                    "cost"      => 1.10163783,
                    "fee"       => null,
                ],
            ],
            "fee"                => null,

        ];
    }

}