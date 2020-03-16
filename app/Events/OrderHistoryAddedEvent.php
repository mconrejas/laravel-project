<?php

namespace Buzzex\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderHistoryAddedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var $pair_id
     */
    protected $pair_id = "";

    /**
     * @var $data
     */
    protected $data = array();

    /**
     * Create a new event instance.
     * @param array $data
     * @return void
     */
    public function __construct($data)
    {
        if (is_array($data) && !empty($data)) {
            $this->pair_id = $data['pair_id'];
            $this->data = $data;
        }
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
        return new Channel('OrderHistoryChannel_'.$this->pair_id);
    }
}
