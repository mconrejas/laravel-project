<?php

namespace Buzzex\Events;

use Illuminate\Queue\SerializesModels;
use Buzzex\Models\User;
use Buzzex\Models\UserRequests;

class ConfirmationCodeEvent
{
    use SerializesModels;

    public $request;

    public $user;
    
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, UserRequests $request)
    {
        $this->request = $request;
        $this->user = $user;
    }

}
