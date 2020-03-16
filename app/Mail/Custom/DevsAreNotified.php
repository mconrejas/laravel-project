<?php

namespace Buzzex\Mail\Custom;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DevsAreNotified extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $dateTime;

    /**
     * @var string
     */
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data = array())
    {
        $this->dateTime = Carbon::now();
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(
                config('mail.system_emails.no_reply.email'),
                config('mail.system_emails.no_reply.name')
            )
            ->subject('[' . config('app.name') . '] '.$this->data['subject'])
            ->markdown('emails.customs.notify-devs');
    }
}
