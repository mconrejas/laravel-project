<?php

namespace Buzzex\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-old-logs:run {days?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old logs on storage on a given days (default 2 days), default can be override on parameter ';

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
        $days = $this->argument('days') ?? parameter('delete_old_logs_date_range', 2);
        $path = storage_path().'/logs/';
        $files = scandir($path);

        foreach ($files as $key => $file) {
            if (!(strpos($file, 'exchange-') === 0)) {
                continue;
            }
            $file_date = str_ireplace(['exchange-','.log'], '', $file);
            $file_date = Carbon::createFromFormat('Y-m-d', $file_date)->format('Y-m-d');
            $ceil_date = Carbon::now()->subDays($days)->format('Y-m-d');

            if ($file_date < $ceil_date) {
                unlink($path.$file);
            }
        }
    }
}
