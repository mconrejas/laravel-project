<?php

use Buzzex\Broadcasting\PublicPresenceChannel;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('PublicPresenceChannel', PublicPresenceChannel::class);

Broadcast::channel('PairBalancesChannel_{user_id}_{pair_text}', function ($user, $user_id) {
    return (int)$user->id === (int)$user_id;
});

Broadcast::channel('CoinBalanceChannel_{user_id}', function ($user, $user_id) {
    return (int)$user->id === (int)$user_id;
});

Broadcast::channel('Buzzex.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
