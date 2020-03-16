<?php

namespace Buzzex\Providers;

use Buzzex\Events\ConfirmationCodeEvent;
use Buzzex\Events\DevelopersEvent;
use Buzzex\Events\SendUserPasswordGeneratedEvent;
use Buzzex\Listeners\Auth\RecordUserSignInEvent;
use Buzzex\Listeners\Auth\SendSuccessfulCodeConfirmation;
use Buzzex\Listeners\Auth\SendSuccessfulGeneratedPassword;
use Buzzex\Listeners\Auth\SendSuccessfulLoginNotification;
use Buzzex\Listeners\Custom\SendDevsNotifications;
use Buzzex\Models\Activity;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Models\User;
use Buzzex\Observers\ActivityLogObserver;
use Buzzex\Observers\ExchangeOrderObserver;
use Buzzex\Observers\ExchangePairStatObserver;
use Buzzex\Observers\UserObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            SendSuccessfulLoginNotification::class,
            RecordUserSignInEvent::class,
        ],
        ConfirmationCodeEvent::class => [
            SendSuccessfulCodeConfirmation::class,
        ],
        SendUserPasswordGeneratedEvent::class => [
            SendSuccessfulGeneratedPassword::class,
        ],
        DevelopersEvent::class => [
            SendDevsNotifications::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->customObservers();
    }

    protected function customObservers()
    {
        User::observe(UserObserver::class);
        Activity::observe(ActivityLogObserver::class);
        ExchangePairStat::observe(ExchangePairStatObserver::class);
        ExchangeOrder::observe(ExchangeOrderObserver::class);
    }
}
