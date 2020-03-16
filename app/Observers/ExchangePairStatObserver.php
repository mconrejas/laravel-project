<?php

namespace Buzzex\Observers;

use Buzzex\Events\ExchangePairStatUpdatedEvent;
use Buzzex\Models\ExchangePairStat;

class ExchangePairStatObserver
{
    /**
     * Handle the ExchangePair "updated" event.
     *
     * @param  Buzzex\Models\ExchangePairStat $exchangePairStat
     * @return void
     */
    public function updated(ExchangePairStat $exchangePairStat)
    {
        broadcast(new ExchangePairStatUpdatedEvent($exchangePairStat));
    }
}
