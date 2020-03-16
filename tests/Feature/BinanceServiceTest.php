<?php

namespace Tests\Feature;

use Buzzex\Models\ExchangePair;
use Buzzex\Services\BinanceService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BinanceServiceTest extends TestCase
{
    /**
     * @var BinanceService
     */
    protected $binanceService;

    /**
     * @throws \Exception
     */
    public function setUp()
    {
        parent::setUp();

        $pair = ExchangePair::query()
            ->active()
            ->first();

        $this->binanceService = BinanceService::create(['pair_stat' => $pair->exchangePairStat]);
    }

    /** @test */
    public function can_execute_trading()
    {
        $params = [
            'symbol' => 'BTCUSDT',
            'side' => 'BUY',
            'quantity' => 1,
            'price' => 0.001,
        ];

        $this->assertTrue($this->binanceService->trade($params));
    }
}
