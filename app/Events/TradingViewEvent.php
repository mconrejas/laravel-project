<?php

namespace Buzzex\Events;

use Buzzex\Contracts\User\Tradable;
use Buzzex\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TradingViewEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $data = array();

    protected $pair_text = "";

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($pair_id, $pair_text = "")
    {
        if (!is_null($pair_id) && !empty($pair_text)) {
            $trading = app(Tradable::class);
            $this->data =  (array) $trading->getOhlcv(['pair_id' => $pair_id, 'is_last' => 1 ]);
            $this->pair_text = $pair_text;
        }
        // \Illuminate\Support\Facades\Log::debug(json_encode($this->data));
    }
    
    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen()
    {
        return !empty($this->data) && !empty($this->pair_text);
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
        return new Channel('TradingViewChannel_' . $this->pair_text);
    }
}
