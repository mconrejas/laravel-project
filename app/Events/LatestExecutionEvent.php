<?php

namespace Buzzex\Events;

use Buzzex\Models\ExchangeFulfillment;
use Buzzex\Models\ExchangeOrder;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LatestExecutionEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    /**
     * @var $pair_text
     */
    protected $pair_text = "";


    /**
     * Create a new event instance.
     * @param astring $pair_text
     * @return void
     */
    public function __construct($pair_text)
    {
        $this->pair_text = $pair_text;
    }

    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen()
    {
        return !empty($this->pair_text);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return array('pair_text' => $this->pair_text );
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('LatestExecutionChannel_'.$this->pair_text);
    }
}
