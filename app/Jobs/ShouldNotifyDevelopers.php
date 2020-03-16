<?php


namespace Buzzex\Jobs;


use Exception;
use Illuminate\Support\Facades\Mail;

trait ShouldNotifyDevelopers
{
    /**
     * The job failed to process.
     *
     * @param  Exception $exception
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        Mail::raw($exception->getMessage(), function($message) {
            $message->to(config('account.developer_emails'));
        });
    }
}