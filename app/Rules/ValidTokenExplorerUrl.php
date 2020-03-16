<?php

namespace Buzzex\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidTokenExplorerUrl implements Rule
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
     * @param  string $attribute
     * @param  mixed $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $value = collect(explode(' ', preg_replace("/[\r\n]+/", " ", $value)));
        $pass = $value->filter(function($url, $key) {
            return filter_var($url, FILTER_VALIDATE_URL) === FALSE;
        })->count();

        return $pass == 0 ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Blockchain explorer url format is invalid.');
    }
}
