<?php

namespace Buzzex\Observers;

use Buzzex\Events\OrderBookAddedOrUpdatedEvent;
use Buzzex\Events\PairBalancesEvent;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\User;

class ExchangeOrderObserver
{
    /**
     * Handle the ExchangeOrder "created" event.
     *
     * @param  ExchangeOrder  $exchangeOrder
     * @return void
     */
    public function created(ExchangeOrder $exchangeOrder)
    {
        broadcast(new OrderBookAddedOrUpdatedEvent($exchangeOrder));
    }

    /**
     * Handle the ExchangeOrder "updated" event.
     *
     * @param  ExchangeOrder  $exchangeOrder
     * @return void
     */
    public function updated(ExchangeOrder $exchangeOrder)
    {
        broadcast(new OrderBookAddedOrUpdatedEvent($exchangeOrder));
        broadcast(new PairBalancesEvent($exchangeOrder->pairStat->pair_text, $exchangeOrder->user));
    }
}
