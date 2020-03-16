<?php

namespace Buzzex\Console\Commands;

use Buzzex\Jobs\UpdateExchangePairStats as PairStatsUpdater;
use Buzzex\Models\ExchangePair;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateExchangePairStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange-pair-status:update {pair_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update exchange pairs status.';
    
    /**
     * Cache key for this jobs.
     *
     * @var string
     */
    protected $cacheKey = 'update_pairs_stats_';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pair_id = (int) ($this->argument('pair_id') ?? 0);
        if ($pair_id > 0) {
            $key = $this->cacheKey.$pair_id;

            if (!is_null(Cache::get($key))) {
                return;
            }

            Cache::forever($key, 1); //remove after executed on jobs

            $this->handleOnePair($pair_id);
            return;
        }
       
        $pairs = (new ExchangePair())->newQuery()
            ->activeBase()
            ->active()
            ->leftJoin('exchange_pairs_stats', 'exchange_pairs_stats.pair_id', '=', 'exchange_pairs.pair_id')
            ->where('exchange_pairs_stats.updated', '<=', Carbon::now()->subDays(1)->timestamp)
            ->get();
            
        if ($pairs) {
            foreach ($pairs as $pair) {
                if ($pair->hasActTokenItem() || $pair->hasInactiveItem()) {
                    continue;
                }
                PairStatsUpdater::dispatch($pair);
            }
        }
    }

    /**
     * Handle if pair_id is present on argument
     *
     * @return mixed
     */
    private function handleOnePair($pair_id)
    {
        $pair = (new ExchangePair())->newQuery()
            ->active()
            ->leftJoin('exchange_pairs_stats', 'exchange_pairs_stats.pair_id', '=', 'exchange_pairs.pair_id')
            ->where('exchange_pairs_stats.pair_id', '=', $pair_id)
            ->first();

        if ($pair) {
            dispatch((new PairStatsUpdater($pair))->onQueue('high'));
        }
    }
}
