<?php

namespace Buzzex\Contracts\User;

use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Models\User;
use Buzzex\Services\ExchangeService;

interface Tradable
{
    /**
     * @param User $user
     * @param array $filters
     *
     * @return array
     */
    public function getCurrentOrders(User $user, $filters = [], $orderBy = 'order_id', $orderDirection = 'desc');

    /**
     * @param User $user
     * @param array $filters
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getOrderHistory(User $user, $filters = []);

    /**
     * @param array $params
     *
     * @return array
     * @throws InvalidPairException
     */
    public function getOhlcv(array $params = []);

    /**
     * Trade
     *
     * @param User $user
     * @param array $params
     * @return ExchangeOrder
     */
    public function trade(User $user, array $params);

    /**
     * @param ExchangeOrder $stopLimitOrder
     *
     * @return bool
     */
    public function processStopLimit(ExchangeOrder $stopLimitOrder);

    /**
     * Create withdrawal request
     *
     * @param User $user
     * @param string $coin Coin Name
     * @param string $address
     * @param float $amount
     * @return boolean
     */
    public function withdraw(User $user, $coin, $address, $amount);

    /**
     * @param $coinSymbol
     *
     * @return mixed
     */
    public function downloadDeposits($coinSymbol,$debug=false);

    /**
     * @param $address
     * @param ExchangeItem $exchangeItem
     *
     * @return boolean
     * @throws CoinNotFoundException
     * @throws \Exception
     */
    public function downloadDepositsByAddress($address, ExchangeItem $exchangeItem,$debug=false);

    /**
     * @param $coinSymbol
     * @param $transaction_id
     * @param $limit
     * @param bool $debug
     * @return mixed
     */
    public function updateBlockchainConfirmations($coinSymbol,$transaction_id,$limit,$debug=false);

    /**
     * @param User $user
     * @param ExchangeItem $item
     * @param $amount
     *
     * @return mixed
     */
    public function reloadFunds(User $user, ExchangeItem $item, $amount);


    /**
     * @param ExchangeService $exchange
     *
     * @return mixed
     */
    public function getOrderbookFromExternalExchange(ExchangeService $exchange, $limit);

    /**
     * @param array $orderBook 
     * @param bool $deleteOld
     *
     * @return mixed
     */
    public function insertExternalOrderBook(array $orderBook, $deleteOld = true);

    /**
    * @param User $user
    * @param ExchangeOrder $order
    *
    * @return mixed
    */
    public function cancelOrder(ExchangeOrder $order);

    /**
     * @param string|null $symbol
     *
     * @return mixed
     */
    public function getApprovedWithdrawals($symbol=null);

    /**
     * @param array $transactions
     */
    public function postWithdrawals(array $transactions);
}
