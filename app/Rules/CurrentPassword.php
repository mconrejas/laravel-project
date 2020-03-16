<?php

namespace Buzzex\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use Auth;
use Hash;

class CurrentPassword implements ImplicitRule
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
        return Hash::check($value, Auth::user()->password); 
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Current password doesn\'t match with account password');
    }
}
