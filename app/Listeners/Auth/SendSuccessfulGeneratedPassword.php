<?php

namespace Buzzex\Listeners\Auth;

use Buzzex\Mail\Auth\GeneratingPasswordSuccessful;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendSuccessfulGeneratedPassword implements ShouldQueue
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
        Mail::to($event->user)->send(new GeneratingPasswordSuccessful($event->user, $event->password));
    }
}
