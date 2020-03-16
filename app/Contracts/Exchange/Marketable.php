<?php

namespace Buzzex\Contracts\Exchange;

use Buzzex\Models\ExchangePair;

interface Marketable
{
    /**
     * @param string $coinSymbol
     * @param int $limit
     * @param int $offset
     *
     * @return array|bool
     */
    public function getMarketFor($coinSymbol, $limit = 100, $offset = 0);

    /**
     * @param $coinSymbol
     *
     * @return bool
     */
    public function isValidCoin($coinSymbol);

    /**
     * @param $pairText
     *
     * @return array|bool
     */
    public function getPairInfoByPairText($pairText);

    /**
     * @param $pairId
     *
     * @return array|bool
     */
    public function getPairInfoByPairId($pairId);

    /**
     * @param $pairText
     * @param int $limit
     * @param string $type Values can be sell|ask or bid|buy. Default is 'all'
     * @param string $orderDirection
     * @return mixed
     */
    public function getDepthByPairText($pairText, $limit = 1, $type = 'all', $orderDirection = 'desc');

    /**
     * @param $pairId
     * @param int $limit
     * @param string $type Values can be sell|ask or bid|buy. Default is 'all'
     * @param string $orderDirection
     * @param int $decimal
     *
     * @return array|bool
     */
    public function getDepthByPairId($pairId, $decimal, $limit = 1, $type = 'all', $orderDirection = 'desc');

    /**
     * @param $pairId
     * @param $user
     * @param int $limit
     * @param int $offset
     * @param array $filters
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getLatestExecution($pairId, $user, $limit = 100, $offset = 0, $filters=[]);

    /**
     * @param string $term
     *
     * @return array
     */
    public function searchPair($term);

    /**
     * @param $term
     *
     * @return array
     */
    public function searchCoin($term);

    /**
     * Get coin
     *
     * @param string $symbol
     * @return \Buzzex\Models\ExchangeItem
     */
    public function getCoin($symbol);

    /**
     * @param ExchangePair $pair
     *
     * @return mixed
     */
    public function updateExchangePairStats(ExchangePair $pair);

    /**
     * @param $pairId
     * @param int $limit
     * @param string $type Values can be sell|ask or bid|buy. Default is 'all'
     * @param string $orderDirection
     * @param int $decimal
     * @param string $module
     *
     * @return array|bool
     */
    public function getOrderBook($pairId, $decimal, $limit = 1, $type = 'all', $orderDirection = 'desc', $module = '');
}