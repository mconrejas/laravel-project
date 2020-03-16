<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Http\Controllers\Controller;
use Carbon\Carbon;
use Buzzex\Http\Requests;
use Buzzex\Models\ExchangeApi;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeMarket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ExchangeItemController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->search;
        $type = (string) $request->type;
        $perExchangeItem = 25;

        $exchangeItems = ExchangeItem::select('*');

        // filter: keyword
        if (!empty($keyword)) {
            $exchangeItems = $exchangeItems->where(function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%$keyword%")
                ->orWhere('symbol', 'LIKE', "%$keyword%");
            });
        }

        // filter: item type
        if (($type && $type != 'all') || $type == "0") {
            $exchangeItems = $exchangeItems->where('type', '=', $type);
        }

        // filter: show deleted items
        if (!(isset($request->deleted) && strtolower($request->deleted) == "on")) {
            $exchangeItems = $exchangeItems->where('deleted', '=', 0);
        }

        // pagination
        $exchangeItems = $exchangeItems->paginate($perExchangeItem);

        return view('admin.exchange-items.index', compact('exchangeItems'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $types = exchangeTypeOptions();
        $apis = getExchangeApis();
        return view('admin.exchange-items.create', compact('types', 'apis'));
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
        $validations = [
            'exchange_api_id' => 'required|string',
            'name' => 'required|string|unique:exchange_items',
            'symbol' => 'required|string|unique:exchange_items',
            'type' => 'required|numeric',
            'alternative_deposit' => 'required|string',
            'icon' => 'sometimes'
        ];

        if ( $request->type == 5) {
            $validations['token_address'] = 'required|string';
        }

        // @todo
        $this->validate($request, $validations);

        ExchangeItem::create(array_merge($request->all(), ['updated_by' => auth()->user()->id]));

        toast('Exchange item created!', 'success', 'top-right');

        return redirect('admin/exchange-items');
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
        $exchangeItem = ExchangeItem::findOrFail($id);

        return view('admin.exchange-items.show', compact('exchangeItem'));
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
        // @todo
        $exchangeItem = ExchangeItem::findOrFail($id);
        $apis = getExchangeApis();
        $types = exchangeTypeOptions();
        return view('admin.exchange-items.edit', compact('exchangeItem', 'types', 'apis'));
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
        $validations = [
            'exchange_api_id' => 'required|string',
            'name' => 'required|string|unique:exchange_items,name,'.$id.',item_id',
            'symbol' => 'required|string|unique:exchange_items,symbol,'.$id.',item_id',
            'alternative_deposit' => 'required|string',
        ];
        if ( $request->type == 5) {
            $validations['token_address'] = 'required|string';
        }
        // @todo
        $this->validate($request, $validations);
        
        $requestData = $request->all();
        $requestData['deposits_off'] = $requestData['deposits_off'] > 0 ? time() : 0;
        $requestData['withdrawals_off'] = $requestData['withdrawals_off'] > 0 ? time() : 0;

        $exchangeItem = ExchangeItem::findOrFail($id);
        $exchangeItem->update($requestData);

        toast('Exchange item updated!', 'success', 'top-right');

        return redirect('admin/exchange-items');
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

        $item = ExchangeItem::find($id)
                ->fill(['deleted' => Carbon::now()->timestamp])
                ->save();

        toast('Exchange item deactivated!', 'success', 'top-right');

        return redirect('admin/exchange-items');
    }

    public function activate($id)
    {
        $item = ExchangeItem::find($id)
                ->fill(['deleted' => 0])
                ->save();

        toast('Exchange item activated!', 'success', 'top-right');

        return redirect('admin/exchange-items');
    }

    /**
     * Show the form for uploading resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function uploadForm($id)
    {
        $exchangeItem = ExchangeItem::findOrFail($id);

        return view('admin.exchange-items.upload', compact('exchangeItem'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function upload(Request $request, $id)
    {
        $fileuploadlimit = maximumFileUploadSize();

        $this->validate($request, [
            'icon' => 'required|image|mimes:png|max:'.$fileuploadlimit,
        ]);
        $exchangeItem = ExchangeItem::findOrFail($id);

        $filename = time().'-'.$exchangeItem->symbol.'.png';

        $request->icon->storeAs('public/icons', $filename);

        $exchangeItem->update(['icon' => $filename]);

        return response()->json(['flash_message' => 'Successfully set icon'], 200);
    }

    /**
     * get Market bases
     *
     * @return \Illuminate\Http\Response
     */
    public function getMarketBases(Request $request)
    {
        $data = [
            'markets' => ExchangeItem::where('type', '<>', 4)->get()
        ];
        return view('admin.exchange-markets.bases', $data);
    }

    /**
     * update Market bases
     *
     * @return \Illuminate\Http\Response
     */
    public function updateMarketBases(Request $request)
    {
        // flush first caching
        Cache::clear();

        $markets = array_pluck(ExchangeMarket::all(), 'item_id'); // list all markets
        $selected_bases = $request->bases;
        $removed = ExchangeMarket::whereIn('item_id', array_diff($markets, $selected_bases))->delete(); // delete non-selected

        // add new item or update
        if ($request->bases) {
            foreach ($request->bases as $order => $base) {
                ExchangeMarket::updateOrCreate([
                        'item_id'=>$base
                    ], [
                        'item_id'=>$base,
                        'order' => $order
                ]);
            }
        }

        sort($selected_bases);
        sort($markets);

        // retu
        if ($selected_bases != $markets) {
            return response()->json(['flash_message' => 'Updated', 'status'=>'OK'], 200);
        } else {
            return response()->json(['status'=>'ERROR'], 400);
        }
    }
}
