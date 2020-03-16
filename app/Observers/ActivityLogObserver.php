<?php

namespace Buzzex\Observers;

use Spatie\Activitylog\Models\Activity as Activity;

class ActivityLogObserver
{
    /**
     * Handle the activitylog "created" event.
     *
     * @param  \Spatie\Activitylog\Models\Activity  $activity
     * @return void
     */
    public function created(Activity $activity)
    {
        //
    }
    
    /**
     * Handle the activitylog "restored" event.
     *
     * @param  \Spatie\Activitylog\Models\Activity  $activity
     * @return void
     */
    public function saving(Activity $activity)
    {
        $request = request();

        $activity->properties = $activity->properties->merge([
                'is_ajax' => $request->ajax(),
                'source_ip' =>  last($request->getClientIps()),
                'source_url' => $request->fullUrl(),
                'user_agent' => $request->header('user-agent')
            ]);
    }

    /**
     * Handle the activitylog "updated" event.
     *
     * @param  \Spatie\Activitylog\Models\Activity  $activity
     * @return void
     */
    public function updated(Activity $activity)
    {
        //
    }

    /**
     * Handle the activitylog "deleted" event.
     *
     * @param  \Spatie\Activitylog\Models\Activity  $activity
     * @return void
     */
    public function deleted(Activity $activity)
    {
        //
    }

    /**
     * Handle the activitylog "restored" event.
     *
     * @param  \Spatie\Activitylog\Models\Activity  $activity
     * @return void
     */
    public function restored(Activity $activity)
    {
        //
    }

    /**
     * Handle the activitylog "force deleted" event.
     *
     * @param  \Spatie\Activitylog\Models\Activity  $activity
     * @return void
     */
    public function forceDeleted(Activity $activity)
    {
        //
    }
}
