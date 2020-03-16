<?php

namespace Buzzex\Rules;

use Buzzex\Contracts\Exchange\Marketable;
use Illuminate\Contracts\Validation\Rule;

class ValidPair implements Rule
{
    /**
     * @var Marketable
     */
    private $marketable;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Marketable $marketable)
    {
        //
        $this->marketable = $marketable;
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
        return (bool)$this->marketable->getPairInfoByPairId($value)
            || (bool)$this->marketable->getPairInfoByPairText($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Invalid pair.');
    }
}
