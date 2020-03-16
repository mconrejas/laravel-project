<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\ExchangeTransaction;
use Buzzex\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('admin.total-trades.index');
    }

    /**
     * @param \Illuminate\Support\Facades\Request $request
     * @return \Illuminate\Support\Facades\Response | mixed
     */
    public function getTotalRecords(Request $request)
    {
        $data = array();
        $page = $request->has('page') ? $request->page : 1;
        $size = $request->has('size') ? $request->size : 100;
        $module = $request->has('module') ? $request->module: 'exchange_fulfillments';

        $query =   ExchangeTransaction::from('exchange_transactions as et')
                    ->leftJoin('exchange_fulfillments as ef', DB::raw('et.module_id'), '=', DB::raw('ef.fulfillment_id'))
                    ->leftJoin('exchange_orders as eo', DB::raw('ef.buy_order_id'), '=', DB::raw('eo.order_id'))
                    ->leftJoin('exchange_pairs as ep', DB::raw('eo.pair_id'), '=', DB::raw('ep.pair_id'))
                    ->leftJoin('users as u', DB::raw('et.user_id'), '=', DB::raw('u.id'))
                    ->select(DB::raw('sum(ABS(et.amount)*item_btc_price) as total_btc,sum(ABS(et.amount)*item_usd_price) as total_usd,count(transaction_id) as total_trades,et.user_id, u.email'))
                    ->whereRaw('et.module ="'.$module.'"')
                    ->whereRaw('item_id = ep.item1')
                    ->whereRaw('et.user_id <> '.parameter('external_exchange_order_user_id', 1));
        
        if ($request->has('user') && !empty(trim($request->user))) {
            $key = preg_replace("/[^a-zA-Z0-9]/", "", trim($request->user));
            $query = $query->whereRaw('u.email like "%'.$key.'%" OR u.first_name like "%'.$key.'%" OR u.last_name like "%'.$key.'%" OR et.user_id = "'.$key.'"');
        }

        $query = $query->groupBy(DB::raw('et.user_id'));
        $counts = $query->count();

        $query = $query->orderBy(DB::raw('sum(ABS(et.amount)*item_usd_price)'), 'desc')
                        ->skip($page - 1)
                        ->take($size)
                        ->get();
        // dd($query);
        if ($query) {
            foreach ($query as $key => $item) {
                $data[] = array(
                            'total_trades' => $item->total_trades,
                            'total_btc' => $item->total_btc,
                            'total_usd' => $item->total_usd,
                            'user_id' => $item->user_id,
                            'user_email' => $item->email
                        );
            }
        }

        return response()->json([
            'last_page' => ceil($counts / $size),
            'data' => $data,
            'all_counts' => $counts
        ], 200);
    }
}
