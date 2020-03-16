<?php

namespace Buzzex\Jobs;

use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Models\ExchangePair;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateExchangePairStats implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShouldNotifyDevelopers;

    /**
     * @var ExchangePair
     */
    public $pair;

    /**
     * @var ExchangePair
     */
    public $cacheKey;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     * @param \Buzzex\Models\ExchangePair $pair
     * @return void
     */
    public function __construct(ExchangePair $pair)
    {
        $this->pair = $pair;
        $this->cacheKey = 'update_pairs_stats_'.$pair->pair_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Marketable $markets)
    {
        try {
            if (!$this->pair->isActive()) {
                throw new \Exception("pair is not active", 1);
            }

            $markets->updateExchangePairStats($this->pair);
            
            Cache::forget($this->cacheKey);
        } catch (\Exception $e) {
            Log::debug('UpdateExchangePairStats >>> '.$e->getMessage());
            Cache::forget($this->cacheKey);
        }
    }
}
