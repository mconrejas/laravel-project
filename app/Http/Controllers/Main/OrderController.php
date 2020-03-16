<?php

namespace Buzzex\Http\Controllers\Main;

use Buzzex\Contracts\User\Tradable;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\ExchangeOrder;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $trading;

    public function __construct(Tradable $trading)
    {
        $this->trading = $trading;
    }

    /**
     * Cancel Order
     * @param Illuminate\Http\Request $request
     * @return mixed
     */
    public function cancelOrder(Request $request)
    {
        $request->validate([ 'order_id' => 'required|numeric' ]);

        $order = ExchangeOrder::where('user_id', '=', auth()->user()->id)->findOrFail($request->order_id);

        abort_unless($order && $order->completed == 0, 422, __('Cancelling an order multiple times is not allowed.'));

        if ($this->trading->cancelOrder($order)) {
            $order->balance = auth()->user()->getFundsByPairText($order->pairStat->pair_text);
        }

        return response()->json($order, 200);
    }
}
