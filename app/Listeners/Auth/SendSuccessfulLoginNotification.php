<?php

namespace Buzzex\Listeners\Auth;

use Buzzex\Mail\Auth\LoginSuccessful;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Jenssegers\Agent\Agent;

class SendSuccessfulLoginNotification implements ShouldQueue
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $data_latest = $event->user->userSignin()->latest()->first();
        $data_previous = $event->user->userSignin()->latest()->skip(1)->first();
        if(!$data_previous || !$data_latest ){
            return null;
        }
        if($data_previous->ip == $data_latest->ip
            && strtolower($data_previous->device['platform']) == strtolower($data_latest->device['platform'])
            && strtolower($data_previous->device['browser']) == strtolower($data_latest->device['browser'])){
            return null;
        }
        Mail::to($event->user)->send(new LoginSuccessful($event->user));
    }
}
