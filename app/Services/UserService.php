<?php

namespace Buzzex\Services;

use Buzzex\Contracts\User\CanManageOwnFund;
use Buzzex\Contracts\User\CanManageUser;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\ExchangeTransaction;
use Buzzex\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserService extends BaseService implements CanManageUser, CanManageOwnFund
{
    /**
     * UserService constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Get Funds
     *
     * @param boolean $includeOrders
     * @param User $user
     *
     * @return array
     */
    public function getFunds($includeOrders = false, User $user)
    {
        if ($includeOrders) {
            return $this->getFundsIncludingOrders($user, []);
        }

        return $this->getFundsWithoutOrders($user, []);
    }

    /**
     * Get Funds by tickers
     *
     * @param boolean $includeOrders
     * @param User $user
     * @param array $tickers
     * @return array
     */
    public function getFundsByTickers($includeOrders = false, User $user, $tickers = [])
    {
        if ($includeOrders) {
            return $this->getFundsIncludingOrders($user, $tickers);
        }

        return $this->getFundsWithoutOrders($user, $tickers);
    }

    /**
     * @param bool $includeOrders
     *
     * @return array
     */
    public function getAllFunds($includeOrders = false)
    {
        if ($includeOrders) {
            return $this->getFundsIncludingOrders(null, []);
        }

        return $this->getFundsWithoutOrders(null, []);
    }

    /**
     * Get funds without open orders
     *
     * @param User $user
     * @param array $tickers
     * @return array
     */
    protected function getFundsWithoutOrders($user, $tickers = [])
    {
        $data = $this->getFundsIncludingOrders($user, $tickers);

        $openOrders = $this->getOpenOrders($user, $tickers);

        foreach ($openOrders as $item => $amount) {
            if (array_key_exists($item, $data)) {
                $data[$item] = $data[$item] - $amount;
            }
        }

        return $data;
    }

    /**
     * Get funds including open orders
     *
     * @param User $user
     * @param array tickers
     * @return array
     */
    protected function getFundsIncludingOrders($user, $tickers = [])
    {
        $time = Carbon::now()->timestamp;

        $items = (new ExchangeItem())->newQuery()->where('deleted', 0);
        if (is_array($tickers) && !empty($tickers)) {
            $items = $items->whereIn('symbol', $tickers);
        }

        $items = $items->get();

        $itemsKeyValue = $item_ids = [];
        foreach ($items as $item) {
            if ($item->type === 4) {
                continue;
            }
            if (is_array($tickers) && !empty($tickers)) {
                $item_ids[] = $item->item_id;
            }
            $itemsKeyValue[$item->item_id] = $item->symbol;
        }

        $transactions = (new ExchangeTransaction())->newQuery()
            ->select(DB::raw('item_id, sum(amount) as total'));

        if ($user instanceof User) {
            $transactions = $transactions->where('user_id', $user->id);
        }else{
            $transactions = $transactions->where('user_id','<>',parameter('external_exchange_order_user_id', 1));
        }

        if (!empty($item_ids)) {
            $transactions = $transactions->whereIn('item_id', $item_ids);
        }
        
        $transactions = $transactions->where('cancelled', 0)
            ->where(function ($query) use ($time) {
                $query->where('released', '>', 0)
                    ->where('released', '<=', $time)
                    ->orWhere('type', 'withdrawal-request');
            })
            ->groupBy('item_id')
            ->get();

        $data = [];

        foreach ($transactions as $transaction) {
            if (array_key_exists($transaction->item_id, $itemsKeyValue)) {
                $data[$itemsKeyValue[$transaction->item_id]] = $transaction->total;
            }
        }

        return $data;
    }

    /**
     * @param User $user
     * @param array $tickers
     * @return array
     */
    public function getOpenOrders($user, $tickers = [])
    {
        $buyOrders = $this->getReservedInOrders($user, 'BUY', $tickers);
        $sellOrders = $this->getReservedInOrders($user, 'SELL', $tickers);

        $data = [];

        foreach ($buyOrders as $item => $buyOrderAmount) {
            $data[$item] = $buyOrderAmount;
        }

        foreach ($sellOrders as $item => $sellOrderAmount) {
            if (array_key_exists($item, $data)) {
                $data[$item] = $data[$item] + $sellOrderAmount;
            } else {
                $data[$item] = $sellOrderAmount;
            }
        }

        return $data;
    }

    /**
     * Get reserved in orders
     *
     * @param User $user
     * @param array $tickers
     *
     * @return array
     */
    protected function getReservedInOrders($user, $type, $tickers = [])
    {
        $items = (new ExchangeItem())->newQuery()->where('deleted', 0);

        if (is_array($tickers) && !empty($tickers)) {
            $items = $items->whereIn('symbol', $tickers);
        }
        $items = $items->get();

        $itemsKeyValue = $item_ids = [];
        foreach ($items as $item) {
            if ($item->type === 4) {
                continue;
            }
            if (is_array($tickers) && !empty($tickers)) {
                $item_ids[] = $item->item_id;
            }
            $itemsKeyValue[$item->item_id] = $item->symbol;
        }

        $select = "distinct(exchange_orders.order_id), exchange_pairs.item2, (sum(exchange_orders.target_amount - exchange_orders.fulfilled_target_amount)+(sum(exchange_orders.target_amount - exchange_orders.fulfilled_target_amount)*(exchange_orders.fee/exchange_orders.target_amount))) as reserved";

        if ($type === 'SELL') {
            $select = "exchange_pairs.item1, sum(amount - fulfilled_amount) as reserved";
        }

        $orders = (new ExchangeOrder())->newQuery()
            ->select(
                DB::raw($select)
            )
            ->leftJoin('exchange_pairs', 'exchange_pairs.pair_id', '=', 'exchange_orders.pair_id')
            ->where('exchange_orders.type', $type)
            ->where('exchange_orders.completed', 0);

        if ($type === 'SELL') {
            $orders->where('exchange_orders.amount', '>', 0);
        } else {
            $orders->where('exchange_orders.target_amount', '>', 0);
        }

        if ($user instanceof User) {
            $orders->where('exchange_orders.user_id', $user->id);
        }else{
            $orders->where('exchange_orders.user_id','<>',parameter('external_exchange_order_user_id', 1));
        }

        if (!empty($item_ids)) {
            $orders = $orders->whereIn(($type === 'SELL' ? 'exchange_pairs.item1' : 'exchange_pairs.item2'), $item_ids);
        }

        if ($type === 'SELL') {
            $orders->groupBy('exchange_pairs.item1');
        } else {
            $orders->groupBy('exchange_pairs.item2');
        }

        $orders = $orders->get();

        $data = [];

        foreach ($orders as $order) {
            if ($type === 'SELL' && isset($itemsKeyValue[$order->item1])) {
                $data[$itemsKeyValue[$order->item1]] = $order->reserved;
            }

            if ($type === 'BUY' && isset($itemsKeyValue[$order->item2])) {
                $data[$itemsKeyValue[$order->item2]] = $order->reserved;
            }
        }

        return $data;
    }
}
