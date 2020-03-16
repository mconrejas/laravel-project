<?php

namespace Buzzex\Console\Commands;

use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Jobs\ProcessStopLimit;
use Buzzex\Models\ExchangeOrder;
use Illuminate\Console\Command;

class ExecuteStopLimitOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stop-limit:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute stop limit orders';

    /**
     * @var Marketable
     */
    private $markets;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Marketable $markets)
    {
        parent::__construct();

        $this->markets = $markets;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stopLimitOrders = (new ExchangeOrder())->newQuery()
            ->where('form_type', 'stop-limit')
            ->whereNotNull('stop_limit_execution_time')
            ->get();

        foreach($stopLimitOrders as $order) {
            if ($this->isStopPriceMet($order)) {
                ProcessStopLimit::dispatch($order);
            }
        }
    }

    /**
     * @param ExchangeOrder $stopLimitOrder
     *
     * @return bool
     */
    protected function isStopPriceMet(ExchangeOrder $stopLimitOrder)
    {
        $pairInfo = $this->markets->getPairInfoByPairId($stopLimitOrder->pair_id);

        if ($stopLimitOrder->type === 'SELL') {
            return $stopLimitOrder->price >= $pairInfo['price'];
        }

        if ($stopLimitOrder->type === 'BUY') {
            return $stopLimitOrder->price <= $pairInfo['price'];
        }

        return false;
    }
}
