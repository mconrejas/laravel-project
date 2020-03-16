<?php

namespace Buzzex\Http\Controllers\Main;

use Auth;
use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Events\ExchangePairStatUpdatedEvent;
use Buzzex\Events\OrderBookAddedOrUpdatedEvent;
use Buzzex\Events\OrderHistoryAddedEvent;
use Buzzex\Events\TradingViewEvent;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Http\Controllers\Main\Traits\InteractsWithTradeForm;
use Buzzex\Http\Requests\BuySellRequest;
use Buzzex\Models\ExchangeApi;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\ExchangePair;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Models\User;
use Buzzex\Repositories\ExchangeRepository;
use Buzzex\Repositories\UserRepository;
use Buzzex\Services\MarketService;
use Buzzex\Services\TradingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class TradeController extends Controller
{
    use InteractsWithTradeForm;

    /**
     * @var UserRepository
     */
    private $users;

    /**
     * @var ExchangeRepository
     */
    private $exchanges;

    /**
     * @var TradingService
     */
    private $trading;

    /**
     * @var MarketService
     */
    private $markets;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserRepository $users,
        ExchangeRepository $exchanges,
        TradingService $trading,
        MarketService $markets
    ) {
        $this->middleware(['auth', '2fa', 'verified'])->except(['processMatchedLocalFromStream']);

        $this->users = $users;
        $this->exchanges = $exchanges;
        $this->trading = $trading;
        $this->markets = $markets;
    }

    /**
     * Process trade form request
     *
     * @param \Buzzex\Http\Requests\BuySellRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function processForm(BuySellRequest $request)
    {
        $data = $this->getData($request, $this->exchanges);

        if ($request->form_type === 'market' && !isset($data['pair_text'])) {
            throw new Exception(__('There are no '.($request->action === 'buy' ? 'sell' : 'buy').' orders in the market at the moment. Please try limit order instead.'));
        }

        if ($data['amount'] <= 0) {
            throw new Exception(__('Amount must not be less than or equal to zero.'));
        }


        // process trade
        $order = $this->users->trade($request->user(), $data);

        if ($order) {
            $this->triggerPairStatUpdate($data['pair']);
            broadcast(new OrderHistoryAddedEvent($this->formatOrderData($order)));
            broadcast(new OrderBookAddedOrUpdatedEvent($order));
            broadcast(new TradingViewEvent($order->pair_id, $order->pairStat->pair_text));
            broadcast(new ExchangePairStatUpdatedEvent($order->pairStat));
            $funds = $request->user()->getFundsByPairText($order->pairStat->pair_text);
            $data = array_merge($data, $funds);
        }

        return response()->json($data, 200);
    }

    /**
     * @param mixed|array
     * @return array
     */
    private function formatOrderData($order)
    {
        return [
            'order_id' => $order->order_id,
            'pair_id' => $order->pair_id,
            'user_id' => $order->user_id,
            'time' => Carbon::createFromTimestamp($order->created)->format('Y-m-d H:i:s'),
            'type' => $order->isStopLimit() ? 'stop-limit' : $order->form_type,
            'side' => strtolower($order->type),
            'price' => currency(@$order->price ? $order->price : 0),
            'amount' => currency($order->amount),
            'unexecuted' => currency($order->amount - $order->fulfilled_amount),
            'executed' => currency($order->fulfilled_amount),
            'avg_price' => currency($order->target_amount),
            'pair_name' => $order->pair->name,
        ];
    }

    /**
     * @param \Buzzex\Models\ExchangePair $pair
     * @return void
     */
    private function triggerPairStatUpdate(ExchangePair $pair)
    {
        $markets = app(Marketable::class);
        $markets->updateExchangePairStats($pair);
    }

    /**
     * Process matched local orders from stream orders
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function processMatchedLocalFromStream(Request $request)
    {
        $request->validate([
            'order_id' => 'required|numeric|min:0',
            'module'  => 'required|string',
            'margin'  => 'required|numeric|min:0',
            'orig_price' => 'required|numeric|min:0.00000001'
        ]);

        // if already on process then dont continue
        if (Cache::has('order_id_'.$request->order_id)) {
            return response()->json(['status' => false], 200);
        }

        Cache::put('order_id_'.$request->order_id, 'pending', now()->addMinutes(1));

        $matched_order = ExchangeOrder::findOrFail($request->order_id);

        if (!$matched_order->isFulfilled() && !$matched_order->isCancelled()) {
            $pair = $matched_order->pair;
            $user = $matched_order->user;
            $data = [
                'pair_id' => $matched_order->pair_id,
                'action' => $matched_order->type,
                'amount' => ($matched_order->amount - $matched_order->fulfilled_amount),
                'price' =>  $request->streamPrice ,
                'margin' => $request->margin,
                'module' => json_decode($request->module, true),
                'order_id' => $request->order_id,
                'form_type' => $matched_order->form_type,
                'orig_price' => $request->orig_price
            ];

            $order = $this->trading->trade($user, $data);

            $data = [
                'status' => false,
                'order_id' => $order->order_id
            ];
            if ($order->isFulfilled() || ($order->isPartiallyFulfilled() && $order->fulfilled_amount > $matched_order->fulfilled_amount)) {
                $tickers = str_replace('_', ',', $pair->exchangePairStat->pair_text);
                $this->triggerPairStatUpdate($pair);
                broadcast(new OrderHistoryAddedEvent($this->formatOrderData($order)));
                broadcast(new OrderBookAddedOrUpdatedEvent($order));
                broadcast(new TradingViewEvent($order->pair_id, $order->pairStat->pair_text));
                broadcast(new ExchangePairStatUpdatedEvent($order->pairStat));
                Artisan::call('get-external-balance:run', ['tickers' => $tickers]);
                $data['status'] = "ok";
            }
            
            Cache::forget('order_id_'.$request->order_id);

            return response()->json($data, 200);
        }

        return response()->json(['status' => false], 200);
    }
}
