<?php

namespace Buzzex\Mail\Custom;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordResetNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $dateTime;

    /**
     * @var array
     */
    public $data;


    /**
     *@var $user
     */
    public $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data = array(), $password)
    {
        $this->dateTime = Carbon::now();
        $this->data = $data;
        $this->password = $password;
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
            ->to($this->data->email)
            ->subject('[' . config('app.name') . '] password reset request.')
            ->markdown('emails.customs.notify-password-reset');
    }
}