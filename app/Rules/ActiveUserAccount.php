<?php

namespace Buzzex\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use Buzzex\Models\User;
use Auth;
use Hash;

class ActiveUserAccount implements ImplicitRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {   
        return User::where('email',$value)->pluck('blocked')->first()['isBlocked'] ? false : true; 
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The :attribute blocked');
    }
}
