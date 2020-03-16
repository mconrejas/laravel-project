<?php

namespace Buzzex\Repositories;

use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Models\ExchangeTransaction;
use Buzzex\Contracts\User\Tradable;
use Buzzex\Models\User;

class ExchangeRepository
{
    /**
     * @var Marketable
     */
    private $markets;

    /**
     * @var Tradable
     */
    private $trading;

    /**
     * ExchangeRepository constructor.
     *
     * @param Marketable $markets
     */
    public function __construct(Marketable $markets, Tradable $trading)
    {
        $this->markets = $markets;
        $this->trading = $trading;
    }

    /**
     * @param $coinSymbol
     * @param int $limit
     * @param int $offset
     *
     * @return array|bool
     */
    public function getMarketFor($coinSymbol, $limit = 100, $offset = 0)
    {
        return $this->markets->getMarketFor($coinSymbol, $limit, $offset);
    }

    /**
     * @param $pairText
     * @param int $limit
     * @param string $type Values can be sell|ask or bid|buy. Default is 'all'
     * @param string $orderDirection
     * @return array|bool
     */
    public function getDepthByPairText($pairText, $limit = 1, $type = 'all', $orderDirection = 'desc')
    {
        return $this->markets->getDepthByPairText($pairText, $limit, $type, $orderDirection);
    }

    /**
     * @param $pairId
     * @param int $limit
     * @param string $type Values can be sell|ask or bid|buy. Default is 'all'
     * @param string $orderDirection
     *
     * @return array|bool
     */
    public function getDepthByPairId($pairId, $decimal=8, $limit = 1, $type = 'all', $orderDirection = 'desc')
    {
        return $this->markets->getDepthByPairId($pairId, $decimal, $limit, $type, $orderDirection);
    }

    /**
     * @param $pairId
     * @param int $limit
     * @param string $type Values can be sell|ask or bid|buy. Default is 'all'
     * @param string $orderDirection
     *
     * @return array|bool
     */
    public function getOrderBook($pairId, $decimal=8, $limit = 1, $type = 'all', $orderDirection = 'desc')
    {
        return $this->markets->getOrderBook($pairId, $decimal, $limit, $type, $orderDirection);
    }

    /**
     * @param $pairId
     * @param $user
     * @param int $limit
     * @param int $offset
     * @param array $filters
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getLatestExecution($pairId, $user, $limit = 100, $offset = 0, $filters=[])
    {
        return $this->markets->getLatestExecution($pairId, $user, $limit, $offset, $filters);
    }

    /**
     * @param $term
     *
     * @return array
     */
    public function searchPair($term)
    {
        return $this->markets->searchPair($term);
    }

    /**
     * @param $term
     *
     * @return array
     */
    public function searchCoin($term)
    {
        return $this->markets->searchCoin($term);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getOhlcv(array $params = [])
    {
        return $this->trading->getOhlcv($params);
    }

    /**
     * Create withdrawal request
     *
     * @param User $user
     * @param string $coin Coin Name
     * @param string $address
     * @param float $amount
     * @return boolean
     */
    public function withdraw(User $user, $coin, $address, $amount, $tag = null)
    {
        return $this->trading->withdraw($user, $coin, $address, $amount,$tag);
    }

    /**
     * @param $pairText
     *
     * @return array|bool
     */
    public function getPairInfoByPairText($pairText)
    {
        return $this->markets->getPairInfoByPairText($pairText);
    }

    /**
     * @param $pairId
     *
     * @return array|bool
     */
    public function getPairInfoByPairId($pairId)
    {
        return $this->markets->getPairInfoByPairId($pairId);
    }
}