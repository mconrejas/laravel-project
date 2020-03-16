<?php

namespace Buzzex\Broadcasting;

use Buzzex\Models\User;

class PublicPresenceChannel
{
    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \Buzzex\Models\User  $user
     * @return array|bool
     */
    public function join(User $user)
    {
        if ($user instanceof User) {
            return array(
                'id' => $user->id,
                'name' => $user->name
            );
        }
        
        return false;
    }
}
