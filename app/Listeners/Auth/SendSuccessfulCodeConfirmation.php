<?php

namespace Buzzex\Listeners\Auth;

use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Buzzex\Mail\Auth\ConfirmationCodeSuccessful;

class SendSuccessfulCodeConfirmation implements ShouldQueue
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
        Mail::to($event->user)->send(new ConfirmationCodeSuccessful($event->user,$event->request));
    }
}
