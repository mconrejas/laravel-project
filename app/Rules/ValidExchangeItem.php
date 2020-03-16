<?php

namespace Buzzex\Rules;

use Buzzex\Contracts\Exchange\Marketable;
use Illuminate\Contracts\Validation\Rule;

class ValidExchangeItem implements Rule
{
    /**
     * @var Marketable
     */
    private $markets;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Marketable $markets)
    {
        $this->markets = $markets;
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
        return $this->markets->isValidCoin($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Invalid exchange item.');
    }
}
