<?php

namespace Buzzex\Observers;

use Buzzex\Models\Role;
use Buzzex\Models\User;
use Illuminate\Support\Str;
use Keiko\Uuid\Shortener\Dictionary;
use Keiko\Uuid\Shortener\Number\BigInt\Converter;
use Keiko\Uuid\Shortener\Shortener;

class UserObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param  User  $user
     * @return void
     */
    public function created(User $user)
    {
        $uuid = Str::uuid();

        $shortener = new Shortener(
            Dictionary::createUnmistakable(), // or just pass your own characters set
            new Converter()
        );
        
        $user->affiliate_id = $shortener->reduce($uuid);
        $user->save();

        // assign default role for each created user
        if (! $user->hasRole('user')) {
            if (Role::where('name', '=', 'user')->count() == 0) {
                Role::create(['name'=>'user','guard_name' => 'web']);
            }
            $user->assignRole('user');
        }
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \Buzzex\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \Buzzex\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \Buzzex\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \Buzzex\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
