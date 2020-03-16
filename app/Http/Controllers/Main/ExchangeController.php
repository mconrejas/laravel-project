<?php

namespace Buzzex\Http\Controllers\Main;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Http\Controllers\Main\Traits\Marketable;
use Buzzex\Http\Controllers\Main\Traits\Serverable;
use Buzzex\Http\Controllers\Main\Traits\Validable;
use Buzzex\Http\Requests\GetCurrentOrdersRequest;
use Buzzex\Http\Requests\GetOrderHistoryRequest;
use Buzzex\Repositories\ExchangeRepository;
use Buzzex\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Models\ExchangePair;
use Buzzex\Models\ExchangeApi;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Services\BinanceService;
use Buzzex\Crypto\Exchanges\ExternalExchangeServiceFactory;

class ExchangeController extends Controller
{
    use Marketable, Serverable, Validable;

    /**
     * @var ExchangeRepository
     */
    protected $exchangeRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * ExchangeController constructor.
     *
     * @param ExchangeRepository $exchangeRepository
     * @param UserRepository $userRepository
     */
    public function __construct(ExchangeRepository $exchangeRepository, UserRepository $userRepository)
    {
        $this->exchangeRepository = $exchangeRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Show Trading Page
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->has('target') && !$this->validTargetCoin($request->get('target'))) {
            abort(422, __('Invalid target coin.'));
        }

        if ($request->has('base') && !$this->validBaseCoin($request->get('base'))) {
            abort(422, __('Invalid base coin.'));
        }

        $base = strtoupper($request->get('base', 'BTC'));
        $target = strtoupper($request->get('target', 'ETH'));
        $pair_text = $base.'_'.$target;

        abort_unless($this->validPairText($pair_text), 404, __('This pair is no longer exist.'));

        $funds = auth()->check() ? auth()->user()->getFundsByPairText($pair_text) : [];
        $userId = auth()->check() ? auth()->user()->id : 0;
        $pair = ExchangePair::join('exchange_pairs_stats', 'exchange_pairs_stats.pair_id', '=', 'exchange_pairs.pair_id')
                    ->where('exchange_pairs_stats.pair_text', $pair_text)
                    ->first();
        
        $data = [
            'bases' => getBases(),
            'base' => $base,
            'target' => $target,
            'baseBalance' => $funds['base'] ?? 0,
            'targetBalance' =>  $funds['target'] ?? 0,
            'decimal' => Cookie::get('buzzex_decimal') ? Cookie::get('buzzex_decimal') : 8,
            'user_discount_percentage' => auth()->check() ? auth()->user()->getPercentageFeeDiscounts() : 0,
            'filterBalance' => parameter('filter_exchange_balance', 0),
            'pair_tolerance' => $pair->tolerance_level,
            'tolerance' => parameter('exchange_tolerance', 3),
            'external_balance' => 0,
            'limitTypeIsDisabled' => (bool) parameter('limitTypeIsDisabled', 0),
            'marketTypeIsDisabled' => (bool) parameter('marketTypeIsDisabled', 1),
            'stopLimitTypeIsDisabled' => (bool) parameter('stopLimitTypeIsDisabled', 1),
            'min_cost' => $pair->getMinCost('local'),
            'min_amount' => $pair->getMinAmount('local'),
            'max_amount' => $pair->getMaxAmount('local'),
            'min_price' => $pair->getMinPrice('local'),
            'max_price' => $pair->getMaxPrice('local'),
            'profit_margin' => 0,
            'binance_is_market_available' => false
        ];
        // dd($data);
        $depth = $this->exchangeRepository->getDepthByPairText($pair_text);
        
        if (!$depth) {
            $depth = $this->exchangeRepository->getPairInfoByPairText($pair_text);
        }

        if (parameter('binance_external_exchange_available', 0) == 1) {
            // initiate binance service
            $binanceService = BinanceService::create(['pair_stat' => $pair->exchangePairStat ]);

            if ($binanceService) {
                $data['binance_is_market_available'] = $binanceService->checkMarket($target.'/'.$base);
                $data['external_balance_base'] = $this->getExternalBalance($binanceService, strtoupper($base));
                $data['external_balance_target'] = $this->getExternalBalance($binanceService, strtoupper($target));
                $data['profit_margin'] = getApiProfitMargin("binance");
                //override minimum trade from external
                $data['min_cost'] = $pair->getMinCost('binance');
                $data['min_amount'] = $pair->getMinAmount('binance');
                $data['max_amount'] = $pair->getMaxAmount('binance');
                $data['min_price'] = $pair->getMinPrice('binance');
                $data['max_price'] = $pair->getMaxPrice('binance');
            }
        }

        $data = array_merge($data, $depth);
        // dd($data);
        return view('main.exchange.exchange', $data);
    }

    /**
     * Get specific free balance
     * @param ExchangeService $service
     * @param string $ticker
     * @return float
     */
    private function getExternalBalance($service, $ticker)
    {
        $response = $service->checkBalance();
        if ($response && array_key_exists($ticker, $response)) {
            return $response[$ticker]['free'];
        }
        return 0;
    }

    /**
     * Change Base for Trading Page
     */
    public function setBase(Request $request)
    {
        $userId = auth()->check() ? auth()->user()->id : 0;
        Cookie::queue(Cookie::make('active-tab-' . $userId, $request->get('base', 'BTC'), 2628000));

        return response()->json([], 200);
    }

    /**
     * Request being passed
     * pair_id   -- integer,
     * from      -- timestamp
     * to        -- timestamp
     * is_last   -- integer 0 or 1 if 1 should only return the last bars oclhv
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBars(Request $request)
    {
        $data = $this->exchangeRepository->getOhlcv($request->all());

        return response()->json($data);
    }

    /**
     * https://www.screencast.com/t/vjjgMNJtqaJ
     *
     * Request being passed
     *  for      -- string 'current-order'
     *  limit    -- integer
     *  filter   -- string 'limit' or 'stop-limit'
     *   -- for filter 'limit' the ff: are needed data
     *    time -- execution time
     *    type
     *    side --buy and sell not sure
     *    price
     *    amount
     *    unexecuted
     *    executed
     *    avg_price
     *    pair_name -- like BTC/ADZ
     *   -- for filter 'stop-limit' the ff: are needed data
     *    time -- order time
     *    type
     *    side --buy and sell not sure
     *    price
     *    amount
     *    avg_price
     *    pair_name -- like BTC/ADZ
     *
     * @param GetCurrentOrdersRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentOrders(GetCurrentOrdersRequest $request)
    {
        $filters = $request->all();
        if ($request->filter == 'stop-limit') {
            $filters['stoplimit'] =  1;
        }
        return response()->json($this->userRepository->getCurrentOrders($request->user(), $filters));
    }

    /**
     * * https://www.screencast.com/t/ALYVMoJwjx
     *
     * Request being passed
     *  for      -- string 'current-order'
     *  limit    -- integer
     *  filter   -- string 'normal' or 'stop-limit'
     *  from -- date in format of Y-m-d, mainly used on filtering by date range, optional
     *  to -- date in format of Y-m-d, mainly used on filtering by date range, optional
     *   -- for filter 'limit' the ff: are needed data
     *    time -- the execution time
     *    type
     *    side
     *    price
     *    amount
     *    unexecuted
     *    executed
     *    avg_price
     *    pair_name -- like 'BTC/ADZ'
     *   -- for filter 'normal' the ff: are needed data
     *    time -- the order time
     *    type
     *    side
     *    price
     *    amount
     *    stop_price
     *    pair_name -- like 'BTC/ADZ'
     *    execution -- not sure this maybe its  the execution time
     *
     * @param GetOrderHistoryRequest $req
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderHistory(GetOrderHistoryRequest $request)
    {
        $filters = $request->all();
        if ($request->filter == 'stop-limit') {
            $filters['stoplimit'] =  1;
        }
        return response()->json(
            $this->userRepository->getOrderHistory($request->user(), $filters)
        );
    }

    /**
     * Show individual order tabs
     *
     * @return \Illuminate\Http\Response
     */
    public function showOrderTab(Request $request)
    {
        $tab = $request->tab;

        abort_if(!in_array($tab, ['current-order', 'order-history', 'latest-execution']), 404);

        $data = [
            'tab' => $tab,

        ];

        return view('main.exchange.orders', $data);
    }
}
