<?php

namespace Buzzex\Crypto\Currency;

use Buzzex\Crypto\Currency\Coins\Coin;

class CoinValidatorFactory
{
    /**
     * @var array
     */
    protected $validators = [];

    /**
     * Create validator
     *
     * @param  Coin $coin [description]
     *
     * @return \Buzzex\Crypto\Currency\Coins\Validators\Validator
     */
    public static function create(Coin $coin)
    {
        $shortName = $coin->getShortName();
        if ($coin->getCommunityID() > 0) {
            $coin->setShortName("ACT");
        } elseif ($coin->getIsErc20()) {
            $coin->setShortName("ERC20");
        }
        if (array_key_exists($coin->getShortName(), static::getValidators())) {
            $validator = static::getValidators()[$coin->getShortName()];

            if ($coin->getCommunityID() > 0 || $coin->getIsErc20()) {
                $coin->setShortName($shortName);
            }

            return new $validator;
        }
    }

    /**
     * Get validators
     *
     * @return array
     */
    public static function getValidators()
    {
        $supportedCoins = CoinFactory::getSupportedCoins();
        $validators = [];

        foreach ($supportedCoins as $coin) {
            $coin = new $coin;
            $namespace = "\\Buzzex\\Crypto\\Currency\\Coins\\Validators";
            $validatorName = ucfirst(strtolower($coin->getName())) . 'Validator';
            $validatorClass = "{$namespace}\\{$validatorName}";

            $validators[$coin->getShortName()] = $validatorClass;
        }

        return $validators;
    }
}
