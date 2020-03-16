<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Http\Requests;
use Illuminate\Http\Request;
use Buzzex\Models\CoinProject;
use Buzzex\Models\CoinCompetition;
use Buzzex\Models\CoinCompetitionRecord;
use Buzzex\Models\ExchangeMarket;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeTransaction;

class CoinProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function pending(Request $request)
    {
        $pendings = CoinProject::where('status', 0)->paginate(50);

        return view('admin.coin-projects.pending', compact('pendings'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function toBeListed(Request $request)
    {
        $to_be_listed = CoinProject::where('status', 2)->paginate(50);

        return view('admin.coin-projects.to-be-listed', compact('to_be_listed'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function votes(Request $request)
    {
        $for_voting = CoinProject::where('status', 1)->paginate(50);

        return view('admin.coin-projects.votes', compact('for_voting'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $project = CoinProject::findOrFail($id);

        return view('admin.coin-projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $project = CoinProject::findOrFail($id);

        return view('admin.coin-projects.edit', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|numeric|in:'.implode(",", range(-1, 1))
        ]);

        $project = CoinProject::findOrFail($id);
        $project->update([
            'status' => $request->status,
            'updated_by' => auth()->user()->id
        ]);

        toast('Project updated!', 'success', 'top-right');

        return redirect()->route('project.pending');
    }

    /**
     * Update resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $request->validate($this->rules($id));

        $project = CoinProject::findOrFail($id);

        $info = json_encode($request->only([
                'date_of_issue','total_supply','official_website','project_description', 'whitepaper','blockchain_explorer', 'source_code', 'coin_type'
            ]));

        $filename = time().'-'.$request->symbol.'.png';

        $request->logo->storeAs('public/icons', $filename);

        $data = array_merge(
                $request->only(['logo','symbol', 'name']),
                [ 'logo' => $filename, 'info' => $info, 'updated_by' => auth()->user()->id ]
            );

        $project->update($data);

        return response()->json([
            'id' => $project->id,
            'flash_message' => 'Project updated'
        ], 200);
    }

    /**
     *
     *
     */
    public function rules($except_id = 0)
    {
        return [
            'symbol' => 'required|string|min:2|unique:coin_projects,symbol,'.$except_id,
            'name'   => 'required|string|min:2|unique:coin_projects,name,'.$except_id,
            'coin_type' => 'required|string|in:'.implode(',', ['Public Chain', 'Non Public Chain']),
            'date_of_issue' => 'sometimes|nullable|date',
            'total_supply'  => 'required|numeric|min:1000',
            'official_website' => 'required|url',
            'project_description' => 'required|string|min:100',
            'whitepaper' => 'sometimes|nullable|url',
            'source_code' => 'required|url',
            'blockchain_explorer' => 'required',
            'logo'   =>  'required|image|mimes:png|max:'.maximumFileUploadSize()
        ];
    }

    public function coinCompetition(Request $request)
    {   
        $coins = [];
        $records = [];
        $markets = ExchangeMarket::select("item_id")->get()->toArray();

        $completions = CoinCompetitionRecord::orderBy('completed_at');
        if($getCoin = $request->coin ?? 'ADZ'){
            $selected_coin = ExchangeItem::where('symbol',$getCoin)->first();
            $completions = $completions->where('item_id',$selected_coin->item_id);
        }

        // list all completed via id
        if($completions = $completions->get()){
            foreach ($completions as $completion) {
                $records[$completion->item_id][] = $completion->completed_at;
            }
        }

        $coins = CoinCompetitionRecord::selectRaw('
            coin_competition_records.completed_at,
            coin_competition_records.winners, 
            exchange_items.symbol, 
            exchange_items.item_id, 
            coin_competitions.volume
            ')
            ->leftJoin('coin_competitions','coin_competitions.id','=','coin_competition_records.competition_id')
            ->leftJoin('exchange_items','exchange_items.item_id','=','coin_competition_records.item_id')
            ->where('exchange_items.deleted', '=', 0)
            ->where('exchange_items.type', '<>', 4)
            ->whereNotIn('exchange_items.item_id', array_pluck($markets, 'item_id'));

        if($getCoin){
            $coins = $coins->where('coin_competition_records.item_id',$selected_coin->item_id);
        }
 
        if($coins = $coins->get()){
            foreach ($coins as $coin) {
               $prev_completed_at = 0;
               $key = array_search ($coin->completed_at, $records[$coin->item_id]);

               if(@$records[$coin->item_id][($key-1)] !== NULL){
                    $prev_completed_at = $records[$coin->item_id][$key-1];
               }
            }

        }

        $data = [
            'coins' => $coins,
            'current_coin' => $getCoin
        ];

        return view('admin.coin-projects.coin-competition', $data);
    }

    /**
     *
     * @return array
     */
    public function getWinners($prev_completed_at, $completed_at, $item_id)
    {   
        return ( ExchangeTransaction::selectRaw("
            users.*, 
            COALESCE(sum(ABS(exchange_transactions.amount) * exchange_transactions.item_btc_price), 0) as volume
        ")
        ->join('exchange_items', 'exchange_items.item_id', '=', 'exchange_transactions.item_id')
        ->join('users', 'users.id', '=', 'exchange_transactions.user_id')
        ->where('users.settings->is_coin_partner', true) 
        ->whereBetween('exchange_transactions.created', [$prev_completed_at, $completed_at])
        ->where('exchange_items.item_id', $item_id)
        ->where('exchange_transactions.module', 'exchange_fulfillments')
        ->where('exchange_transactions.cancelled', '=', 0)
        ->whereNotIn('users.email', config('account.official_emails'))
        ->groupBy('exchange_transactions.user_id')
        ->orderBy('volume', 'desc')
        ->get());
    }
}

