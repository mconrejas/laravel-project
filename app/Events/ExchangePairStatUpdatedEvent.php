<?php

namespace Buzzex\Events;

use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Models\ExchangePairStat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExchangePairStatUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var $data
     */
    protected $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ExchangePairStat $pair_stat)
    {
        $market = app(Marketable::class);
        $this->data = (array) $market->getPairInfoByPairId($pair_stat->pair_id);
    }

    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen()
    {
        return !empty($this->data);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return $this->data;
    }
    
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new Channel('PairStatsChannel_'. $this->data['pair_id']),
            new Channel('MarketBaseChannel_'. $this->data['base'])
        ];
    }
}
