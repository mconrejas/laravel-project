<?php

namespace Buzzex\Http\Controllers\Main\Traits;

use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Models\ExchangePair;

trait Validable
{
    /**
     * @param string $coin
     * @return boolean
     */
    public function validExchangeRequest($base, $target)
    {
        $pair_text = strtoupper($base.'_'.$target);
        return $this->validTargetCoin($target) && $this->validBaseCoin($coin) && validPairText($pair_text);
    }

    /**
     * @param string $coin
     * @return boolean
     */
    public function validTargetCoin($coin)
    {
        $market = app(Marketable::class);
        return $market->isValidCoin(strtoupper($coin));
    }

    /**
     * @param string $coin
     * @return boolean
     */
    public function validBaseCoin($coin)
    {
        $bases = getBases();
        return in_array(strtoupper($coin), $bases);
    }

    /**
     * @param string $pair_text
     * @return boolean
     */
    public function validPairText($pair_text)
    {
        $marketable = app(Marketable::class);
        return (bool) $marketable->getPairInfoByPairText($pair_text);
    }

    /**
     * @param string $pair_text
     * @return boolean
     */
    public function isActTokenPair(ExchangePair $exchangePair)
    {
        return ($exchangePair->exchangeItemOne->type != 4 && $exchangePair->exchangeItemTwo->type != 4);
    }
}
