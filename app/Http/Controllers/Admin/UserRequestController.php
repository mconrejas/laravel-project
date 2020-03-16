<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\ExchangeUserDepositRequest;
use Illuminate\Http\Request;

class UserRequestController extends Controller
{
    /**
     *
     *
     */
    public function showDepositIndex(Request $request)
    {
        return view('admin.user-requests.deposits');
    }

    /**
     *
     *
     */
    public function userRequestDeposits(Request $request)
    {
        $data = array();
        $size = isset($request->size) ? $request->size : 100;
        $page = isset($request->page) ? $request->page : 1;
        $ticker = isset($request->ticker) ? $request->ticker: 'all';

        $urequests = (new ExchangeUserDepositRequest())->newQuery();

        $count = $urequests->count();
        $urequests = $urequests->orderBy('request_id', 'desc')->skip($page - 1)->take($size)->get();

        if ($urequests) {
            foreach ($urequests as $key => $req) {
                $data[] = [
                    'request_id' => $req->request_id,
                    'user_id' => $req->user_id,
                    'user' => $req->user->email,
                    'api' => $req->api ? $req->api->name : 'local',
                    'item' => $req->item->symbol,
                    'amount' => $req->amount,
                    'created' => $req->created
                ];
            }
        }

        $response =  array(
            'last_page' => ceil($count / $size),
            'data' => $data,
            'counts' => $count
        );

        return response()->json($response, 200);
    }
}
