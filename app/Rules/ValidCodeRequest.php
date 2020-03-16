<?php

namespace Buzzex\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use Buzzex\Models\UserRequests;
use Auth;
use Carbon\Carbon;

class ValidCodeRequest implements ImplicitRule
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
        $user = Auth::user();

        $userRequest = $user->getRequestByCode($value);

        if (!$userRequest) {
            return false;
        }

        $now = Carbon::now();
        $difference = $now->diffInSeconds(Carbon::parse($userRequest->created_at));

        return ($difference <= config('codes.expiration', 600)) ;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The code you enter is an invalid  or an expired code.');
    }
}
