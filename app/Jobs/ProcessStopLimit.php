<?php

namespace Buzzex\Jobs;

use Buzzex\Contracts\User\Tradable;
use Buzzex\Models\ExchangeOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessStopLimit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShouldNotifyDevelopers;

    /**
     * @var ExchangeOrder
     */
    private $stopLimitOrder;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ExchangeOrder $stopLimitOrder)
    {
        $this->stopLimitOrder = $stopLimitOrder;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Tradable $trading)
    {
        if (empty($this->stopLimitOrder->stop_price) && empty($this->stopLimitOrder->limit_price)) {
            return;
        }

        $trading->processStopLimit($this->stopLimitOrder);
    }
}
