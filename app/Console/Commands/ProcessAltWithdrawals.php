<?php

namespace Buzzex\Console\Commands;

use Buzzex\Models\ExchangeApi;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeItemWithdrawalsFee;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Models\ExchangeTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ProcessAltWithdrawals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alt_withdrawals:release {source?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release Alternative Withdrawals';

    /**
     * Determines whether or not debugging is on
     * @var bool
     */
    protected  $debug = true;

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
        $source = is_null($this->argument('source')) ? 'binance' : $this->argument('source');
        if (Cache::has('alt-withdrawal-validation-script-running-'.$source)) {
            return;
        }
        $exchangeAPI = ExchangeApi::where('name',$source)->first();
        if(!$exchangeAPI){
            if($this->debug) echo "Invalid API name - $source.";
            return;
        }

        Cache::put('withdrawal-validation-script-running-'.$source, true, now()->addMinutes(1));

        $releasingWithdrawal = (new ExchangeTransaction())->newQuery()
            ->withdrawals()
            ->releasing()
            ->where('exchange_api_id',$exchangeAPI->id)
            ->where('processed',0)
            ->oldest('approved')
            ->first();

        if(!$releasingWithdrawal){
            if($this->debug) echo "No more approved and unprocessed withdrawals to release!";
            return;
        }
        $releasingWithdrawal->processed = Carbon::now()->timestamp;
        $releasingWithdrawal->save();

        if($this->debug) print_r($releasingWithdrawal);

        $exchangeItem = ExchangeItem::where('item_id',$releasingWithdrawal->item_id)->first();

        if(!$exchangeItem){
            $message = "Invalid Exchange item, ".$releasingWithdrawal->item_id;
            if($this->debug) echo $message;
            $message = "Auto Releasing Failed: $message";
            $this->recordNotes($releasingWithdrawal,$message);
            return;
        }
        $net_amount = $releasingWithdrawal->amount + $releasingWithdrawal->fee;
        if(abs($net_amount) <= 0){
            $message = "Withdrawal net amount less than or equal to zero - ".$net_amount;
            if($this->debug) echo $message;
            $message = "Auto Releasing Failed: $message";
            $this->recordNotes($releasingWithdrawal,$message);
            return;
        }

        if(empty($releasingWithdrawal->remarks)){
            $message = "Withdrawal address is empty";
            if($this->debug) echo $message;
            $message = "Auto Releasing Failed: $message";
            $this->recordNotes($releasingWithdrawal,$message);
            return;
        }

        $service = false;
        $exchange_api_name_service = "Buzzex\\Services\\".ucfirst($source)."Service";
        if (class_exists($exchange_api_name_service)) {
            $service = $exchange_api_name_service::create(['pair_stat' => new ExchangePairStat()]);
        }
        if (!$service) {
            $message = "$source service not found...";
            if($this->debug) echo $message;
            $message = "Auto Releasing Failed: $message";
            $this->recordNotes($releasingWithdrawal,$message);
            return;
        }

        $apiWithdrawalFee = ExchangeItemWithdrawalsFee::where('item_id',$releasingWithdrawal->item_id)->first();
        if(!$apiWithdrawalFee){
            $message = "API withdrawal fee not found for this exchange item #".$releasingWithdrawal->item_id;
            if($this->debug) echo $message;
            $message = "Auto Releasing Failed: $message";
            $this->recordNotes($releasingWithdrawal,$message);
            return;
        }

        $code = strtoupper($exchangeItem->symbol);
        $amount = abs($net_amount);
        $address = $releasingWithdrawal->remarks;
        $tag = $releasingWithdrawal->tag;

        $final_amount = $amount + $apiWithdrawalFee->fee;
        if($final_amount < $apiWithdrawalFee->minimum_amount){
            $message = "Final withdrawal amount of $final_amount is less than the minimum withdrawal amount of ".$apiWithdrawalFee->minimum_amount." for this item in ".ucfirst($source);
            if($this->debug) echo $message;
            $message = "Auto Releasing Failed: $message";
            $this->recordNotes($releasingWithdrawal,$message);
            return;
        }

        $result = $service->withdraw($code,$amount,$address,$tag);
        if(is_array($result)){
            print_r($result);
            if(isset($result["info"]["success"]) && $result["info"]["success"] == 1){
                $releasingWithdrawal->remarks2 = isset($result["info"]["id"])?$result["info"]["id"]:""; // marking as released will be done by external withdrawals download script
                $releasingWithdrawal->save();
                $message = "Auto Releasing Successful! This withdrawal will be marked as released when we get the blockchain txid from $source. Actual Amount Requested: $amount; Response: ".print_r($result,true);
                if($this->debug) echo $message;
                $this->recordNotes($releasingWithdrawal,$message);
                return;
            }
        }

        $message = $result;
        if($this->debug) echo "Result: ".$message;
        $message = "Auto Releasing Failed. Response: ".$message;
        $this->recordNotes($releasingWithdrawal,$message);

    }

    /**
     * @param ExchangeTransaction $exchangeTransaction
     */
    protected function recordNotes(ExchangeTransaction $exchangeTransaction,$notes=null){
        if(!$notes) return;
        $data = array();
        $current_logs = $exchangeTransaction->logs;
        $current_logs[] = array(
            'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
            'notes' => $notes,
            'updated_by' => 0
        );
        $data['logs'] = $current_logs;
        $exchangeTransaction->update($data);
    }
}
