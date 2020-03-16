<?php

namespace Buzzex\Mail\Auth;

use Buzzex\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginSuccessful extends Mailable
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
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->dateTime = Carbon::now();
        $this->data = $user->userSignin()->latest()->first();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.system_emails.no_reply.email'), config('mail.system_emails.no_reply.name'))
            ->subject('[' . config('app.name') . '] Successful Login From New IP or Device')
            ->markdown('emails.auth.login-successful');
    }
}
