<?php

namespace Buzzex\Jobs;

use Buzzex\Models\ExchangePairStat;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tolawho\Loggy\Facades\Loggy;

class HandleDownloadExternalWithdrawalFees implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected $source;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($source = 'binance')
    {
        $this->source = $source;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service_name = "\\Buzzex\\Services\\".ucfirst($this->source)."Service";

        if (class_exists($service_name)) {
            $service = $service_name::create(['pair_stat' => new ExchangePairStat()]);
            
            if (method_exists($service, 'downloadExternalWithdrawalFees')) {
                $service->downloadExternalWithdrawalFees();
            }

            Loggy::info('exchange', json_encode(['message' => 'External download withdrawal fee run on '.Carbon::now()->format('Y-m-d H:i:s')]));
        }
    }
}
