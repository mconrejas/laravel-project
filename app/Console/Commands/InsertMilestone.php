<?php

namespace Buzzex\Console\Commands;

use Illuminate\Console\Command;
use Buzzex\Models\CoinCompetition;
use Buzzex\Models\CoinCompetitionRecord;
use Buzzex\Models\ExchangeTransaction;
use Buzzex\Models\User;
use Carbon\Carbon;
use DB;

class InsertMilestone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-milestone:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var Marketable
     */
    private $order_ids = null;

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
     * get completed competitions
     *
     * @return array
     */
    public function getCompetitionRecordLastDate($item_id)
    {
        return ExchangeTransaction::getMilestoneLastDate($item_id);
    }

    /**
     * get completed competitions
     *
     * @return array
     */
    public function getPriorDate($item_id, $current_date)
    {
        $records = [];
        $previous_date = 0;
        // list all completed via id
        if ($completions = CoinCompetitionRecord::orderBy('completed_at')->get()) {
            foreach ($completions as $completion) {
                $records[$completion->item_id][] = $completion->completed_at;
            }
            $key = array_search($current_date, $records[$item_id]);
            if (@$records[$item_id][($key-1)] !== null) {
                $previous_date = $records[$item_id][$key-1];
            }
        }

        return $previous_date;
    }

    /**
     * get completed competitions
     *
     * @return array
     */
    public function getAllTransactions($item_id, $partner_only=false)
    {
        $transactions = ExchangeTransaction::selectRaw('exchange_transactions.created, exchange_transactions.amount, exchange_transactions.item_btc_price, users.first_name')
            ->join('exchange_items', 'exchange_transactions.item_id', '=', 'exchange_items.item_id')
            ->join('users', 'users.id', '=', 'exchange_transactions.user_id')
            ->whereNotIn('users.email', config('account.official_emails'))
            ->where('exchange_items.item_id', $item_id)
            ->where('exchange_transactions.module', 'exchange_fulfillments')
            ->where('exchange_transactions.amount', '>', 0)
            ->where('exchange_transactions.cancelled', '=', 0)
            ->orderBy('exchange_transactions.created', 'ASC');

        // if partner only
        if ($partner_only) {
            $transactions = $transactions->where('users.settings->is_coin_partner', true);
        }

        return $transactions->get();
    }

    /**
     *
     * @return array
     */
    public function getCoinPartnerWinners($item_id, $prize)
    {
        $winners = [];

        if ($user = DB::table('users')->where('settings->coin_partner', "$item_id")->first()) {
            $winners[] = [
                'id' => $user->id,
                'email' => $user->email,
                'reward' => $prize,
                'claimed_at' => null
            ];
        }

        return $winners;
    }

    /**
     *
     * @return array
     */
    public function getGeneralWinners($item_id, $longJump=false)
    {
        $winners = [];
        $current_date = $this->getCompetitionRecordLastDate($item_id);
        $previous_date = $this->getPriorDate($item_id, $current_date);
        $general_prices = getGeneralPrizes();

                    
        $users = ExchangeTransaction::selectRaw("
                        users.*, 
                        FORMAT(sum(ABS(exchange_transactions.amount) * exchange_transactions.item_btc_price), 8) as volume
                    ")
                    ->join('exchange_items', 'exchange_items.item_id', '=', 'exchange_transactions.item_id')
                    ->join('users', 'users.id', '=', 'exchange_transactions.user_id');

        if($longJump){ 
            $users = $users->where('exchange_transactions.created', $current_date);
            echo "long jump \n";
        }else{
            $users = $users->whereBetween('exchange_transactions.created', [$previous_date, $current_date]);
        }
     
        $users = $users->where('exchange_items.item_id', '=', $item_id)
                    ->where('exchange_transactions.module', '=', 'exchange_fulfillments')
                    ->where('exchange_transactions.cancelled', '=', 0)
                    ->whereNotIn('users.email', config('account.official_emails'))
                    ->groupBy('exchange_transactions.user_id')
                    ->orderByRaw("sum(ABS(exchange_transactions.amount) * exchange_transactions.item_btc_price) DESC")
                    ->take(10)
                    ->get();
  
        if (count($users) > 0) {
            foreach ($users as $key => $user) {
                $rank = $key + 1;
                $user->volume = currency_format($user->volume);
                $volume = currency_format($user->volume, 4);
                $reward = $general_prices[$key];

                $winners[] = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'rank' => $rank,
                    'total_volume' => $volume,
                    'reward' => $reward,
                    'claimed_at' => null
                ];
            }
        }



        return $winners;
    }

 
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $CoinCompetition =  CoinCompetition::class;
        $competitions =  $CoinCompetition::all();
        $lastInsertedDate = 0;



        if ($coins = getCoinItems()) {
             foreach ($coins as $key => $coin) {
                //echo $coin."===>> \n";
                // $key = 6;
                // $coin = "LTC";
                $last_date = ExchangeTransaction::getMilestoneLastDate($key);
                $current_milestone = $competitions[0];
                $prev_completed_at = 0;

                $currentCompetition = $CoinCompetition::getCurrentCompetition($key);
                $currentCompetitionVolume =  $currentCompetition->volume;
                $currentCompetitionPrize =  $currentCompetition->prize;
                $winners = [];

 

                if ($competitions) {
                    foreach ($competitions as $k => $competition) {
                        $amount_btc = 0;
                        $longJump = false;

                        if ($currentCompetitionVolume <= $competition->volume && $currentCompetitionVolume >= $competition->volume) {
                            if ($exchangeTransactions = $this->getAllTransactions($key)) {
                                foreach ($exchangeTransactions as $exchangeTransaction) {
                                    $amount_btc += ($exchangeTransaction->amount * $exchangeTransaction->item_btc_price);

                                    echo "COIN: ".$coin." | USER:".$exchangeTransaction->first_name.' | Amount:'.$exchangeTransaction->amount * $exchangeTransaction->item_btc_price." | Total:".$amount_btc."/".$currentCompetitionVolume." | Timestamp:".$exchangeTransaction->created." \n";

                                    if ($amount_btc > $currentCompetitionVolume) {
                                        $completed = CoinCompetitionRecord::firstOrCreate([
                                            'competition_id' => $competition->id,
                                            'item_id' => $key,
                                        ], [
                                            'competition_id' => $competition->id,
                                            'item_id' => $key,
                                            'completed_at' => $exchangeTransaction->created,
                                        ]);

                                        $new_record = CoinCompetitionRecord::findOrfail($completed->id);

                                        // this will catch the long jump of competition
                                        if($lastInsertedDate == $exchangeTransaction->created){
                                            $longJump = true;
                                            echo "long jump \n";
                                        }

                                        if ($new_record->winners===null) {
                                            if ($partner_winner = $this->getCoinPartnerWinners($key, $currentCompetitionPrize)) {
                                                $winners['partner_winner'] = $partner_winner[0];
                                            }
                                            if ($general_winners = $this->getGeneralWinners($key, $longJump)) {
                                                $winners['general_winners'] = $general_winners;
                                            }
                                            $new_record->winners = $winners;
                                            $new_record->save();

                                            $lastInsertedDate = $exchangeTransaction->created;
                                        }

                                        $currentCompetition =  $CoinCompetition::getCurrentCompetition($key);
                                        $currentCompetitionVolume = $currentCompetition->volume;
                                        $currentCompetitionPrize =  $currentCompetition->prize;
                                    }
                                }
                            }
                        }
                    }
                }
             }
         }
    }
}
