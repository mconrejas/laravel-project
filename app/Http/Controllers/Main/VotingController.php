<?php

namespace Buzzex\Http\Controllers\Main;

use Buzzex\Events\CoinProjectIsVotedEvent;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\CoinProject;
use Buzzex\Models\ExchangeFulfillment;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\ExchangePair;
use Buzzex\Models\ExchangeMarket;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Models\ExchangeTransaction;
use Buzzex\Models\CoinCompetition;
use Buzzex\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VotingController extends Controller
{
    /**
     * Show the voting page
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        switch ($request->tab) {
            case 'listed':
                $markets = ExchangeMarket::select("item_id")->get()->toArray();

                $items = ExchangeItem::selectRaw("exchange_items.*, COALESCE(sum(exchange_orders.fulfilled_amount), 0) as volume")
                            ->leftJoin('exchange_pairs', 'exchange_pairs.item1', '=', 'exchange_items.item_id')
                            ->leftJoin('exchange_orders', 'exchange_pairs.pair_id', '=', 'exchange_orders.pair_id')
                            ->where(function ($query) use ($request) {
                                if (isset($request->coin)) {
                                    $query->where('exchange_items.symbol', 'LIKE', "%$request->coin%")
                                            ->orWhere('exchange_items.name', 'LIKE', "%$request->coin%");
                                }
                            })
                            ->where('exchange_items.type', '<>', 4)
                            ->where('exchange_items.deleted', 0)
                            ->where('exchange_items.symbol', '<>', 'BZX')
                            ->whereNotIn('exchange_items.item_id', array_pluck($markets, 'item_id'))
                            ->groupBy('exchange_items.item_id')
                            ->orderBy('exchange_items.symbol', 'asc')
                            ->paginate(51);

                $content = array(
                    'counts' => ExchangeItem::where('type', '<>', 4)->where('deleted', 0)->count(),
                    'data'   => $items
                );
                break;

            case 'to-be-listed':
                $content = array(
                    'counts' => CoinProject::where('status', 2)->count(),
                    'data' => CoinProject::where('status', 2)->orderBy('symbol', 'asc')->paginate(50)
                 );
                break;
                
            default:
                $content = array(
                    'counts' => CoinProject::where('status', 1)->count(),
                    'data' => CoinProject::where('status', 1)->orderBy('symbol', 'asc')->paginate(50) //should be status = 1
                );
                break;
        }
        
        $data = array_merge([ 'tab' => $request->tab ], $content);

        if ($request->ajax()) {
            return view('main.voting.tabs.listed', compact('data'))->render();
        }
   
        return view('main.voting.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $this->canVote($request);

        $request->validate([
            'coin' => 'required|string|exists:coin_projects,symbol',
            'action' => 'required|numeric|in:'.implode(',', range(-1, 1))
        ]);

        $project = CoinProject::where('symbol', $request->coin)->first();
        $project->votes()->create(['user_id' => auth()->user()->id, 'type' => 1 ]);

        broadcast(new CoinProjectIsVotedEvent($project));

        return response()->json(['flash_message' => __('Successfully voted!')], 200);
    }

    /**
     * Check if user can vote
     * @param Request
     * @return mixed
     */
    public function canVote($request)
    {
        abort_unless(auth()->check(), 403, __('Login Required'));

        $user = User::find(auth()->user()->id);
        
        if ($user->hasVoted()) {
            abort(403, __('You already casted your vote.'));
        }
    }

    /**
     * View coin competition status
     * @param Request $request
     * @param String $coin
     * @return mixed
     */
    public function view(Request $request)
    {
        $current_user = Auth()->user();
        $symbol = $request->symbol ?? 'BTC';
        $ExchangeTransaction = ExchangeTransaction::class;
        $CoinCompetition = CoinCompetition::class;
        $item = ExchangeItem::where('symbol', $symbol)->first();
 
        $transaction = $ExchangeTransaction::getTransactions($item->item_id);
 
        $users = $ExchangeTransaction::getUsersFromTransactions($item->item_id);
        $current_competition = $CoinCompetition::getCurrentCompetition($item->item_id);

        $rank = '?';
        $volume = '?';

        $competitions = $CoinCompetition::all();

        if ($competitions) {
            foreach ($competitions as $key => $competition) {
                if ($competition->volume == $current_competition->volume) {
                    $bar_width[$key] = ($transaction->amount_btc / $competition->volume) * 100;
                } elseif ($competition->volume < $current_competition->volume) {
                    $bar_width[$key] = $competition->volume * 100;
                } else {
                    $bar_width[$key] = 0;
                }
            }
        }

        // this will catch the empty current milestone
        if ($transaction->amount_btc < 1 && $current_competition->volume > 1) {
            $previous_volume = $CoinCompetition::getPreviousCompetition($item->item_id)->volume;
            $finished_competitions = $CoinCompetition::getFinishedCompetition($item->item_id);
            $transaction->amount_btc = $previous_volume;
            for ($i = 0; $i < count($finished_competitions); $i++) {
                $bar_width[$i] = 100;
            }
        }

        if (count($users) > 0) {
            foreach ($users as $key => $user) {
                $user->volume = currency_format($user->volume);

                if ($current_user !== null && $current_user->id == $user->id) {
                    $rank = $key + 1;
                    $volume = currency_format($user->volume, 4);
                }

                $user->email_blured = blurEmail($user->email);
            }
        }
 
        return view('main.voting.view', compact('item', 'transaction', 'users', 'rank', 'volume', 'bar_width', 'current_competition'));
    }
}
