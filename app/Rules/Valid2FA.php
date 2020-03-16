<?php

namespace Buzzex\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FALaravel\Google2FA;

class Valid2FA implements ImplicitRule
{
    protected $google2fa;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Google2FA $google2fa)
    {
        $this->google2fa = $google2fa;
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

        if (!$user || !$user->is2FAEnable()) {
            return true;
        }

        $secret = $user->passwordSecurity->google2fa_secret;

        return $this->google2fa->verifyGoogle2FA($secret, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The code is invalid!');
    }
}
