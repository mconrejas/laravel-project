<?php

namespace Buzzex\Listeners\Auth;

use Jenssegers\Agent\Agent;
use Buzzex\Models\UserSignin;
use GeoIP;

class RecordUserSignInEvent
{
    protected $agent;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $ip = get_ip_address();
        $event->user->userSignin()->create([
            'user_id' => $event->user->id,
            'ip' => $ip,
            'device' => json_encode(array(
                'device' => $this->agent->device(),
                'platform' => $this->agent->platform(),
                'browser' => $this->agent->browser(),
            )),
            'location' => json_encode(GeoIP::getLocation($ip)->toArray())
        ]);
    }
}
