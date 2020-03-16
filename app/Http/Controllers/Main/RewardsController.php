<?php

namespace Buzzex\Http\Controllers\Main;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\CoinCompetitionRecord;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeMarket;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\ExchangeReward;
use Buzzex\Models\ExchangeTransaction;
use Buzzex\Models\User;
use Buzzex\Services\RevenueService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Response;

class RewardsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }
    /**
     * Show diviends page
     * @return \Illuminate\Support\Facades\View
     */
    public function dividends()
    {
        $balance = auth()->user()->getBZXBalance();
        //$balance_all = auth()->user()->getAllBZXBalance();
        $user_id = auth()->user()->id;
        $exchangeItem = ExchangeItem::where('symbol', 'BZX')->first();

        abort_unless($exchangeItem, 404);

        $time = Carbon::now()->timestamp;
        $share_cost = parameter('dividends.share_cost_bzx', 20000);
        $balance_all_qualified = ExchangeTransaction::where('item_id', $exchangeItem->item_id)
            ->where('cancelled', 0)
            ->where('amount', '>=', $share_cost)
            ->where(function ($query) use ($time) {
                $query->where('released', '>', 0)
                ->where('released', '<=', $time)
                ->orWhere('type', 'withdrawal-request');
            })
            ->sum('amount');
        $total_active_shares = floor($balance_all_qualified / parameter('dividends.share_cost_bzx', 20000));
        $markets = ExchangeMarket::get();
        if (!$markets) {
            return false;
        }
        $data = array();
        $total_pool_amount = $total_fee_collected = $pool_amount_distributed = $dividends_count = $dividends_sum = 0;
        $base_markets = array();
        $minimum_share_amount = parameter('dividends.minimum_share_amount', 1);
        if ($minimum_share_amount < 0.0001) {
            $minimum_share_amount = 1;
        }
        if ($balance_all_qualified > $share_cost && $minimum_share_amount < 1) {
            $minimum_share_cost = $minimum_share_amount * $share_cost;
            $minimum_shares_all = floor($balance_all_qualified / $minimum_share_cost);
            $total_active_shares = $minimum_shares_all / (1 / $minimum_share_amount);
        }
        foreach ($markets as $market) {
            $exchangeItem = ExchangeItem::where('item_id', $market->item_id)->first();
            if (!$exchangeItem) {
                continue;
            }
            if ($exchangeItem->symbol != "BTC") {
                //continue; //update later when tabs are applied to show dividend info for all markets
            }
            $base_markets[] = $exchangeItem->symbol;
            $total_fee_collected = $exchangeItem->getTradeFeesCollected();
            $revenueService = new RevenueService();
            $revenueService->setCurrency($exchangeItem->symbol);
            $revenue_amount_recorded = $revenueService->getRevenueAmount("regular", -1);
            $revenue_amount_unrecorded = $exchangeItem->getTradeFeesCollected(0) * (parameter('dividends.percentage_of_fees_to_distribute', 40)/100);
            $total_pool_amount =  $revenue_amount_recorded + $revenue_amount_unrecorded;
            $pool_amount_distributed = $revenueService->getRevenueAmount("regular", 1);
            $dividends = ExchangeTransaction::where('type', 'dividend')
                ->where('item_id', $exchangeItem->item_id)
                ->where('user_id', $user_id)
                ->selectRaw('sum(amount) as total,count(transaction_id) as rows')
                ->first();
            if ($dividends) {
                $dividends_count = $dividends->rows;
                $dividends_sum = $dividends->total;
            }
            $data[$exchangeItem->symbol] = array(
                'total_fee_collected' => currency($total_fee_collected?$total_fee_collected:0, 8),
                'total_div_pool_amount' => currency($total_pool_amount?$total_pool_amount:0, 8),
                'div_pool_amount_distributed' => currency($pool_amount_distributed?$pool_amount_distributed:0, 8),
                'div_pool_amount_undistributed' => currency($total_pool_amount - $pool_amount_distributed, 8),
                'total_active_share' => currency($total_active_shares?$total_active_shares:0, 8),
                'value_per_share' => currency($total_active_shares?($total_pool_amount - $pool_amount_distributed) / $total_active_shares:0, 8),
                'bzx_balance' => currency($balance?$balance:0, 8),
                'no_of_shares' => floor($balance / $share_cost),
                'total_div_trans_received' => $dividends_count?$dividends_count:0,
                'total_div_amount_received' => currency($dividends_sum?$dividends_sum:0, 8),
                'min_pool_amount' => parameter('dividends.minimum_pool_amount.'.strtolower($exchangeItem->symbol), 0.01)
            );
            if ($balance > $share_cost && $minimum_share_amount < 1) {
                $minimum_share_cost = $minimum_share_amount * $share_cost;
                $minimum_shares = floor($balance / $minimum_share_cost);
                $data[$exchangeItem->symbol]['no_of_shares'] = $minimum_shares / (1 / $minimum_share_amount);
            }
            $data[$exchangeItem->symbol]['estimate_share_value'] = currency($data[$exchangeItem->symbol]['no_of_shares'] * $data[$exchangeItem->symbol]['value_per_share']);
        }

        return view('main.rewards.dividends', compact('data', 'base_markets'));
    }

    /**
     * get transactions for dividends by item
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response |array
     */
    public function getDividendsTransactions(Request $request)
    {
        abort_unless(auth()->check(), 404);

        $item = ExchangeItem::active()->where('symbol', $request->item)->firstOrFail();

        abort_unless($item, 404);

        $data = [];
        $dividends = ExchangeTransaction::where('type', 'dividend')
                ->where('item_id', '=', $item->item_id)
                ->where('user_id', '=', auth()->user()->id);

        $count = $dividends->count();

        $dividends = $dividends->skip($request->size * ($request->page - 1))
                ->take($request->size)
                ->orderBy('created', 'desc')
                ->get();

        if ($dividends) {
            foreach ($dividends as $key => $dividend) {
                $data[] = array(
                    'id' => $dividend->transaction_id,
                    'time' => Carbon::createFromTimestamp($dividend->created)->format('Y-m-d H:i:s'),
                    'amount' => abs($dividend->amount),
                    'usd_value' => currency($dividend->amount * $dividend->item_usd_price)
                );
            }
        }
        $response = [
            //the total number of available pages (this could be record_counts for particular query divided by size ), must greater than zero
            'last_page' => ceil($count / $request->size),
            'data'      => $data,
        ];

        return response()->json($response, 200);
    }

    /**
     * Show rewards page
     * @return \Illuminate\Support\Facades\View
     */
    public function rewards()
    {
        $data = array(
                'total_trades' => 0,
                'total_btc' => 0,
                'total_usd' => 0,
                'total_rewards' => 0,
                'total_claimed' => 0,
                'claimable' => 0,
                'rewards_in' => 'BZX',
                'claiming_rewards_available' => parameter('claiming_rewards_available', 0) == 1 ? true : false
            );
        $query =   ExchangeTransaction::from('exchange_transactions as et')
                    ->leftJoin('exchange_fulfillments as ef', DB::raw('et.module_id'), '=', DB::raw('ef.fulfillment_id'))
                    ->leftJoin('exchange_orders as eo', DB::raw('ef.buy_order_id'), '=', DB::raw('eo.order_id'))
                    ->leftJoin('exchange_pairs as ep', DB::raw('eo.pair_id'), '=', DB::raw('ep.pair_id'))
                    ->select(DB::raw('sum(ABS(et.amount)*item_btc_price) as total_btc,sum(ABS(et.amount)*item_usd_price) as total_usd,count(transaction_id) as total_trades,et.user_id'))
                    ->whereRaw('et.module ="exchange_fulfillments"')
                    ->whereRaw('item_id = ep.item1')
                    ->whereRaw('et.user_id = '. auth()->user()->id)
                    ->groupBy(DB::raw('et.user_id'))
                    ->first();
                    
        $reward = ExchangeReward::selectRaw('sum(amount) as total')->where('user_id', auth()->user()->id)->first();
        $claimed = (!$reward || is_null($reward->total)) ? 0 : $reward->total;
        $total_usd = 0;
        if (isset($query->total_usd) && !is_null($query->total_usd)) {
            $total_usd = $query->total_usd;
        }

        if ($query) {
            $data['total_trades'] = $query->total_trades;
            $data['total_btc'] = $query->total_btc ?? 0;
            $data['total_usd'] = $query->total_usd ?? 0;
            $data['total_rewards'] = floor($total_usd / parameter('rewards.divisibleby', 65));
            $data['total_claimed'] = $claimed;
            $data['claimable'] = $data['total_rewards'] - $claimed;
        }
        
        return view('main.rewards.trans-fee', $data);
    }

    /**
     * Claim reward
     * @param Illuminate\Http\Request $request
     * @return mixed
     */
    public function claimRewards(Request $request)
    {
        $query =   ExchangeTransaction::from('exchange_transactions as et')
                    ->leftJoin('exchange_fulfillments as ef', DB::raw('et.module_id'), '=', DB::raw('ef.fulfillment_id'))
                    ->leftJoin('exchange_orders as eo', DB::raw('ef.buy_order_id'), '=', DB::raw('eo.order_id'))
                    ->leftJoin('exchange_pairs as ep', DB::raw('eo.pair_id'), '=', DB::raw('ep.pair_id'))
                    ->select(DB::raw('sum(ABS(et.amount)*item_btc_price) as total_btc,sum(ABS(et.amount)*item_usd_price) as total_usd,count(transaction_id) as total_trades,et.user_id'))
                    ->whereRaw('et.module ="exchange_fulfillments"')
                    ->whereRaw('item_id = ep.item1')
                    ->whereRaw('et.user_id = '. auth()->user()->id)
                    ->groupBy(DB::raw('et.user_id'))
                    ->first();
        if (!$query) {
            throw new \Exception("Error Processing Request. You dont have rewards to claim", 433);
        }
    
        $total_usd = 0;
        if (isset($query->total_usd) && !is_null($query->total_usd)) {
            $total_usd = $query->total_usd;
        }

        $bzx = ExchangeItem::where('symbol', '=', 'BZX')->first();
        if (!$bzx) {
            throw new Exception("Error Processing Request. BZX is not yet integrated.", 404);
        }
        $reward = ExchangeReward::selectRaw('sum(amount) as total')->where('user_id', auth()->user()->id)->first();
        $claimed = (!$reward || is_null($reward->total)) ? 0 : $reward->total;
        $total_rewards = floor($total_usd / parameter('rewards.divisibleby', 65));
        $claimable = $total_rewards - $claimed;

        if ($claimable <= 0) {
            throw new Exception("Error Processing Request. Insufficient claims.", 402);
        }
        $data = array(
                'type' => 'exchange_rewards',
                'user_id' => auth()->user()->id,
                'amount' => $claimable,
                'item_id' => $bzx ? $bzx->item_id : 0,
            );
        $data['raw_data'] = $data;

        $newReward = ExchangeReward::create($data);
        if ($newReward) {
            ExchangeTransaction::create([
                    'module' => 'exchange_rewards',
                    'module_id' => $newReward->reward_id,
                    'user_id' => auth()->user()->id,
                    'item_id' => $bzx->item_id,
                    'amount' => $claimable,
                    'fee' => 0,
                    'type' => 'exchange-rewards',
                    'released' => Carbon::now()->timestamp,
                    'created' => Carbon::now()->timestamp
                ]);
        }
        return response()->json($data, 200);
    }

    /**
     * Claim trading rewards
     * @param Illuminate\Http\Request $request
     * @return mixed
     */
    public function getMilestoneRewards(User $user, $item_id = null)
    {
        $data = [];

        $competitions = CoinCompetitionRecord::where(function ($query) use ($user) {
            return $query->where('winners->general_winners', 'LIKE', '%"email": "'.$user->email.'"%')
                            ->orWhere('winners->partner_winner->id', '=', $user->id);
        });

        if (!is_null($item_id) && $item_id > 0) {
            $competitions = $competitions->where('item_id', '=', $item_id);
        }

        $competitions = $competitions->orderBy('item_id', 'asc')->get();
        $is_claiming_available = parameter('is_claiming_milestone_reward_available', 1) == 0 ? false : true;
 
        if ($competitions) {
            foreach ($competitions as $key => $reward) {
                $item = ExchangeItem::findOrFail($reward->item_id);
                $partner_winner = $reward->winners['partner_winner'] ?? [];
                $general_winners = $reward->winners['general_winners'] ?? [];
                $is_coin_partner = @$partner_winner['id'] == $user->id;
                $index = array_search($user->id, array_column($general_winners, 'id'));
                $coin_partner_reward_claimable = !is_null(@$partner_winner['claimed_at']) ? $reward->competition->prize : 0;
                $general_claimable = is_null($general_winners[$index]['claimed_at']) ? $general_winners[$index]['reward'] : 0;

                $data[$item->symbol][] = array(
                        'is_claiming_available' => $is_claiming_available,
                        'id' =>$reward->id,
                        'is_coin_partner' => $is_coin_partner,
                        'coin_partner_rewards' => $reward->competition->prize,
                        'coin_partner_reward_claimable' => $coin_partner_reward_claimable,
                        'coin_partner_reward_claimed' => !is_null(@$partner_winner['claimed_at']) ? $reward->competition->prize : 0,
                        'coin' => $item->symbol,
                        'rank' => $general_winners[$index]['rank'],
                        'claimable' => $general_claimable,
                        'claimed' => !is_null($general_winners[$index]['claimed_at']) ? $general_winners[$index]['reward'] : 0,
                        'milestone_number' => $reward->competition_id,
                        'total_volume' => $general_winners[$index]['total_volume'],
                        'total_claimable' => $is_coin_partner ? ($coin_partner_reward_claimable + $general_claimable) : $general_claimable
                    );
            }
        }
 
        return $data;
    }
    /**
     * Claim trading rewards
     * @param Illuminate\Http\Request $request
     * @return mixed
     */
    public function milestone(Request $request)
    {
        return view('main.rewards.trading');
    }

    /**
     * Claim reward
     * @param Illuminate\Http\Request $request
     * @return mixed
     */
    public function getMilestoneRewardsList(Request $request)
    {
        $data = [];
        $current_user = User::find(auth()->user()->id);
        $item_id = null;
        if ($request->has('coin') && !empty($request->coin) &&  $request->coin != 'all') {
            $item = ExchangeItem::where('symbol', '=', $request->coin)->first();
            if ($item) {
                $item_id = $item->item_id;
            }
        }

        $data = $this->getMilestoneRewards($current_user, $item_id);

        $html = view('main.rewards.milestone-each-coin', compact('data'))->render();

        return response()->json(['html' => $html], 200);
    }

    /**
     * Claim reward
     * @param Illuminate\Http\Request $request
     * @return mixed
     */
    public function claimMilestoneRewards(Request $request)
    {
        $is_claiming_available = parameter('is_claiming_milestone_reward_available', 1) == 0 ? false : true;
        
        if (!$is_claiming_available) {
            throw new \Exception("Error Processing Request. Claiming milestone rewards is temporary unavailable.", 433);
        }

        $competition = CoinCompetitionRecord::findOrFail($request->id);
       
        if (!$competition) {
            throw new \Exception("Error Processing Request. You dont have claimable rewards.", 433);
        }
        $user = auth()->user();
        $isGeneralWinner = $competition->isGeneralWinner($user->id);
        $isPartnerWinner = $competition->isPartnerWinner($user->id);
        if (!($isGeneralWinner || $isPartnerWinner)) {
            throw new Exception("Error Processing Request. You dont have rewards to claim", 433);
        }

        $bzx = ExchangeItem::where('symbol', '=', 'BZX')->first();
        if (!$bzx) {
            throw new Exception("Error Processing Request. BZX is not yet integrated.", 404);
        }
        //claim by general winner
        if ($isGeneralWinner) {
            $winner = $competition->getGeneralWinnerByUserId($user->id);
            if (!is_null($winner->claimed_at)) {
                throw new Exception("Error Processing Request. You already claimed this reward last ".Carbon::createFromTimestamp($winner->claimed_at)->toCookieString(), 433);
            }
            $data = array(
                    'type' => 'milestone_rewards',
                    'user_id' => $user->id,
                    'amount' => $winner->rewards,
                    'item_id' => $bzx->item_id,
                );
            $data['raw_data'] = $data;

            $newReward = ExchangeReward::create($data);
            if ($newReward) {
                ExchangeTransaction::create([
                        'module' => 'exchange_rewards',
                        'module_id' => $newReward->reward_id,
                        'user_id' => $user->id,
                        'item_id' => $bzx->item_id,
                        'amount' => $winner->rewards,
                        'fee' => 0,
                        'type' => 'milestone_rewards',
                        'released' => Carbon::now()->timestamp,
                        'created' => Carbon::now()->timestamp
                    ]);
                //update json claimed_at
                $competition->setClaimGeneralWinner($user->id);
            }
        }

        //claim by partner winner
        if ($isPartnerWinner) {
            $winner = $competition->getCoinPartnerWinner();
            if (!is_null($winner->claimed_at)) {
                throw new Exception("You already claimed this reward last ".Carbon::createFromTimestamp($winner->claimed_at)->toCookieString(), 433);
            }
            $data = array(
                    'type' => 'milestone_rewards',
                    'user_id' => $user->id,
                    'amount' => $winner->rewards,
                    'item_id' => $bzx->item_id,
                );
            $data['raw_data'] = $data;

            $newReward = ExchangeReward::create($data);
            if ($newReward) {
                ExchangeTransaction::create([
                        'module' => 'exchange_rewards',
                        'module_id' => $newReward->reward_id,
                        'user_id' => $user->id,
                        'item_id' => $bzx->item_id,
                        'amount' => $winner->rewards,
                        'fee' => 0,
                        'type' => 'milestone_rewards',
                        'released' => Carbon::now()->timestamp,
                        'created' => Carbon::now()->timestamp
                    ]);
                //update json claimed_at
                $competition->setClaimPartnerWinner();
            }
        }

        return response()->json(['message' => 'Successfully claimed'], 200);
    }
}
