<?php

namespace Buzzex\Services;

use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Models\ExchangeApi;
use Buzzex\Models\ExchangePairStat;
use ccxt\Exchange as ExchangeCCXT;
use Exception;

abstract class ExchangeService
{
    /**
     * @var string
     */
    protected $service = '';

    /**
     * @var \Buzzex\Models\ExchangeApi
     */
    protected $exchange;

    /**
     * @var \Buzzex\Models\ExchangePairStat
     */
    protected $pairStat;

    /**
     * ExchangeService constructor.
     *
     * @param ExchangePairStat $pairStat
     * @param $serviceName
     */
    protected function __construct(ExchangePairStat $pairStat)
    {
        $this->exchange = ExchangeApi::where('name', '=', $this->service)->first();
        $this->pairStat = $pairStat;
    }

    /**
     * @param array $params
     *
     * @return ExchangeService
     * @throws Exception
     */
    public static function create(array $params)
    {
        $pairStat = $params['pair_stat'] ?: null;

        if (!$pairStat) {
            throw new Exception(__('Could not create an instance of the service.'));
        }

        return new static($pairStat);
    }

    /**
     * @return bool
     */
    abstract public function trade(array $params);

    /**
     *
     * @return array
     */
    abstract public function getOrderbook($limit);

    /**
     * @return string
     */
    abstract public function getPairString();

    /**
     * @return float
     */
    public function getProfitMargin()
    {
        if ($this->exchange->profit_margin <= 0) {
            return 0;
        }

        return $this->exchange->profit_margin / 100;
    }

    /**
     * @return \Buzzex\Models\ExchangeApi
     */
    public function getExchangeApi()
    {
        return $this->exchange;
    }

    /**
     * @return \Buzzex\Models\ExchangePairStat
     */
    public function getExchangePairStat()
    {
        return $this->pairStat;
    }

    /**
     * @return string
     */
    public function getBaseEndpoint()
    {
        return $this->exchange->base_url;
    }

    /**
     * @return string
     */
    public function getTradeEndpoint()
    {
        $url = (strtolower(config('external_exchanges.status', 'development')) == 'live') ? $this->exchange->trade_url : $this->exchange->test_url;

        return $this->getBaseEndpoint() . $url;
    }


    /**
     * @return string
     */
    public function getOrderbookEndpoint()
    {
        return $this->getBaseEndpoint() . $this->exchange->orderbook_url;
    }

    /**
     * @return string
     */
    public function getServerTimeEndpoint()
    {
        return $this->getBaseEndpoint() . $this->exchange->server_time_url;
    }

    /**
     * @param $pairId
     *
     * @return array
     */
    public function getInternalOrderBook($pairId)
    {
        $market = app()->make(Marketable::class);

        $orderBook = $market->getOrderbook($pairId, 8, 100, 'ask', 'desc', $this->service);
        $orderBook = array_merge(
            $orderBook,
            $market->getOrderbook($pairId, 8, 100, 'bid', 'desc', $this->service)
        );
  
        return $orderBook;
    }

    /**
     * @param class $service
     * @param array $params
     *
     * @return json
     */
    public function create_order($service, $params=[])
    {
        try {
            return $service->create_order($params['market'], $params['type'], $params['side'], $params['amount'], $params['price'], $params['settings']);
        } catch (Exception $e) {
            $extract = strstr($e->getMessage(), '{');

            if (!empty($extract)) {
                return json_decode($extract);
            } else {
                return (object) [
                    'error' => $e->getMessage()
                ];
            }
        }
    }

    /**
     *
     */
    public function deleteOldEntries()
    {
    }
}
