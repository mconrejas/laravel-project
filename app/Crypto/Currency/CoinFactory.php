<?php

namespace Buzzex\Crypto\Currency;

use Buzzex\Crypto\Currency\Coins\AdzCoin;
use Buzzex\Crypto\Currency\Coins\AdzbuzzCommunityToken;
use Buzzex\Crypto\Currency\Coins\BitCoin;
use Buzzex\Crypto\Currency\Coins\BitCoinCash;
use Buzzex\Crypto\Currency\Coins\BitCoinGold;
use Buzzex\Crypto\Currency\Coins\BuzzexCoin;
use Buzzex\Crypto\Currency\Coins\Dash;
use Buzzex\Crypto\Currency\Coins\DogeCoin;
use Buzzex\Crypto\Currency\Coins\Erc20;
use Buzzex\Crypto\Currency\Coins\Ethereum;
use Buzzex\Crypto\Currency\Coins\EthereumClassic;
use Buzzex\Crypto\Currency\Coins\LiteCoin;
use Buzzex\Crypto\Currency\Coins\TronixToken;
use Buzzex\Crypto\Currency\Coins\TwinkleToken;
use Buzzex\Crypto\Currency\Coins\Usdex;
use Buzzex\Models\ExchangeItem;

class CoinFactory
{
    /**
     * @param $shortName
     *
     * @return bool
     * @throws Coins\Exceptions\CoinUnsetPropertyException
     */
    public static function create($shortName)
    {
        $exchangeItem = (new ExchangeItem())->newQuery()
            ->where('symbol', $shortName)
            ->first();

        if ($exchangeItem) {
            if ($exchangeItem->type === 5 && !empty($exchangeItem->token_address)) {
                $real_short_name = $shortName;
                $act_token_address = $exchangeItem->token_address;
                $shortName = "ERC20";
            }
        }

        if (array_key_exists($shortName, static::getSupportedCoins())) {
            $coin = static::getSupportedCoins()[$shortName];

            $coinObj = new $coin;

            if (!empty($act_token_address)) {
                $coinObj->setTokenAddress($act_token_address);
                $coinObj->setShortName($real_short_name);

                if ($shortName == "ERC20") {
                    $coinObj->setIsErc20();
                }
            }

            return $coinObj;
        }

        return false;
    }

    /**
     * @return array
     * @throws Coins\Exceptions\CoinUnsetPropertyException
     */
    public static function getSupportedCoins()
    {
        return [
            (new AdzCoin)->getShortName()                 => get_class(new AdzCoin),
            (new BitCoin)->getShortName()                 => get_class(new BitCoin),
            (new Dash)->getShortName()                    => get_class(new Dash),
            (new LiteCoin)->getShortName()                => get_class(new LiteCoin),
            (new BitCoinGold)->getShortName()             => get_class(new BitCoinGold),
            (new DogeCoin)->getShortName()                => get_class(new DogeCoin),
            (new Ethereum)->getShortName()                => get_class(new Ethereum),
            (new EthereumClassic)->getShortName()         => get_class(new EthereumClassic),
            (new TronixToken)->getShortName()             => get_class(new TronixToken),
            (new TwinkleToken)->getShortName()            => get_class(new TwinkleToken),
            (new BitCoinCash)->getShortName()             => get_class(new BitCoinCash),
            (new AdzbuzzCommunityToken())->getShortName() => get_class(new AdzbuzzCommunityToken()),
            (new Erc20)->getShortName()                   => get_class(new Erc20),
            (new BuzzexCoin())->getShortName()            => get_class(new BuzzexCoin()),
            (new Usdex())->getShortName()                 => get_class(new Usdex()),
        ];
    }
}
