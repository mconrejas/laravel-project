<?php

namespace Buzzex\Events;

use Buzzex\Models\ExchangeOrder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderBookAddedOrUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var $pair_id
     */
    protected $pair_id;

    /**
     * @var $data
     */
    protected $data = [];

    /**
     * Create a new event instance.
     * @param Buzzex\Models\ExchangeOrder $exchangeOrder
     * @return void
     */
    public function __construct(ExchangeOrder $exchangeOrder)
    {
        $this->pair_id = $exchangeOrder->pair_id;

        $this->data = $exchangeOrder->toArray();
    }

    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen()
    {
        return !empty($this->pair_id) && !empty($this->data);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return (array) $this->data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('OrderBookChannel_'.$this->pair_id);
    }
}
