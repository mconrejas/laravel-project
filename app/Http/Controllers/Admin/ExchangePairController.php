<?php

namespace Buzzex\Http\Controllers\Admin;

use Carbon\Carbon;
use Buzzex\Http\Requests;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\ExchangePair;
use Buzzex\Models\ExchangeItem;
use Illuminate\Http\Request;

class ExchangePairController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $inactive_filter = '';
        $keyword = $request->search;
        $perExchangePairs = 25;

        // initialize
        $exchangePairs = ExchangePair::select('*');

        // filter: show inactive items
        if (!strtolower($request->inactive) == "on") {
            $inactive_filter = "AND exchange_pairs.deleted = 0";
            $exchangePairs = $exchangePairs->where('deleted', '=', 0);
        }

        // filter: keyword
        if (!empty($keyword)) {
            $exchangePairs = $exchangePairs->whereHas('exchangeItemOne', function ($query) use ($keyword, $inactive_filter) {
                $query->whereRaw('(exchange_items.name LIKE "%'.$keyword.'%" OR exchange_items.symbol LIKE "%'.$keyword.'%") '.$inactive_filter);
            })->orWhereHas('exchangeItemTwo', function ($query) use ($keyword, $inactive_filter) {
                $query->whereRaw('(exchange_items.name LIKE "%'.$keyword.'%" OR exchange_items.symbol LIKE "%'.$keyword.'%") '.$inactive_filter);
            });
        }

        // pagination
        $exchangePairs = $exchangePairs->paginate($perExchangePairs);


        return view('admin.exchange-pairs.index', compact('exchangePairs'));
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
        $exchangePair = ExchangePair::findOrFail($id);
        $filters = $exchangePair->getFilters('local');
        $items = ExchangeItem::active()->get()->pluck('name', 'item_id');

        return view('admin.exchange-pairs.edit', compact('exchangePair', 'items', 'filters'));
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
        $exchangePair = ExchangePair::findOrFail($id);

        return view('admin.exchange-pairs.show', compact('exchangePair'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $items = ExchangeItem::all()->pluck('name', 'item_id');
        $exchangePair = ExchangePair::first();
        $filters = $exchangePair->getFilters('local');
        return view('admin.exchange-pairs.create', compact('items','filters'));
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
        // @todo test validation
        $this->validate($request, [
            'item1' => 'required|different:item2|exists:exchange_items,item_id|unique_with:exchange_pairs,item2',
            'item2' => 'required|exists:exchange_items,item_id',
            'fee_percentage' => 'required|numeric',
            'minimum_trade_total' => 'required|numeric'
        ]);

        $extra_fields = array(
            'created' => time(),
            'deleted' => 0,
            'dynamic_pricing' => 0
        );

        ExchangePair::create(array_merge($request->all(), $extra_fields));

        toast('Exchange Pair created!', 'success', 'top-right');

        return redirect('admin/exchange-pairs');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'fee_percentage' => 'required|numeric',
            'minimum_trade_total' => 'required|numeric'
        ]);

        $requestData = $request->except(['item1','item2']);

        $exchangePair = ExchangePair::findOrFail($id);
        $filters = $exchangePair->filters;

        foreach ($filters as $index => $filter) {
            foreach ($filter as $key => $field) {
                foreach ($field as $k => $val) {
                    if (array_key_exists($k, $requestData)) {
                        $filters[$index][$key][$k] = $requestData[$k];
                    }
                }
            }
        }

        $requestData = array_merge($requestData, ['filters' => $filters]);

        $exchangePair->update($requestData);

        toast('Exchange Pair updated!', 'success', 'top-right');

        return redirect('admin/exchange-pairs');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        // @todo
        ExchangePair::find($id)
                ->fill(['deleted' => Carbon::now()->timestamp])
                ->save();

        toast('Exchange pair deactivated!', 'success', 'top-right');

        return redirect('admin/exchange-pairs');
    }

    public function activate($id)
    {
        ExchangePair::find($id)
                ->fill(['deleted' => 0])
                ->save();

        toast('Exchange pair activated!', 'success', 'top-right');

        return redirect('admin/exchange-pairs');
    }
}
