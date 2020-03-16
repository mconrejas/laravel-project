<?php

namespace Buzzex\Services;

use GuzzleHttp\Client;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use ccxt\bittrex;
use Exception;

class BittrexService extends ExchangeService
{
    /**
     * @var string
     */
    protected $service = 'bittrex';


    /**
     * @return bool
     */
    public function trade(array $params)
    {
        $data = [];
        $params['type'] = 'limit';
        $params['settings'] = [];

        $this->api = new bittrex([
            'apiKey' => config('external_exchanges.' . $this->service . '.api_key'),
            'secret' => config('external_exchanges.' . $this->service . '.secret_key'),
        ]);

        // this will allow us to simulate with dummy data
        if (strtolower(config('external_exchanges.status')) == 'live') {
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

        }

        if (!$results->success) {
            return [];
        }

        $data = [
            'BUY'  => $results->result->buy ?: [],
            'SELL' => $results->result->sell ?: [],
        ];

        $profitMargin = $this->getProfitMargin();

        foreach ($data as $type => $orders) {

            foreach ($orders as $order) {

                $price = ($type === 'BUY')
                    ? $order->Rate - ($order->Rate * $profitMargin)
                    : $order->Rate + ($order->Rate * $profitMargin);

                $orderBook[] = [
                    'action'     => $type,
                    'price'      => $price,
                    'amount'     => $order->Quantity,
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
                    'market' => $pair_string,
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

        return formatMarket($pairstat->pair_text, '_', '-');
    }

    /**
     *
     * @return array
     */
    public function generateDummyResult()
    {
        return [
            "info"   => [
                "success" => true,
                "message" => "",
                "result"  => [
                    "uuid" => "2f354982-4565-4a06-9f37-c1f03b7e21e0",
                ],
            ],
            "id"     => "2f354982-4565-4a06-9f37-c1f03b7e21e0",
            "symbol" => "XRP/USDT",
            "type"   => "limit",
            "side"   => "buy",
            "status" => "open",
        ];
    }

}