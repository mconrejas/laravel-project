<?php

namespace Buzzex\Console\Commands;

use Buzzex\Events\PairBalancesEvent;
use Buzzex\Events\LatestExecutionEvent;
use Buzzex\Events\TradingViewEvent;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\User;
use Buzzex\Notifications\OrderFulfilledNotification;
use Illuminate\Console\Command;

class NotifyForOrdersFulfilled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders-are-fulfilled:run {order_ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute when orders given orders ids are fulfilled';

    /**
     * @var Marketable
     */
    private $order_ids = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->order_ids = $this->argument('order_ids') ?? null;

        if (is_null($this->order_ids)) {
            return;
        }

        $orders = ExchangeOrder::whereIn('exchange_orders.order_id', explode(',', $this->order_ids))->get();

        if ($orders) {
            $distinct_ids = array();
            $systemUserId = parameter('external_exchange_order_user_id', 1);

            foreach ($orders as $key => $order) {
                //only notify to distict order id to avoid duplicates and if the owner is not the system
                if (!in_array($order->order_id, $distinct_ids) && $systemUserId != $order->user_id) {
                    $this->notifyOrderOwners($order);
                    $distinct_ids[] = $order->order_id;
                }
            }

            if (isset($orders[0])) {
                broadcast(new LatestExecutionEvent($orders[0]->pairStat->pair_text));
                broadcast(new TradingViewEvent($orders[0]->pair_id, $orders[0]->pairStat->pair_text));
            }
        }
        return;
    }

    /**
     * @param ExchangeOrder $exchangeOrder
     *
     * @return void
     */
    protected function notifyOrderOwners(ExchangeOrder $exchangeOrder)
    {
        $fullfilled_percentage = $exchangeOrder->getFulfilledPercentage();
        
        if (!$exchangeOrder->isCancelled() && $fullfilled_percentage > 0) {
            $user = User::findOrFail($exchangeOrder->user_id);
            $user->notify(new OrderFulfilledNotification($exchangeOrder));
            broadcast(new PairBalancesEvent($exchangeOrder->pairStat->pair_text, $user));
        }
    }
}
