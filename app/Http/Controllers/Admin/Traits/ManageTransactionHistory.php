<?php

namespace Buzzex\Http\Controllers\Admin\Traits;

use Buzzex\Models\ExchangeFulfillment;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Models\ExchangeTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait ManageTransactionHistory
{
    /**
     *
     * @param string $ticker
     * @param string $type 'deposits'|'withdrawals'
     * @param array $user_ids
     * @param int $limit
     * @param int $skip
     * @return mixed|array
     */
    public function getItemHistories($ticker = 'all', $type = 'deposits', $user_ids = array(), $limit = 100, $skip = 0)
    {
        if ($type == 'deposits') {
            return $this->getItemDepositsHistory($ticker, $user_ids, $limit, $skip);
        }
        return $this->getItemWithdrawalsHistory($ticker, $user_ids, $limit, $skip);
    }

    /**
     *
     * @param string|int $ticker
     * @param int $limit
     * @param array $user_ids
     * @param int $skip
     * @return array
     */
    public function getItemWithdrawalsHistory($ticker = 'all', $user_ids = array(), $limit = 100, $skip = 0)
    {
        $count = 0;
        $history  = ExchangeTransaction::where('exchange_transactions.type', '=', 'withdrawal-request')
                    ->selectRaw('exchange_transactions.*,exchange_items.symbol')
                    ->join('exchange_items', 'exchange_items.item_id', '=', 'exchange_transactions.item_id')
                    ->where('exchange_items.type', '<>', 4);

        if ($ticker != 'all') {
            $history = $history->where('exchange_items.symbol', '=', $ticker);
        }
        if (!empty($user_ids)) {
            $history = $history->whereIn('exchange_transactions.user_id', $user_ids);
        }

        $count = $history->count();

        $history = $history->skip($skip)->take($limit)->orderBy('transaction_id', 'desc')->get();
        if ($history) {
            $history = $history->mapWithKeys(function ($history, $key) {
                return [
                    $key => [
                        'txid' => $history->transaction_id,
                        'date' => Carbon::createFromTimestamp($history->created)->format('Y-m-d H:i:s'),
                        'user_id' => $history->user_id,
                        'amount' => currency(abs($history->amount) - $history->fee),
                        'fee' => $history->fee,
                        'item' => $history->symbol,
                        'status' => $history->getStatus(),
                        'details' => $history->toArray()
                    ]
                ];
            });
        }

        return array('count' => $count, 'data' => $history);
    }

    /**
     *
     * @param string|int $ticker
     * @param array $user_ids
     * @param int $limit
     * @param int $skip
     * @return array
     */
    public function getItemDepositsHistory($ticker = 'all', $user_ids = array(), $limit = 100, $skip = 0)
    {
        $count = 0;
        $history  = ExchangeTransaction::where('exchange_transactions.type', 'like', 'deposit%')
                    ->selectRaw('exchange_transactions.*,exchange_items.symbol')
                    ->join('exchange_items', 'exchange_items.item_id', '=', 'exchange_transactions.item_id')
                    ->where('exchange_items.type', '<>', 4);

        if ($ticker != 'all') {
            $history = $history->where('exchange_items.symbol', '=', $ticker);
        }
        
        if (!empty($user_ids)) {
            $history = $history->whereIn('exchange_transactions.user_id', $user_ids);
        }

        $count = $history->count();

        $history = $history->skip($skip)->take($limit)->orderBy('transaction_id', 'desc')->get();
        if ($history) {
            $history = $history->mapWithKeys(function ($history, $key) {
                return [
                    $key => [
                        'txid' => $history->transaction_id,
                        'date' => Carbon::createFromTimestamp($history->created)->format('Y-m-d H:i:s'),
                        'user_id' => $history->user_id,
                        'amount' => currency(abs($history->amount) - $history->fee),
                        'fee' => $history->fee,
                        'item' => $history->symbol,
                        'status' =>  $history->getStatus(),
                        'source' => str_replace(array("_transactions","_".strtolower($history->symbol),"_erc20","_act"), "", $history->module)
                    ]
                ];
            });
        }

        return array('count' => $count, 'data' => $history);
    }

    /**
     *
     * @param string|int $pair_id
     * @param string $type 'orders'|'bids'
     * @param array $user_ids
     * @param int $limit
     * @param int $skip
     * @return mixed|array
     */
    public function getPairHistories($pair_id = 'all', $type = 'orders', $user_ids = [], $limit = 100, $skip = 0)
    {
        if ($type == 'orders') {
            return $this->getOrders($pair_id, $user_ids, $limit, $skip);
        }
        return $this->getBidsOrder($pair_id, $user_ids, $limit, $skip);
    }

    /**
     *
     * @param string|int $pair_id
     * @param array $user_ids
     * @param int $limit
     * @param int $skip
     * @return array
     */
    public function getBidsOrder($pair_id = 'all', $user_ids = [], $limit = 100, $skip = 0)
    {
        $count = 0;
        $items = ExchangeFulfillment::select(DB::raw('exchange_fulfillments.*, exchange_orders.user_id, exchange_orders.pair_id, exchange_orders.type, exchange_orders.user_id'))
                ->leftJoin('exchange_orders', function ($join) {
                    $join->on(function ($query) {
                        $query->on('exchange_orders.order_id', '=', 'exchange_fulfillments.sell_order_id');
                        $query->orOn('exchange_orders.order_id', '=', 'exchange_fulfillments.buy_order_id');
                    });
                });

        if (!empty($user_ids)) {
            $items = $items->whereIn('exchange_orders.user_id', $user_ids);
        }
        if ($pair_id != 'all') {
            $items =  $items->where('exchange_orders.pair_id', '=', $pair_id);
            $count =  $items->count();
        } else {
            $count = ExchangeFulfillment::count();
        }

        $items = $items->skip($skip)->take($limit)->orderBy('exchange_fulfillments.created', 'desc')->get();

        if ($items) {
            $items = $items->mapWithKeys(function ($items, $key) {
                $pair = ExchangePairStat::where('pair_id', '=', $items->pair_id)->first();
                return [
                    $key => [
                        'date' => Carbon::createFromTimestamp($items->created)->format('Y-m-d H:i:s'),
                        'pair' => $pair->pair_text,
                        'type' => $items->type,
                        'amount' => $items->amount,
                        'price' => $items->price,
                        'total' => sprintf("%.8f", round(($items->price * $items->amount), 8)),
                        'user_id' => $items->user_id,
                    ]
                ];
            });
        }

        return array('count' => $count, 'data' => $items);
    }
            
    /**
     *
     * @param string|int $pair_id
     * @param array $user_ids
     * @param int $limit
     * @param int $skip
     * @return array
     */
    public function getOrders($pair_id = 'all', $user_ids = [], $limit = 100, $skip = 0)
    {
        $count = 0;
        $items = ExchangeOrder::select(DB::raw('exchange_orders.*'));

        if ($pair_id != 'all') {
            $items = $items->where('pair_id', '=', $pair_id);
        }
        if (!empty($user_ids)) {
            $items = $items->whereIn('exchange_orders.user_id', $user_ids);
        }

        $count = $items->count();
        $items = $items->skip($skip)->take($limit)->orderBy('created', 'desc')->get();

        if ($items) {
            $items = $items->mapWithKeys(function ($item, $key) {
                return [
                    $key => [
                        'user_id' => $item->user_id,
                        'user_name' => $item->user ? $item->user['email'] : '',
                        'date' => Carbon::createFromTimestamp($item->created)->format('Y-m-d H:i:s'),
                        'time' => Carbon::createFromTimestamp($item->created)->format('Y.m.d H:i:s'),
                        'time_order' => $item->created,
                        'closed_date' => $item->completed == 0 ? 'OPEN' : Carbon::createFromTimestamp($item->completed)->format('Y-m-d H:i:s'),
                        'pair' => $item->pairStat->pair_text,
                        'type' => $item->type,
                        'price' => $item->price == 0 ? 'Dynamic <sup>1</sup>' : $item->price,
                        'amount' => $item->amount == 0 ? $item->amount.'<sup>2</sup>' : $item->amount,
                        'fulfilled_amount' =>$item->fulfilled_amount,
                        'remaining_amount' => sprintf("%.8f", round(($item->price * $item->amount), 8)),
                        'total' => sprintf("%.8f", round(($item->price * $item->amount), 8)),
                        'remaining_total' => sprintf("%.8f", round(($item->price * ($item->amount - $item->fulfilled_amount)), 8)) ,
                        'order_id'=> $item->order_id,
                    ]
                ];
            });
        }

        return array('count' => $count, 'data' => $items);
    }
}
