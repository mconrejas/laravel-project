<?php

namespace Buzzex\Rules;

use Buzzex\Crypto\Currency\Coins\Validators\AdzcoinValidator;
use Buzzex\Crypto\Currency\Coins\Validators\BitcoincashValidator;
use Buzzex\Crypto\Currency\Coins\Validators\BitcoingoldValidator;
use Buzzex\Crypto\Currency\Coins\Validators\BitcoinValidator;
use Buzzex\Crypto\Currency\Coins\Validators\BuzzexcoinValidator;
use Buzzex\Crypto\Currency\Coins\Validators\DashValidator;
use Buzzex\Crypto\Currency\Coins\Validators\DogecoinValidator;
use Buzzex\Crypto\Currency\Coins\Validators\Erc20tokensValidator;
use Buzzex\Crypto\Currency\Coins\Validators\EthereumclassicValidator;
use Buzzex\Crypto\Currency\Coins\Validators\EthereumValidator;
use Buzzex\Crypto\Currency\Coins\Validators\LitecoinValidator;
use Buzzex\Crypto\Currency\Coins\Validators\TronixtokenValidator;
use Buzzex\Crypto\Currency\Coins\Validators\TwinkletokenValidator;
use Buzzex\Models\ExchangeItem;

class ValidCoinAddress
{

    /**
     * Get All supported coin validator
     *
     * @return array
     */
    protected function supportedCoins()
    {
        return [
            'adz' => AdzcoinValidator::class,
            'btc' => BitcoinValidator::class,
            'bch' => BitcoincashValidator::class,
            'btg' => BitcoingoldValidator::class,
            'dash' => DashValidator::class,
            'doge' => DogecoinValidator::class,
            'erc20' => Erc20tokensValidator::class,
            'etc' => EthereumclassicValidator::class,
            'eth' => EthereumValidator::class,
            'ltc' => LitecoinValidator::class,
            'trx' => TronixtokenValidator::class,
            'twnkl' => TwinkletokenValidator::class,
            'bzx' => BuzzexcoinValidator::class,
        ];
    }

    /**
     * Get Validator class for certain coin
     *
     * @param string $coin
     *
     * @return string|mixed
     */
    protected function getValidator($coin)
    {
        $validators = $this->supportedCoins();

        if (array_key_exists(strtolower($coin), $validators)) {
            return $validators[strtolower($coin)];
        }

        return null;
    }

    /**
     * Determine if address is valid
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return bool
     */
    public function validAddress($attribute, $value, $parameters, $validator)
    {
        $data = $validator->getData();

        $addressValidator = $this->getValidator(trim($data['coin']));

        if(is_null($addressValidator)){
            $item = ExchangeItem::where("symbol","=",strtoupper(trim($data['coin'])))->first();
            if($item){
                return $item->getAltWithdrawalStatus();
            }
            return false;
        }

        return (bool)(new $addressValidator())->isValid($value);
    }
}
