<?php

namespace Buzzex\Events;

use Illuminate\Queue\SerializesModels;
use Buzzex\Models\User;

class SendUserPasswordGeneratedEvent
{
    use SerializesModels;

    /**
     *@var $user
     */
    public $user;

    /**
     *@var $user
     */
    public $password;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $pass)
    {
        $this->user = $user;
        $this->password  = $pass;
    }
}
