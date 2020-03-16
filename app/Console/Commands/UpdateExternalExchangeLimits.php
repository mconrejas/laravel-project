<?php

namespace Buzzex\Console\Commands;

use Buzzex\Models\ExchangePair;
use Buzzex\Models\ExchangePairStat;
use Illuminate\Console\Command;

class UpdateExternalExchangeLimits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-external-limits:run {source?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A command to update external limits for each external pair';

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
        $source = $this->argument('source') ?? 'binance';

        $service_name = "\\Buzzex\\Services\\".ucfirst($source)."Service";
        $service = $service_name::create(['pair_stat' => new ExchangePairStat()]);
        $markets = $service->markets();

        if ($markets) {
            foreach ($markets as $key => $market) {
                $pair_text = strtoupper($market['quote'].'_'.$market['base']);
                $exchangePair = ExchangePair::join('exchange_pairs_stats', 'exchange_pairs_stats.pair_id', '=', 'exchange_pairs.pair_id')
                                 ->where('exchange_pairs_stats.pair_text', '=', $pair_text)
                                 ->first();
                if (!$exchangePair) {
                    continue;
                }

                $filters = $exchangePair->filters;
                $pair_filters = array();
                foreach ($market["info"]["filters"] as $index => $pair_filter) {
                    $pair_filters[$pair_filter["filterType"]] = $pair_filter;
                }
                $filters[strtolower($source)] = $pair_filters;
                $exchangePair->filters = $filters;
                $exchangePair->save();
            }
        }
    }
}
