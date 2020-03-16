<?php

namespace Buzzex\Services;

use GuzzleHttp\Client;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use ccxt\coinex;


class CoinexService extends ExchangeService
{
    /**
     * @var string
     */
    protected $service = 'coinex';

    /**
     * @return bool
     */
    public function trade(array $params)
    {
        $data = [];
        $params['type'] = 'limit';
        $params['settings'] = [];

        $service = new coinex([
            'apiKey' => config('external_exchanges.' . $this->service . '.api_key'),
            'secret' => config('external_exchanges.' . $this->service . '.secret_key'),
        ]);

        // this will allow us to simulate with dummy data
        if (strtolower(config('external_exchanges.status')) == 'live') {
            $data = $this->create_order($service, $params);
        } else {
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
        return isset($data['info'])
            && $data['info']['status'] == "done";
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
        $pairStat = $this->getExchangePairStat();
        $orderBook = [];

        $results = null;
        try {
            $results = $this->getData($url, $limit);
        } catch (GuzzleException $exception) {

        }

        if ($results->code > 0) {
            return [];
        }

        $data = [
            'BUY'  => $results->data->bids ?: [],
            'SELL' => $results->data->asks ?: [],
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
                    'pair_id'    => $pairStat->pair_id,
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
     * @throws \GuzzleHttp\Exception\GuzzleException
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
                    'limit'  => $limit,
                    'merge'  => 0,
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

        return $pairstat->exchangePair->getNameAttribute(true);
    }

    /**
     *
     * @return timestamp
     */
    public function getServerTime()
    {
        return (int)(microtime(true) * 1000);
    }

    /**
     *
     * @return array
     */
    public function generateDummyResult()
    {
        return [
            "id"                 => "2924484365",
            "datetime"           => "2018-12-21T05:37:05.000Z",
            "timestamp"          => 1545370625000,
            "lastTradeTimestamp" => null,
            "status"             => "closed",
            "symbol"             => "XRP/USDT",
            "type"               => "limit",
            "side"               => "buy",
            "price"              => 0.376948,
            "cost"               => 0.359847,
            "average"            => 0.359847,
            "amount"             => 1.0,
            "filled"             => 1.0,
            "remaining"          => 0.0,
            "trades"             => null,
            "fee"                => [
                "currency" => "USDT",
                "cost"     => 0.001,
            ],
            "info"               => [
                "amount"         => "1",
                "asset_fee"      => "0",
                "avg_price"      => "0.35984700000000000000",
                "create_time"    => 1545370625,
                "deal_amount"    => "1",
                "deal_fee"       => "0.001",
                "deal_money"     => "0.359847",
                "fee_asset"      => null,
                "fee_discount"   => "0",
                "id"             => 2924484365,
                "left"           => "0e-8",
                "maker_fee_rate" => "0.001",
                "market"         => "XRPUSDT",
                "order_type"     => "limit",
                "price"          => "0.37694800",
                "source_id"      => "",
                "status"         => "done",
                "taker_fee_rate" => "0.001",
                "type"           => "buy",
            ],
        ];
    }
}