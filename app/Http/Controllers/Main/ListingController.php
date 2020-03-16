<?php

namespace Buzzex\Http\Controllers\Main;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\CoinProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Buzzex\Http\Requests\TokenListingRequest;

class ListingController extends Controller
{

    /**
     * Show the form for submitting coin project
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('main.listing.index');
    }

    /**
     * Show the page for a certain coin project
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $coin = CoinProject::findOrFail($request->id);
    
        return view('main.listing.show', compact('coin'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(TokenListingRequest $request)
    {
        $info = json_encode($request->only([
                'date_of_issue','total_supply','official_website','project_description', 'whitepaper','blockchain_explorer', 'source_code', 'coin_type'
            ]));

        $filename = time().'-'.$request->symbol.'.png';

        $request->logo->storeAs('public/icons', $filename);

        $data = array_merge(
                $request->only(['logo','symbol', 'name']),
                [ 'logo' => $filename, 'info' => $info, 'created_by' => auth()->user()->id ]
            );

        $project = CoinProject::create($data);

        return response()->json([
            'id' => $project->id,
            'flash_message' => __("Successfully submitted")
        ], 200);
    }

    /**
     * Search coin projects
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function search(Request $request)
    {
        $term = trim($request->q);

        if (empty($term)) {
            return response()->json([], 200);
        }

        $data = CoinProject::where('symbol', 'LIKE', '%'.$term.'%')
                ->orWhere('name', 'LIKE', '%'.$term.'%')
                ->limit(10)
                ->get()
                ->toArray();

        return response()->json($data, 200);
    }
}
