<?php

namespace Buzzex\Http\Controllers\Admin\Traits;

use Buzzex\Models\ExchangeFulfillment;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\ExchangeTransaction;
use Illuminate\Support\Facades\DB;

trait ManageWallets
{
    /**
     * @param int $item_id
     * @return float
     */
    public function getTotalWithdrawals($item_id)
    {
        $withdrawals =  ExchangeTransaction::select(DB::raw('sum(amount) as total'))
            ->withdrawals()
            ->where('item_id', $item_id)
            ->where('cancelled', '=', 0)
            ->first();

        return $withdrawals ? currency(abs($withdrawals->total)) : 0;
    }
    /**
     * @param int $item_id
     * @return float
     */
    public function getTotalDeposits($item_id)
    {
        $deposits =  ExchangeTransaction::select(DB::raw('sum(amount) as total'))
            ->deposits()
            ->where('item_id', $item_id)
            ->where('amount', '>', 0)
            ->where('released', '>', 0)
            ->first();
    
        return $deposits ? currency($deposits->total) : 0;
    }
    /**
     * @param int $item_id
     * @return float
     */
    public function getTotalTradeFee($item_id)
    {
        $fees = ExchangeFulfillment::select(DB::raw('sum(exchange_fulfillments.fee) as total'))
            ->leftJoin('exchange_orders', 'exchange_fulfillments.sell_order_id', '=', 'exchange_orders.order_id')
            ->leftJoin('exchange_pairs', 'exchange_orders.pair_id', '=', 'exchange_pairs.pair_id')
            ->where('exchange_pairs.item2', '=', $item_id)
            ->first();

        return $fees ? currency($fees->total * 2) : 0; //multiply by 2 since the fee is for both seller and buyer
    }

    /**
     * @param int $item_id
     * @return float
     */
    public function getTotalReserveInOrders($item_id)
    {
        $buy_reserved = $this->getTotalReserveInBuyOrders($item_id) ?: 0;
        $sell_reserved = $this->getTotalReserveInSellOrders($item_id) ?: 0;

        return ($buy_reserved + $sell_reserved);
    }

    /**
     * @param int $item_id
     * @return float
     */
    public function getTotalReserveInBuyOrders($item_id)
    {
        $reserved = ExchangeOrder::select(DB::raw('sum(target_amount - fulfilled_target_amount)+(sum(target_amount - fulfilled_target_amount)*(fee/target_amount)) as reserved'))
            ->leftJoin('exchange_pairs', 'exchange_orders.pair_id', '=', 'exchange_pairs.pair_id')
            ->where('type', '=', 'BUY')
            ->where('completed', '=', 0)
            ->where('target_amount', '>', 0)
            ->where('exchange_pairs.item2', '=', $item_id)
            ->where('user_id','<>',parameter('external_exchange_order_user_id', 1))
            ->first();

        return $reserved ? $reserved->reserved : 0;
    }

    /**
     * @param int $item_id
     * @return float
     */
    public function getTotalReserveInSellOrders($item_id)
    {
        $reserved = ExchangeOrder::select(DB::raw('sum(amount - fulfilled_amount) as reserved'))
            ->leftJoin('exchange_pairs', 'exchange_orders.pair_id', '=', 'exchange_pairs.pair_id')
            ->where('type', '=', 'SELL')
            ->where('completed', '=', 0)
            ->where('amount', '>', 0)
            ->where('exchange_pairs.item1', '=', $item_id)
            ->where('user_id','<>',parameter('external_exchange_order_user_id', 1))
            ->first();

        return $reserved ? $reserved->reserved : 0;
    }
}
