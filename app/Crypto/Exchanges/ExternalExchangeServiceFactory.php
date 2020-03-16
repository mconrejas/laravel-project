<?php

namespace Buzzex\Crypto\Exchanges;


use Buzzex\Models\ExchangePair;
use Buzzex\Services\BinanceService;
use Buzzex\Services\CoinexService;
use Buzzex\Services\BittrexService;
use Buzzex\Services\KucoinService;

class ExternalExchangeServiceFactory
{
    /**
     * @param string $serviceName
     * @param \Buzzex\Models\ExchangePair $pair
     *
     * @return \Buzzex\Services\ExchangeService|null
     */
    public static function create($serviceName, ExchangePair $pair)
    {
        $services = static::getExternalExchangeServices();

        $service = $services[$serviceName] ?: null;

        if (!$service) {
            return null;
        }

        return $service::create(['pair_stat' => $pair->exchangePairStat]);
    }

    /**
     * @return array
     */
    public static function getExternalExchangeServices()
    {
        return [
            'binance'  => BinanceService::class,
            'coinex'   => CoinexService::class,
//            'bittrex' => BittrexService::class,
//            'kucoin'  => KucoinService::class
        ];
    }
}