<?php

namespace Buzzex\Console\Commands;

use Buzzex\Contracts\User\CanManageOwnFund;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeTransaction;
use Buzzex\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ValidateWithdrawals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'withdrawals:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate withdrawals. Withdrawals that passes the rules will be marked as approved.';

    protected $debug = true;
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
        if (Cache::has('withdrawal-validation-script-running')) {
            return;
        }

        Cache::forever('withdrawal-validation-script-running', true);

        $pendingWithdrawals = (new ExchangeTransaction())->newQuery()
            ->withdrawals()
            ->pending()
            ->oldest('transaction_id')
            ->get();

        foreach ($pendingWithdrawals as $withdrawal) {
            if ($this->debug) {
                echo $withdrawal->transaction_id ."...";
            }
            if (Cache::has('withdrawal-validation-script-running-'.$withdrawal->transaction_id)) {
                continue;
            }

            Cache::put('withdrawal-validation-script-running-'.$withdrawal->transaction_id, 'pending', now()->addMinutes(30));
            $this->validate($withdrawal);
        }

        Cache::forget('withdrawal-validation-script-running');
    }

    /**
     * @param ExchangeTransaction $withdrawal
     */
    protected function validate(ExchangeTransaction $withdrawal)
    {
        if (!$withdrawal->user) {
            if ($this->debug) {
                echo "\nuser does not exist...";
            }
            if($this->markAsCancelled($withdrawal, "user does not exist")){
                if($this->debug) echo "Withdrawal cancelled!";
            }else{
                if($this->debug) echo "Withdrawal attempt FAILED!";
            }
            return;
        }

        if ($this->debug) {
            echo "--withdrawal user okay.";
        }

        $withdrawalAmount = abs($withdrawal->amount);

        if (true !== $withinCoinRange=$this->withinRangeOfMaxWithdrawalOfCoin($withdrawal->exchangeItem, $withdrawalAmount)) {
            $data = array();
            $current_logs = $withdrawal->logs;
            $notes = "Not within allowable daily withdrawal per item. ".$withinCoinRange["error"];
            $current_logs[] = array(
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'notes' => $notes,
                'updated_by' => 0
            );
            $data['logs'] = $current_logs;
            $withdrawal->update($data);
            $withdrawal->fresh();

            if ($this->debug) {
                echo "<br/>not within range of max withdrawal per coin";
            }
            return;
        }

        if ($this->debug) {
            echo "--coin max withdrawal okay.";
        }

        if (true !== $userDailyLimits=$this->areWithdrawalsWithinDailyLimit($withdrawal->user, $withdrawal->exchangeItem, $withdrawalAmount)) {
            $data = array();
            $current_logs = $withdrawal->logs;
            $notes = "Not within allowable daily withdrawal per user. ".$userDailyLimits["error"];
            $current_logs[] = array(
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'notes' => $notes,
                'updated_by' => 0
            );
            $data['logs'] = $current_logs;
            $withdrawal->update($data);
            $withdrawal->fresh();
            if ($this->debug) {
                echo "\n--Not within user limit";
            }
        } else {
            if($this->markAsApproved($withdrawal)){
                if ($this->debug) echo "<br/>Approved!!";
            }else{
                if ($this->debug) echo "<br/>Approval attempt FAILED!!";
            }
        }
    }

    /**
     * @param ExchangeItem $exchangeItem
     * @param $withdrawalAmount
     *
     * @return bool
     */
    protected function withinRangeOfMaxWithdrawalOfCoin(ExchangeItem $exchangeItem, $withdrawalAmount)
    {
        $defaultPercentage = parameter('coin_max_withdrawal_percentage', 10); // default is 10%

        $coinPercentage = parameter(strtolower($exchangeItem->symbol) . '_max_withdrawal_percentage',
                $defaultPercentage) / 100;

        $fundManager = app()->make(CanManageOwnFund::class);

        $systemWideFunds = $fundManager->getAllFunds();

        $coinFunds = $systemWideFunds[$exchangeItem->symbol] ?: 0;

        if ($coinFunds <= 0) {
            return array("error"=>"Available funds is $coinFunds");
        }

        $coinFunds += $withdrawalAmount;

        $coinTodayWithdrawal = $this->getCurrentDayTotalWithdrawalsByExchanteItem($exchangeItem);

        if (false === $coinTodayWithdrawal) {
            return array("error"=>"Cannot fetch today's total withdrawals");
        }

        $max_withdrawable = $coinFunds * $coinPercentage;
        $result = (($coinTodayWithdrawal + $withdrawalAmount) <= $max_withdrawable);
        return $result?$result:array("error"=>"Available funds: $coinFunds; Max Withdrawable: $max_withdrawable; Withdrawn Today: $coinTodayWithdrawal");
    }

    /**
     * @param User $user
     * @param $withdrawalAmount
     *
     * @return bool
     */
    protected function areWithdrawalsWithinDailyLimit(User $user, ExchangeItem $exchangeItem, $withdrawalAmount)
    {
        #print_r($exchangeItem);
        $amountLimit = $user->dailyWithdrawLimit();

        $currentDayTotalAmount = $this->getCurrentDayTotalWithdrawalsBy($user);

        if (false === $currentDayTotalAmount) {
            return array("error"=>"Cannot fetch today's user withdrawals");
        }

        if ($this->debug) {
            echo "\n--Withdrawn Amount Naive: $withdrawalAmount; USD Price: ".$exchangeItem->index_price_usd;
        }

        $withdrawalAmount = $withdrawalAmount * $exchangeItem->index_price_usd;

        if ($this->debug) {
            echo "\n--Daily Limit: $amountLimit; Current Withdrawal: $currentDayTotalAmount; New Amount: $withdrawalAmount";
        }

        $return = $withdrawalAmount > 0
            && ($currentDayTotalAmount + $withdrawalAmount)  <= $amountLimit;
        return $return?$return:array("error"=>"Daily Limit: $amountLimit; Current Withdrawal: $currentDayTotalAmount; New Amount: $withdrawalAmount");
    }

    /**
     * @param ExchangeTransaction $withdrawal
     */
    protected function markAsCancelled(ExchangeTransaction $withdrawal, $reason=null)
    {
        $data = array();
        $current_logs = $withdrawal->logs;
        if(!empty(trim($reason))) $notes = "Marked as Cancelled with reason: ".trim($reason);
        else $notes = "Marked as cancelled.";
        $current_logs[] = array(
            'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
            'notes' => $notes,
            'updated_by' => 0
        );
        $data['logs'] = $current_logs;
        $data["cancelled"] = Carbon::now()->timestamp;
        $withdrawal->update($data);
        $withdrawal->fresh();
        return ($withdrawal->cancelled > 0);
    }

    /**
     * @param ExchangeTransaction $withdrawal
     */
    protected function markAsApproved(ExchangeTransaction $withdrawal, $reason = null)
    {
        $data = array();
        $current_logs = $withdrawal->logs;
        if(!empty(trim($reason))) $notes = "Marked as Approved with reason: ".trim($reason);
        else $notes = "Marked as approved.";
        $current_logs[] = array(
            'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
            'notes' => $notes,
            'updated_by' => 0
        );
        $data['logs'] = $current_logs;
        $data["approved"] = Carbon::now()->timestamp;
        $withdrawal->update($data);
        $withdrawal->fresh();
        return ($withdrawal->approved > 0);
    }

    /**
     * Get the total USD worth of User's active and not yet approved withdrawals today
     * @param User $user
     * @return int
     */
    public function getCurrentDayTotalWithdrawalsBy(User $user)
    {
        $currentDate = now()->format('Y-m-d');

        $withdrawals = (new ExchangeTransaction())->newQuery()
            ->selectRaw(DB::raw('sum(amount*item_usd_price) as total'))
            ->withdrawals()
            ->where('cancelled', 0)
            ->where('approved', '!=', 0)
            ->whereRaw("DATE_FORMAT(FROM_UNIXTIME(created), '%Y-%m-%d') = '{$currentDate}'")
            ->where('user_id', $user->id)
            ->get();

        if (!$withdrawals) {
            return false;
        }
        $total = 0;
        foreach ($withdrawals as $withdrawal) {
            $total += $withdrawal->total;
        }
        return abs($total);
    }

    /**
     * Gets the total USD worth of User's active withdrawals today
     * @param User $user
     * @return bool|number
     */
    public function getCurrentDayTotalActiveWithdrawalsBy(User $user)
    {
        $currentDate = now()->format('Y-m-d');

        $withdrawals = (new ExchangeTransaction())->newQuery()
            ->selectRaw(DB::raw('sum(amount*item_usd_price) as total'))
            ->withdrawals()
            ->where('cancelled', 0)
            ->whereRaw("DATE_FORMAT(FROM_UNIXTIME(created), '%Y-%m-%d') = '{$currentDate}'")
            ->where('user_id', $user->id)
            ->get();

        if (!$withdrawals) {
            return false;
        }
        $total = 0;
        foreach ($withdrawals as $withdrawal) {
            $total += $withdrawal->total;
        }
        return abs($total);
    }

    /**
     * @param ExchangeItem $exchangeItem
     * @return bool|number
     */
    protected function getCurrentDayTotalWithdrawalsByExchanteItem(ExchangeItem $exchangeItem)
    {
        $currentDate = now()->format('Y-m-d');

        $withdrawals = (new ExchangeTransaction())->newQuery()
            ->selectRaw(DB::raw('sum(amount) as total'))
            ->withdrawals()
            ->where('item_id', $exchangeItem->item_id)
            ->where('cancelled', 0)
            ->where('approved', '!=', 0)
            ->whereRaw("DATE_FORMAT(FROM_UNIXTIME(created), '%Y-%m-%d') = '{$currentDate}'")
            ->get();

        if (!$withdrawals) {
            return false;
        }
        $total = 0;
        foreach ($withdrawals as $withdrawal) {
            $total += $withdrawal->total;
        }
        return abs($total);
    }
}
