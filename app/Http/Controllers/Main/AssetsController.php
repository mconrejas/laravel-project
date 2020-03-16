<?php

namespace Buzzex\Http\Controllers\Main;

use Buzzex\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Buzzex\Models\ExchangeTransactionHistory;
use Buzzex\Models\ExchangeTransaction;

class AssetsController extends Controller
{
    /**
     * Show assets
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $operations = assetsOptions();

        return view('main.wallet.asset', compact('operations'));
    }
    /**
     * Get record changes on assets
     *
     * @return \Illuminate\Http\Response
     */
    public function getRecords(Request $request)
    {
        // http://buzzex.local/ph/my/assets request are the ff
        // coin -- string, 'all' or coin short name like 'BTC', if all this mean in any coin
        // size -- integer, this is the limit
        // page -- integer, the page number being requested
        // operation -- string, see operation on index(), if all the result can be be different
        //this should return from latest changes to old changes on assets
        $data = array();
        $size = isset($request->size) ? $request->size: 25;
        $page = isset($request->page) ? $request->page: 1;

        $text = (strtolower($request->text) == 'all') ? '' : trim($request->text);
        $type = ($request->operations == 'all') ? '' : trim($request->operations);

        $records = ExchangeTransaction::selectRaw("exchange_transactions.*, exchange_items.symbol")
                        ->where('exchange_transactions.user_id', '=', auth()->user()->id)
                        ->leftJoin('exchange_items', 'exchange_transactions.item_id', 'exchange_items.item_id');
                        
        if (!empty($text)) {
            $records = $records->where('exchange_items.symbol', 'like', '%' . $text . '%');
        }
        if (!empty($type)) {
            $records = $records->where('exchange_transactions.type', 'like', '%' . $type . '%');
        }

        $count =  $records->count();

        // pagination
        $records = $records->orderBy('exchange_transactions.transaction_id', 'desc')
                    ->skip($size * ($page-1))
                    ->take($size)
                    ->get();

        if ($records) {
            foreach ($records as $record) {
                $data[] = array(
                        'time'=> date('Y-m-d H:i:s', $record->created),
                        'coin'=> $record->symbol,
                        'operation'=> ucfirst($record->type),
                        'coinName'=> '',
                        'amount'=> abs($record->amount), //'amount'=> $record->amount,
                        'status'=> $record->cancelled > 0 ? 'cancelled':'approved',
                        'address' => !empty($record->address) ? $record->address: $record->remarks,
                        'txid' => empty($record->txid) ? '-' : $record->txid
                    );
            }
        }
 
        $response =  array(
            'last_page' => ceil($count / $size), // allrecord count divide by request->size
            'data' => $data,
            'count' => $count
        );

        return response()->json($response);
    }
}
