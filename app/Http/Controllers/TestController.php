<?php

namespace Buzzex\Http\Controllers;

use Buzzex\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Constructor for test controller
     */
    public function __construct()
    {
        // abort_unless(app()->environment('local') == 'local', 404);
    }

    /**
     * test function here if you dont know how to use unit testing
     * http://buzzex.local/en/test
     */
    public function index(Request $request)
    {
        $exchangeOrder = \Buzzex\Models\ExchangeOrder::find(1);
        $user = \Buzzex\Models\User::findOrFail($request->id ?? 0);
        $user->notify(new \Buzzex\Notifications\OrderFulfilledNotification($exchangeOrder));
        dd('done');
    }

    public function mailable()
    {
        //return new \Buzzex\Mail\Auth\LoginSuccessful(\Buzzex\Models\User::find(1));
    }

    /**
     * test function for getting blockchain data for coin integration
     * http://buzzex.local/en/testing/{COIN_SYMBOL}
     * @param $lang string
     * @param $coin string
     *
     * @return void
     */
    public function testCoinIntegration($lang = 'en', $coin='')
    {
        //$address = \Illuminate\Support\Facades\DB::table(strtolower($coin).'_addresses')->first();
        // dd($address);
        //$item = (new \Buzzex\Models\ExchangeItem())->where('symbol', strtoupper($coin))->first();
        // dd($item);
        //$trading = app()->make(\Buzzex\Contracts\User\Tradable::class);
        // dd($trading);
        //dump($trading->downloadDepositsByAddress($address, $item));
    }
}
