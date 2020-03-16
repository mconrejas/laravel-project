<?php

namespace Buzzex\Events;

use Buzzex\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PairBalancesEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    /**
     * @var $data
     */
    protected $data;
    /**
     * @var $pair_text
     */
    protected $pair_text;
    /**
     * @var $user_id
     */
    protected $user_id;
    
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($pair_text, User $user)
    {
        $this->user_id = $user->id;
        $this->data = $user->getFundsByPairText($pair_text);
        $this->pair_text = str_replace('_', '', $pair_text);
    }

    /**
     *  When to broadcast.
     *
     * @return array
     */
    public function broadcastWhen()
    {
        return !empty($this->data) && !empty($this->user_id);
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
        return new PrivateChannel('PairBalancesChannel_'.$this->user_id.'_'. $this->pair_text);
    }
}
