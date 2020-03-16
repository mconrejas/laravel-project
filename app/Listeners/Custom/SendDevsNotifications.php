<?php

namespace Buzzex\Listeners\Custom;

use Buzzex\Mail\Custom\DevsAreNotified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Jenssegers\Agent\Agent;

class SendDevsNotifications implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $target = array_key_exists('target', $event->data) ? $event->data['target'] : env('DEVS_TARGET_EMAIL', '');
        Mail::to($target)->send(new DevsAreNotified($event->data));
    }
}
