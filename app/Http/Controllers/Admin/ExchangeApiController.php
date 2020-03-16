<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\ExchangeApi;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ExchangeApiController extends Controller
{

    /**
     * Display list of API.
     *
     * @return void
     */
    public function index()
    {
        $data = [
            'apis' => ExchangeApi::all(),
        ];

        return view('admin.exchange-apis.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.exchange-apis.create');
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
        $this->validate($request, [
            'name' => 'required|string|unique:exchange_apis',
            'base_url' => 'required|string',
            'trade_url' => 'required|string',
            'orderbook_url' => 'required|string',
            'balance_filter' => 'required|string',
            'profit_margin' => 'required|numeric'
        ]);

        ExchangeApi::firstOrCreate([
            'name' => $request->name,
            'base_url' => $request->base_url,
            'trade_url' => $request->trade_url,
            'orderbook_url' => $request->orderbook_url,
            'balance_filter' => $request->balance_filter,
            'profit_margin' => $request->profit_margin
        ]);

        toast($request->name . ' API created!', 'success', 'top-right');

        return redirect(route('exchangeapi'));
    }

    /**
     * edit external api's settings
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $data = [
            'api' => ExchangeApi::findOrFail($request->id),
            'id' => $request->id,
        ];

        return view('admin.exchange-apis.edit', $data);
    }

    /**
     * update external api's settings
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'base_url' => 'required|string',
            'trade_url' => 'required|string',
            'orderbook_url' => 'required|string',
            'balance_filter' => 'required|string',
        ]);

        $requestData = $request->all();

        $exchangeApi = ExchangeApi::findOrFail($request->id);
        $exchangeApi->update($requestData);

        Cache::forget('api-profit-margin-'.$exchangeApi->name);

        toast($request->name . ' API updated!', 'success', 'top-right');

        return redirect()->route('exchangeapi');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Request $request)
    {
        // @todo
        ExchangeApi::destroy($request->id);

        toast($request->name . ' item deleted!', 'success', 'top-right');

        return redirect(route('exchangeapi'));
    }
}
