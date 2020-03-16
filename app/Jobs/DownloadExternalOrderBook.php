<?php

namespace Buzzex\Jobs;

use Buzzex\Contracts\User\Tradable;
use Buzzex\Services\ExchangeService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;

class DownloadExternalOrderBook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var ExchangeService
     */
    private $exchangeService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ExchangeService $exchangeService)
    {
        $this->exchangeService = $exchangeService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Tradable $trading)
    {
        if (Cache::has('external-orderbook-fulfillment-' . $this->exchangeService->getExchangePairStat()->pair_id)) {
            return;
        }

        Cache::put('downloading-external-order-book-' . $this->exchangeService->getExchangePairStat()->pair_id, true, 10);

        $limit = config('external_exchanges.data_limit');

        $orderBook = $trading->getOrderbookFromExternalExchange($this->exchangeService, $limit);

        $trading->insertExternalOrderBook($orderBook, $this->exchangeService);


        Cache::forget('downloading-external-order-book-' . $this->exchangeService->getExchangePairStat()->pair_id);
    }
}
