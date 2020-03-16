<?php

namespace Buzzex\Mail\Auth;

use Carbon\Carbon;
use Buzzex\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConfirmationCodeSuccessful extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $dateTime;

    /**
     * @var string
     */
    public $request;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user,$request )
    {
        $this->user = $user;
        $this->dateTime = Carbon::now();
        $this->request = $request;
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
            ->subject('[' . config('app.name') . '] Confirmation Code')
            ->markdown('emails.auth.confirmation-code-successful');
    }
}
