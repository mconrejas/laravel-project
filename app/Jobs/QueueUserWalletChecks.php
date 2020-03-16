<?php

namespace Buzzex\Jobs;

use Buzzex\Http\Controllers\Main\WalletController;
use Buzzex\Models\UserSignin;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class QueueUserWalletChecks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WalletController $walletController)
    {
        $query = DB::table("user_signin")
            ->select('user_id')
            ->where('created_at','>=', date("Y-m-d H:i:s",strtotime('-30 days')))
            ->groupBy('user_id')
            ->orderBy('id','desc')
            ->get();

        if(!$query){
            return;
        }
        $walletController->setSkipManagerCheck(true);
        foreach($query as $users){
            echo $users->user_id ."<br/>";
            $walletController->requestWalletsCheck($users->user_id);
        }
        $walletController->setSkipManagerCheck(false);
    }
}
