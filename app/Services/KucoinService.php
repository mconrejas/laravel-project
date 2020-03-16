<?php

namespace Buzzex\Services;

use GuzzleHttp\Client;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use ccxt\kucoin;
use Exception;

class KucoinService extends ExchangeService
{
    /**
     * @var string
     */
    protected $service = 'kucoin';


    /**
     * @return bool
     */
    public function trade(array $params)
    {
        $data = [];
        $params['type'] = 'limit';
        $params['settings'] = [];

        $this->api = new kucoin([
            'apiKey' => config('external_exchanges.' . $this->service . '.api_key'),
            'secret' => config('external_exchanges.' . $this->service . '.secret_key'),
        ]);

        // this will allow us to simulate with dummy data
        if (strtolower(config('external_exchanges.status')) != 'live') {
            $data = $this->create_order($this->api, $params);
        } else {
            $data = $this->generateDummyResult();
        }

        if (isset($data->success) && $data->success == false) {
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
        return isset($data['info'])
            && $data['info']['success'] == "done";
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
            return [];
        }

        $data = [
            'BUY'  => $results->data->BUY ?: [],
            'SELL' => $results->data->SELL ?: [],
        ];

        $profitMargin = $this->getProfitMargin();

        foreach ($data as $type => $orders) {

            foreach ($orders as $order) {
                // dump($order[0]);
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
                    'symbol' => $pair_string,
                    'type'   => 'both',
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

        return formatMarket($pairstat->pair_text, '_', '-', true);
    }

    /**
     *
     * @return array
     */
    public function generateDummyResult()
    {
        return [
            "info"               => [
                "success"   => true,
                "code"      => "OK",
                "msg"       => "OK",
                "timestamp" => 1546423203819,
                "data"      => [
                    "orderOid" => "5c2c8ba3837bcc20912a6fe8",
                ],
            ],
            "id"                 => "5c2c8ba3837bcc20912a6fe8",
            "timestamp"          => 1546423203819,
            "datetime"           => "2019-01-02T10:00:04.819Z",
            "lastTradeTimestamp" => null,
            "symbol"             => "XRP/NEO",
            "type"               => "limit",
            "side"               => "sell",
            "amount"             => 6.0,
            "filled"             => null,
            "remaining"          => null,
            "price"              => 0.43700001,
            "cost"               => 2.62200006,
            "status"             => "open",
            "fee"                => null,
            "trades"             => null,
            "dummy"              => true,
        ];
    }

}