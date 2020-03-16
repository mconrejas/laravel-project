<?php

namespace Tests\Feature;

use Buzzex\Crypto\Exchanges\Arbitrage;
use Buzzex\Models\ExchangePair;
use Buzzex\Services\BinanceService;
use Buzzex\Services\CoinexService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArbitrageTest extends TestCase
{
    /**
     * @var BinanceService
     */
    protected $binanceService;

    /**
     * @var CoinexService
     */
    protected $coinexService;

    /**
     * @var Arbitrage
     */
    protected $arbitrage;

    /**
     * @throws \Exception
     */
    public function setUp()
    {
        parent::setUp();

        $pair = ExchangePair::query()
            ->active()
            ->where('pair_id', 5)
            ->first();

        $this->binanceService = BinanceService::create(['pair_stat' => $pair->exchangePairStat]);
        $this->coinexService = CoinexService::create(['pair_stat' => $pair->exchangePairStat]);

        $this->arbitrage = new Arbitrage(
            $this->binanceService->getOrderbook(5),
            $this->coinexService->getOrderbook(5)
        );
    }

    /** @test */
    public function can_get_order_from_orderbook_identified_by_price_and_type()
    {
        $entry = array_first($this->arbitrage->getOrderBook());

        $orders = $this->arbitrage->getOrder($entry['price'], $entry['action']);

        $this->assertTrue((bool)$orders);
    }
}
