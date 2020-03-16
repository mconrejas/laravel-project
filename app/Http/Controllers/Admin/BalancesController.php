<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Contracts\User\CanManageOwnFund;
use Buzzex\Http\Controllers\Admin\Traits\ManageTransactionHistory;
use Buzzex\Http\Controllers\Admin\Traits\ManageWallets;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\User;
use Illuminate\Http\Request;

class BalancesController extends Controller
{
    use ManageWallets, ManageTransactionHistory;

    /**
     * Show balance index page
     *
     * @return \Illuminate\Support\Facades\View
     */
    public function index()
    {
        return view('admin.balances.index');
    }

    /**
     * get item balance records
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function getBalances(Request $request)
    {
        $data = array();
        $size = isset($request->size) ? $request->size: 100;
        $page = isset($request->page) ? $request->page: 1;

        $items = ExchangeItem::where('type', '<>', 4)
                    ->where('deleted', '=', 0);
        $count = $items->count();

        $app = app()->make(CanManageOwnFund::class);
        $funds = $app->getAllFunds(true);

        $items = $items->skip($size * ($page-1))
                    ->take($size)
                    ->get();

        $data = $items->map(function ($item) use ($funds) {
            $item->trade_fees = $this->getTotalTradeFee($item->item_id);
            $item->withdrawals = $this->getTotalWithdrawals($item->item_id);
            $item->deposits = $this->getTotalDeposits($item->item_id);
            $item->reserved_in_orders = $this->getTotalReserveInOrders($item->item_id);
            $item->total_balance = isset($funds[$item->symbol]) ? currency($funds[$item->symbol]) : 0;
            $item->available_balance = isset($funds[$item->symbol]) ? currency($funds[$item->symbol] - $item->reserved_in_orders) : 0;
            $item->history = route('history.item', ['ticker' => $item->symbol]);
            return $item;
        });
        
        $response =  array(
            'last_page' => ceil($count / $size),
            'data' => $data
        );

        return response()->json($response, 200);
    }

    /**
     * Show item history index page
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\View
     */
    public function itemHistory(Request $request)
    {
        $addresses = array();
        $type = isset($request->type) ? $request->type : 'deposits';
        $user = isset($request->user) ? $request->user : 'All users';

        return view('admin.balances.item-history', compact('addresses', 'type', 'user'));
    }

    /**
     * Show pair history index page
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\View
     */
    public function pairHistory(Request $request)
    {
        $addresses = array();
        $type = isset($request->type) ? $request->type : 'orders';
        $user = isset($request->user) ? $request->user : 'All users';

        return view('admin.balances.pair-history', compact('addresses', 'type', 'user'));
    }

    /**
     * get item history records
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function getItemHistory(Request $request)
    {
        $data = array();
        $size = isset($request->size) ? $request->size: 100;
        $page = isset($request->page) ? $request->page: 1;
        $type = isset($request->type) ? $request->type: 'deposits';
        $ticker = isset($request->ticker) ? $request->ticker: 'all';
        $users = [];
        
        if (isset($request->user) &&  $request->user != 'All users' && !empty($request->user)) {
            if (is_numeric($request->user) && $request->user > 0) {
                $users = [ $request->user ];
            } else {
                $users = User::where('first_name', 'like', '%'.$request->user.'%')
                        ->orWhere('last_name', 'like', '%'.$request->user.'%')
                        ->orWhere('email', 'like', '%'.$request->user.'%')
                        ->pluck('id')
                        ->toArray();
            }
        }

        $history = $this->getItemHistories($ticker, $type, $users, $size, $page - 1);

        $response =  array(
            'last_page' => ceil($history['count'] / $size),
            'data' => $history['data'],
            'counts' => $history['count']
        );

        return response()->json($response, 200);
    }

    /**
     * get pair history records
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function getPairHistory(Request $request)
    {
        $data = array();
        $size = isset($request->size) ? $request->size: 100;
        $page = isset($request->page) ? $request->page: 1;
        $type = isset($request->type) ? $request->type: 'orders';
        $pair_id = isset($request->pair_id) ? $request->pair_id: 'all';
        $users = [];
        
        if (isset($request->user) &&  $request->user != 'All users' && !empty($request->user)) {
            if (is_numeric($request->user) && $request->user > 0) {
                $users = [ $request->user ];
            } else {
                $users = User::where('first_name', 'like', '%'.$request->user.'%')
                        ->orWhere('last_name', 'like', '%'.$request->user.'%')
                        ->orWhere('email', 'like', '%'.$request->user.'%')
                        ->pluck('id')
                        ->toArray();
            }
        }
        $history = $this->getPairHistories($pair_id, $type, $users, $size, $page - 1);

        $response =  array(
            'last_page' => ceil($history['count'] / $size),
            'data' => $history['data'],
            'counts' => $history['count']
        );

        return response()->json($response, 200);
    }
}
