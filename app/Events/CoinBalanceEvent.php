<?php

namespace Buzzex\Events;

use Buzzex\Models\ExchangeItem;
use Buzzex\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CoinBalanceEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var $user_id
     */
    protected $user_id = 0;

    /**
     * @var $data
     */
    protected $data;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ExchangeItem $item, User $user)
    {
        $this->user_id = $user->id;
        $this->data = array('ticker' => $item->symbol, 'balance' => $user->getFundsByCoin($item->symbol));
    }

    /**
    *  When to broadcast.
    *
    * @return array
    */
    public function broadcastWhen()
    {
        return !empty($this->data) && $this->user_id > 0;
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
        return new PrivateChannel('CoinBalanceChannel_'.$this->user_id);
    }
}
