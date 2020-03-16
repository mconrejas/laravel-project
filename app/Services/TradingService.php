<?php

namespace Buzzex\Services;

use Buzzex\Contracts\User\Tradable;
use Buzzex\Crypto\Currency\CoinAddress;
use Buzzex\Crypto\Currency\CoinFactory;
use Buzzex\Crypto\Currency\Coins\Exceptions\CoinNotFoundException;
use Buzzex\Crypto\Exchanges\Arbitrage;
use Buzzex\Crypto\Exchanges\ExternalExchangeServiceFactory;
use Buzzex\Events\LatestExecutionEvent;
use Buzzex\Events\OrderBookAddedOrUpdatedEvent;
use Buzzex\Exceptions\InvalidPairException;
use Buzzex\Exceptions\NotEnoughFundsException;
use Buzzex\Jobs\DownloadAddressDeposits;
use Buzzex\Models\ExchangeApi;
use Buzzex\Models\ExchangeFulfillment;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\ExchangePair;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Models\ExchangeTransaction;
use Buzzex\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class TradingService extends BaseService implements Tradable
{
    /**
     * @var UserService
     */
    private $users;

    /**
     * @var MarketService
     */
    private $markets;

    /**
     * Determines if debugging is on or off
     * @var bool
     */
    private $debug = false;

    /**
     * TradingService constructor.
     *
     * @param ExchangeOrder $model
     */
    public function __construct(
        ExchangeOrder $model,
        UserService $users,
        MarketService $markets
    ) {
        parent::__construct($model);

        $this->users = $users;
        $this->markets = $markets;
    }

    /**
     * @param User $user
     * @param array $filters
     *
     * @return array
     */
    public function getCurrentOrders(User $user, $filters = [], $orderBy = 'order_id', $orderDirection = 'desc')
    {
        $response = [];
        $query = $this->model->newQuery()
            ->with(['pair'])
            ->leftJoin('exchange_pairs_stats', 'exchange_pairs_stats.pair_id', '=', 'exchange_orders.pair_id')
            ->where('completed', 0)
            ->where('exchange_orders.target_amount', '>', 0);

        if (!isset($filters['no-user'])) {
            $query = $query->where('user_id', $user->id);
        }

        if (isset($filters['stoplimit'])) {
            $query = $query->where('form_type', '=', 'stop-limit');
        } else {
            $query = $query->whereIn('form_type', ['limit', 'market']);
        }

        if (isset($filters['pair_id'])) {
            $query = $query->where('exchange_orders.pair_id', $filters['pair_id']);
        }

        if (isset($filters['side']) && $filters['side'] !== 'all') {
            $query = $query->where('exchange_orders.type', strtoupper($filters['side']));
        }

        if (isset($filters['pair']) && $filters['pair'] !== '') {
            $query = $query->where('exchange_orders.pair_id', $filters['pair']);
        }

        if (!isset($filters['size'])) {
            $filters['size'] = $filters['limit'];
            $filters['page'] = 1;
        }

        $response['last_page'] = ceil($query->count() / $filters['size']);

        $query = $query->skip($filters['size'] * ($filters['page'] - 1))->take($filters['size'])
            ->orderBy("exchange_orders.{$orderBy}", $orderDirection);


        $response['data'] = $query->get()
            ->mapWithKeys(function ($item, $key) {
                return [
                    $key => [
                        'order_id'   => $item->order_id,
                        'user_id'    => $item->user_id,
                        'time'       => Carbon::createFromTimestamp($item->created)->format('Y-m-d H:i:s'),
                        'type'       => $item->isStopLimit() ? 'stop-limit' : $item->form_type,
                        'side'       => strtolower($item->type),
                        'price'      => currency($item->price),
                        'amount'     => currency($item->amount),
                        'unexecuted' => currency($item->amount - $item->fulfilled_amount),
                        'executed'   => currency($item->fulfilled_amount),
                        'avg_price'  => currency($item->target_amount),
                        'pair_name'  => $item->pair->name,
                        'fee'        => currency($item->fee),
                        'completed'  => $item->completed,
                        'remarks'    => $item->remarks,
                    ],
                ];
            });

        if (isset($filters['side'])) {
            return $response;
        } else {
            return $response['data'];
        }
    }

    /**
     * @param User $user
     * @param array $filters
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getOrderHistory(User $user, $filters = [])
    {
        $response = [];

        $query = $this->model->newQuery()
            ->with(['pair'])
            ->leftJoin('exchange_pairs_stats', 'exchange_pairs_stats.pair_id', '=', 'exchange_orders.pair_id')
            ->where('user_id', $user->id);


        if (isset($filters['stoplimit'])) {
            $query = $query->where('form_type', '=', 'stop-limit');
        } else {
            $query = $query->whereIn('form_type', ['limit', 'market']);
        }

        if (isset($filters['pair_id'])) {
            $query = $query->where('exchange_orders.pair_id', $filters['pair_id']);
        }

        if (isset($filters['side']) && $filters['side'] !== 'all') {
            $query = $query->where('exchange_orders.type', strtoupper($filters['side']));
        }

        if (isset($filters['pair']) && $filters['pair'] !== '') {
            $query = $query->where('exchange_orders.pair_id', $filters['pair']);
        }

        if (isset($filters['from'])) {
            $query = $query->where('exchange_orders.created', '>', strtotime($filters['from'] . " 00:00:00"));
        }

        if (isset($filters['to'])) {
            $query = $query->where('exchange_orders.created', '<', strtotime($filters['to'] . " 24:59:59"));
        }

        if (!isset($filters['size'])) {
            $filters['size'] = $filters['limit'];
            $filters['page'] = 1;
        }

        if (isset($filters['order_by']) && !empty($filters['order_by']) && isset($filters['order_as']) && !empty($filters['order_as'])) {
            $query = $query->orderBy($filters['order_by'], strtoupper($filters['order_as']) == 'ASC' ? 'ASC' : 'DESC');
        }

        $response['last_page'] = ceil($query->count() / $filters['size']);

        $query = $query->skip($filters['size'] * ($filters['page'] - 1))->take($filters['size']);

        $response['data'] = $query->get()
            ->mapWithKeys(function ($item, $key) {
                return [
                    $key => [
                        'order_id'   => $item->order_id,
                        'user_id'    => $item->user_id,
                        'time'       => Carbon::createFromTimestamp($item->created)->format('Y-m-d H:i:s'),
                        'type'       => $item->isStopLimit() ? 'stop-limit' : $item->form_type,
                        'side'       => strtolower($item->type),
                        'price'      => currency($item->price),
                        'amount'     => currency($item->amount),
                        'unexecuted' => currency($item->amount - $item->fulfilled_amount),
                        'executed'   => currency($item->fulfilled_amount),
                        'avg_price'  => currency($item->target_amount),
                        'pair_name'  => $item->pair->name,
                        'stop_price' => currency($item->stop_price),
                        'execution'  => Carbon::createFromTimestamp($item->completed)->format('Y-m-d H:i:s'),
                        'fee'        => currency($item->fee),
                    ],
                ];
            });

        if (isset($filters['side'])) {
            return $response;
        } else {
            return $response['data'];
        }
    }

    /**
     * @param array $params
     *
     * @return array
     * @throws InvalidPairException
     */
    public function getOhlcv(array $params = [])
    {
        $pair = null;

        if (array_key_exists('pair_id', $params)) {
            $pair = ExchangePair::find($params['pair_id']);
        }

        if (!$pair) {
            throw new InvalidPairException(__('Invalid pair.'));
        }

        $currentTime = Carbon::now()->timestamp;
        $from = isset($params['from']) ? $params['from'] : Carbon::now()->startOfDay()->timestamp;
        $to = isset($params['to']) ? $params['to'] : $currentTime;

        $exchangeFulfillment = $this->getLastExchangeFulfillment($pair, $from, $to);

        $lastFid = ($exchangeFulfillment) ? $exchangeFulfillment->fulfillment_id : 0;

        $data = [
            'last_fid' => $lastFid,
            'from'     => $from,
            'to'       => $to,
            'bars'     => [],
        ];

        $bars = [];

        $query = (new ExchangeFulfillment())->newQuery()
            ->selectRaw('FLOOR(exchange_fulfillments.created/600)*600 as date_10min, exchange_fulfillments.amount, exchange_fulfillments.price')
            ->leftJoin('exchange_orders', 'exchange_orders.order_id', '=', 'exchange_fulfillments.sell_order_id')
            ->where('exchange_orders.pair_id', $pair->pair_id)
            ->whereBetween('exchange_orders.created', [$from, $to]);

        if ($lastFid > 0) {
            $query->where('exchange_fulfillments.fulfillment_id', '<=', $lastFid);
        }

        $results = $query->orderBy('exchange_fulfillments.created', 'ASC')
            ->get();

        $ohlc = [];
        $volume = [];
        $index = 0;

        if ($results) {
            foreach ($results->toArray() as $item) {
                $ohlc[$item['date_10min']][] = $item['price'];
                $volume[$item['date_10min']][] = $item['amount'];
            }


            foreach ($ohlc as $date => $info) {
                $length = count($info);

                $open = !$index ? $info[0] : $bars[$index - 1]['close'];

                $max = max($info);

                if ($max < $open) {
                    $max = $open;
                }

                $min = min($info);

                if ($min > $open) {
                    $min = $open;
                }

                $bars[] = [
                    's'      => 'ok',
                    'time'   => $date * 1000,
                    'open'   => $open,
                    'high'   => $max,
                    'low'    => $min,
                    'close'  => $info[$length - 1],
                    'volume' => array_sum($volume[$date]),
                ];

                $index++;
            }
        }

        if (isset($params['is_last']) && $params['is_last'] === 1) {
            $data['bars'] = empty($bars) ? [] : array_pop($bars);
        } else {
            $data['bars'] = $bars;
        }

        return $data;
    }

    /**
     * @param ExchangePair $pair
     * @param string $from
     * @param string $to
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null|object
     */
    protected function getLastExchangeFulfillment(ExchangePair $pair, $from, $to)
    {
        return (new ExchangeFulfillment())->newQuery()
            ->leftJoin('exchange_orders', 'exchange_orders.order_id', '=', 'exchange_fulfillments.sell_order_id')
            ->where('exchange_orders.pair_id', $pair->pair_id)
            ->whereBetween('exchange_orders.created', [$from, $to])
            ->orderBy('exchange_fulfillments.fulfillment_id', 'desc')
            ->first();
    }

    /**
     * @param User $user
     * @param array $params
     *
     * @return bool|\Buzzex\Models\ExchangeOrder
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Buzzex\Exceptions\InvalidPairException
     * @throws \Buzzex\Exceptions\NotEnoughFundsException
     */
    public function trade(User $user, array $params)
    {
        if (!$user) {
            throw new AuthenticationException(__('Please specify a valid user.'));
        }

        $pairInfo = $this->markets->getPairInfoByPairId($params['pair_id']);
        $pair = $pairInfo['pairObject'];
        $order_module = "";

        if (!$pair) {
            throw new InvalidPairException(__('Invalid pair.'));
        }

        $funds = $this->users->getFundsByTickers(false, $user, [$pair->exchangeItemTwo->symbol,$pair->exchangeItemOne->symbol]);
        $systemUserId = parameter('external_exchange_order_user_id');
        $type = strtoupper($params['action']);
        $amount = $params['amount'];
        $price = $params['price'];

        if ($amount <= 0) {
            throw new Exception(__('Amount cannot be less than or equal to zero.'));
        }

        $targetAmount = $this->getTargetAmount($amount, $price);
        $feeAmount = $this->getFeeAmount($pair, $user, $targetAmount);
        $total = $targetAmount + $feeAmount;

        if (!array_key_exists('order_id', $params)) {
            $use_balance_symbol  = ($type == "BUY") ? $pair->exchangeItemTwo->symbol : $pair->exchangeItemOne->symbol;

            if ($systemUserId != $user->id && !array_key_exists($use_balance_symbol, $funds)) {
                throw new NotEnoughFundsException(__('Not enough funds in '.$use_balance_symbol));
            }

            if ($systemUserId != $user->id && $type == "BUY" && $total > $funds[$use_balance_symbol]) {
                throw new NotEnoughFundsException(__('Not enough funds in '.$use_balance_symbol));
            }

            if ($systemUserId != $user->id && $type == "SELL" && $amount > $funds[$use_balance_symbol]) {
                throw new NotEnoughFundsException(__('Not enough funds in '.$use_balance_symbol));
            }

            $data = [
                'user_id'       => $user->id,
                'pair_id'       => $pair->pair_id,
                'type'          => strtoupper($type),
                'amount'        => $amount,
                'fee'           => $feeAmount,
                'price'         => $price,
                'target_amount' => $targetAmount,
                'form_type'     => $params['form_type'],
                'created'       => Carbon::now()->timestamp,
                'ip_address'    => get_ip_address(),
                'margin'        => 0 //local orders dont have margin
            ];
            
            if (isset($params['stop']) && isset($params['limit'])) {
                $data['stop_price'] = $params['stop'];
                $data['limit_price'] = $params['limit'];
            } else {
                $data['stop_limit_execution_time'] = Carbon::now()->timestamp;
            }

            $order = $this->model->create($data);
            
            if (!$order) {
                throw new Exception(__("Can't create order. Please contact administrator."));
            }
        } else {
            //dont create a new order if order id is provided
            $order = ExchangeOrder::findOrFail($params['order_id']);
            if (!$order || $order->isFulfilled() || $order->isCancelled()) {
                throw new Exception(__("Can't find order."));
            }

            $order->margin = $params['margin'];
            $order->save();
        }

        $this->runInternalFulfillmentProcess($order);
        $order = $order->fresh();

        $remaining_amount = $order->amount - $order->fulfilled_amount;
        // $remaining_amount = $params['amount'];

        //fullfill the remaining qty to external exchange
        if (array_key_exists('binance', $params['module']) && $remaining_amount > 0 && parameter('binance_external_exchange_available', 0) == 1) {
            $binance_avail_qty = (float) $params['module']['binance'];
            $can_trade_qty = ($binance_avail_qty >= $remaining_amount) ? $remaining_amount : $binance_avail_qty;
 
            $this->runExternalFulfillmentProcess($order, $pair, $type, $can_trade_qty, $order->price, $user, 'binance');

            //refresh the order model
            $order = $order->fresh();
        }

        return $order;
    }

    /**
     *
     *
     */
    protected function runExternalFulfillmentProcess(
        ExchangeOrder $order,
        ExchangePair $pair,
        $type,
        $amount,
        $price,
        User $user,
        $module = ""
    ) {
        $service = ExternalExchangeServiceFactory::create($module, $pair);
        $api_service = ExchangeApi::where('name', $module)->first();

        $margin = ($api_service->profit_margin > 0)?$api_service->profit_margin/100:0;
        $minCost = $pair->getMinCost($module);
        $minAmount = $pair->getMinAmount($module);
        $maxAmount = $pair->getMaxAmount($module);
        $minPrice = $pair->getMinPrice($module);
        $maxPrice = $pair->getMaxPrice($module);
        $tickSize = $pair->getTickSize($module);
        $stepSize = $pair->getStepSize($module);
        Loggy::info('exchange', "minCost:$minCost;minAmount:$minAmount;maxAmount:$maxAmount;minPrice:$minPrice;maxPrice=$maxPrice,tickSize:$tickSize,stepSize:$stepSize");

        if ($amount < $minAmount) {
            Loggy::info('exchange', "amount ($amount) is below minimum ($minAmount)");
            $amount = $minAmount;
        }
        if ($maxAmount > 0 && $amount > $maxAmount) {
            Loggy::info('exchange', "amount ($amount) is above maximum ($maxAmount)");
            $amount = $maxAmount;
        }

        $params = [
            'symbol'   => $service->getPairString(),
            'side'     => $type,
            'quantity' => $amount,
            'price'    => ($type === 'BUY') ?
                        number_format(($price / (1 + $margin)), 8, ".", "") :
                        number_format(($price / (1 - $margin)), 8, ".", "")
        ];

        if ($params["price"] < $minPrice) {
            Loggy::info('exchange', "price ({$params["price"]}) is below minimum ($minPrice)");
            $params["price"] = $minPrice;
        }
        if ($maxPrice > 0 && $params["price"] >$maxPrice) {
            Loggy::info('exchange', "price ({$params["price"]}) is above maximum ($maxPrice)");
            $params["price"] = $maxPrice;
        }

        $fmod_tickSize = fmod($params["price"], $tickSize);
        Loggy::info('exchange', "tickSize: $tickSize; fmod_tickSize: $fmod_tickSize");
        if ($tickSize > 0 && $fmod_tickSize != 0) {
            Loggy::info('exchange', "price ({$params["price"]}) is invalid based on tickSize ($tickSize)");
            $orig_price = $params["price"];
            $params["price"] = ($type === 'BUY')?$params["price"] - $fmod_tickSize:$params["price"] - $fmod_tickSize + $tickSize;
            if ($type === "BUY" && $orig_price > $params["price"]) {
                $params["price"] += $tickSize;
            }
            if ($type !== "BUY" && $orig_price < $params["price"]) {
                $params["price"] -= $tickSize;
            }
        }
        $fmod_stepSize = fmod($params["quantity"], $stepSize);
        if ($stepSize > 0 && $fmod_stepSize != 0) {
            $params["quantity"] = $params["quantity"] - $fmod_stepSize + $stepSize;
        }
        $trade_cost = $params['quantity'] * $params['price'];
        if ($trade_cost < $minCost) {
            $trade_cost_deficit = $minCost - $trade_cost;
            Loggy::info('exchange', "trade cost ($trade_cost) is lower than minCost ($minCost) for origQty ({$params['quantity']})");
            $params['quantity'] += ($trade_cost_deficit/$params['price']); //override quantity to request at least the minimum order cost to external exchange
            $fmod_stepSize2 = fmod($params["quantity"], $stepSize);
            if ($stepSize > 0 && $fmod_stepSize2 != 0) {
                $params["quantity"] = $params["quantity"] - $fmod_stepSize2 + $stepSize;
            }
            for ($i=1; $i<=10; $i++) {
                $trade_cost = $params['quantity'] * $params['price'];
                if ($trade_cost < $minCost) {
                    $params['quantity'] += $stepSize;
                    Loggy::info('exchange', "adjustedQty: {$params['quantity']}; reCalcAttempt# $i");
                } else {
                    break;
                }
            }
        }
        $thisorderonprocess = (int) Cache::get('for_external_order_id_'.$order->order_id, 0);

        //added trap to not trade when equal or lower than min cost and not yet processed
        if (($params['quantity'] * $params['price']) > $minCost && $thisorderonprocess == 0) {
            Cache::put('external-orderbook-fulfillment-' . $service->getExchangePairStat()->pair_id, true, 10);

            $params["price"] = number_format($params["price"], 8, ".", "");
            $params["quantity"] = number_format($params["quantity"], 8, ".", "");
            Loggy::info('exchange', json_encode(['params_sent' => $params,'buzzex-price'=>$price,'api-margin'=>$margin]));

            $external_trade = false;
            try {
                Cache::put('for_external_order_id_'.$order->order_id, 1, now()->addMinutes(1));
                $external_trade =  $service->trade($params);
            } catch (\Exception $e) {
                Loggy::info('exchange', 'Exception on external trade:'. $e->getMessage());
            }

            if ($external_trade) {
                Loggy::info('exchange', json_encode(['params_sent' => $params, 'external_trade_return' => $external_trade]));

                if (isset($external_trade->fills) && !empty($external_trade->fills)) {
                    $external_user = parameter('external_exchange_order_user_id', 1);
                    //had to insert rows one by one based on fulfillments from external exchange so that our users can still get the best price
                    foreach ($external_trade->fills as $key => $fill) {
                        $fill = (object) $fill;
                        $margined_price = $type === 'BUY' ? ($fill->price * (1 + $margin)) : ($fill->price * (1 - $margin)) ;
                        $fillTargetAmount = $this->getTargetAmount($fill->qty, $margined_price);
                        $fillFeeAmount = $this->getFeeAmount($pair, $user, $fillTargetAmount);
                        $orderBook = [
                        'type'       => $type === 'BUY' ? 'SELL' : 'BUY',
                        'price'      => $margined_price ,
                        'amount'     => $fill->qty,
                        'target_amount' => $fillTargetAmount,
                        'fee'        => $fillFeeAmount,
                        'pair_id'    => $service->getExchangePairStat()->pair_id,
                        'module_id'  => $api_service->id,
                        'module'     => $api_service->name,
                        'user_id'    => $external_user,
                        'created'    => Carbon::now()->timestamp,
                        'ip_address' => get_ip_address(),
                        'margin'     => $margin,
                        'form_type'  => strtolower($external_trade->type),
                        ];

                        if ($orderBook["form_type"] == "limit") {
                            $orderBook['stop_limit_execution_time'] = Carbon::now()->timestamp;
                        }

                        $external_order = ExchangeOrder::create($orderBook);
                        if (!$external_order) {
                            throw new Exception(__("Can't create order from external. Please contact administrator."));
                        }
                        $this->runInternalFulfillmentProcess($external_order);
                    }
                }
            }
        
            Cache::forget('external-orderbook-fulfillment-' . $service->getExchangePairStat()->pair_id);
            Cache::forget('for_external_order_id_'.$order->order_id);
        } else {
            Loggy::info('exchange', 'External order ('.$order->order_id.') is lower or equal to min cost:'.$minCost.','. json_encode($params));
        }
    }

    /**
     * @param ExchangeOrder $order
     * @param ExchangePair $pair
     * @param $type
     * @param $amount
     * @param $price
     * @param $feeAmount
     * @param User $user
     */
    protected function runInternalFulfillmentProcess(
        ExchangeOrder $order
    ) {
        $amount = $order->amount - $order->fulfilled_amount;
        //DB::unprepared("call xFFauto({$order->order_id},{$pair->pair_id},'{$type}',{$amount},{$price},{$feeAmount},{$user->id},@affected_orders)");
        DB::unprepared("call xFFauto({$order->order_id},{$order->pair_id},'{$order->type}',{$amount},{$order->price},{$order->fee},{$order->user_id},@affected_orders)");

        $result = DB::select('select @affected_orders');

        if (count($result) > 0 && isset($result[0])) {
            $affected_orders = ((array)$result[0])['@affected_orders'];
            if ($affected_orders !== 'x') {
                Loggy::info('exchange', json_encode(['affected orders' => $affected_orders]));
                Artisan::call('stop-limit:run');
                Artisan::call('orders-are-fulfilled:run', [ 'order_ids' => $affected_orders ]);
                $affected_orders = explode(",", $affected_orders);
                //@todo: add milestone
                foreach ($affected_orders as $index => $affected_order_id) {
                    $order = ExchangeOrder::findOrFail($affected_order_id);
                    if ($order) {
                        broadcast(new OrderBookAddedOrUpdatedEvent($order));
                    }
                }
                //run below if there are fulfillments
                broadcast(new LatestExecutionEvent($order->pairStat->pair_text));
                Artisan::call('exchange-pair-status:update', ['pair_id' => $order->pair_id ]);
            }
        }
    }

    /**
     * @param ExchangeOrder $order
     * @param ExchangePair $pair
     *
     * @return mixed
     */
    protected function getMatchingExternalOrder(ExchangeOrder $order, ExchangePair $pair)
    {
        $externalExchanges = ExternalExchangeServiceFactory::getExternalExchangeServices();

        $data = [];

        foreach ($externalExchanges as $exchangeService) {
            $exchange = $exchangeService::create(['pair_stat' => $pair->exchangePairStat]);
            $data = array_merge($data, $exchange->getInternalOrderBook($pair->pair_id));
        }

        $arbitrage = new Arbitrage($data);

        return $arbitrage->getOrder($order->price, $order->type);
    }

    /**
     * Get Fee Amount
     *
     * @param ExchangePair $pair
     * @param User $user
     * @param float $targetAmount
     * @return float
     */
    public function getFeeAmount(ExchangePair $pair, User $user, $targetAmount = 0)
    {
        $fee_amount = 0;

        if ($targetAmount <= 0) {
            return $fee_amount;
        }

        $fee_percentage = $pair->fee_percentage > 0 ? $pair->fee_percentage : parameter('exchange.trade_fee', 0);

        $fee_amount = $targetAmount * ($fee_percentage / 100);
        $user_percentage_discount = $user->getPercentageFeeDiscounts();

        if ($fee_amount > 0 && $user_percentage_discount > 0) {
            //apply user discount on current fee amount
            $fee_amount = $fee_amount - ($fee_amount * ($user_percentage_discount / 100));
        }

        return $fee_amount;
    }

    /**
     * Get Target Amount
     *
     * @param float $amount
     * @param float $price
     *
     * @return float
     */
    public function getTargetAmount($amount, $price)
    {
        return $amount * $price;
    }

    /**
     * @param ExchangeOrder $stopLimitOrder
     *
     * @return bool
     */
    public function processStopLimit(ExchangeOrder $stopLimitOrder)
    {
        $stopLimitOrder->stop_limit_execution_time = Carbon::now()->timestamp;

        return $stopLimitOrder->save();
    }

    /**
     * Create withdrawal request
     *
     * @param User $user
     * @param string $coin Coin Name
     * @param string $address
     * @param float $amount
     *
     * @return boolean
     */
    public function withdraw(User $user, $coin, $address, $amount, $tag = null)
    {
        $item = $this->markets->getCoin($coin);

        $exchange_api_id = $item->getAltWithdrawalStatus()?$item->exchange_api_id:0;
        $data = [
            'user_id' => $user->id,
            'item_id' => $item->item_id,
            'amount'  => ($amount * -1), //Multiply to negative for deduction of balance
            'type'    => 'withdrawal-request',
            'exchange_api_id' => $exchange_api_id,
            'fee'     => $item->getWithdrawalFee(),
            'tag'     => $tag,
            'remarks' => $address,
            'created' => Carbon::now()->timestamp,
            'module'  => '',
        ];

        return (bool)(new ExchangeTransaction())->create($data);
    }


    public function downloadDeposits($coinSymbol, $debug=false)
    {
        $this->debug = $debug;
        $exchangeItem = (new ExchangeItem())->newQuery()
            ->active()
            ->where('symbol', strtoupper(trim($coinSymbol)))
            ->first();

        if (!$exchangeItem) {
            if ($this->debug) {
                echo "exchangeItem $coinSymbol is false...";
            }
            return false;
        }

        $allAddresses = $this->getAllAddresses($exchangeItem);

        if ($allAddresses->count() === 0) {
            if ($this->debug) {
                echo "No address in this exchange item $coinSymbol...";
            }
            return false;
        }

        foreach ($allAddresses as $address) {
            $queueAddressDepositsDownload = (parameter("deposits.queue_deposits_download") == 1);
            if ($queueAddressDepositsDownload) { /*if($address->address == "1PPVE4xPxdmeVCM9sLbM46tsxhaA74JHFT")*/ DownloadAddressDeposits::dispatch($address, $exchangeItem);
            } else {
                $this->downloadDepositsByAddress($address, $exchangeItem, $this->debug);
            }

            DB::table("wallet_addresses_check_requests")
                ->where("request_id", $address->request_id)
                ->update(["status_id"=>time()]);
        }

        return true;
    }

    /**
     * @param ExchangeItem $exchangeItem
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getAllAddresses(ExchangeItem $exchangeItem)
    {
        return DB::table("wallet_addresses_check_requests")
            ->select($exchangeItem->addresses_table.".*", "request_id")
            ->leftJoin($exchangeItem->addresses_table, "wallet_addresses_check_requests.address_id", "=", $exchangeItem->addresses_table.".address_id")
            ->where([
                ["type","=",$exchangeItem->item_id],
                ["wallet_type","=",1],
                ["wallet_addresses_check_requests.status_id","=",0]
            ])
            ->orderBy("request_id", "asc")
            ->take(5)
            ->get();

        //return DB::table($exchangeItem->addresses_table)->get();
    }


    /**
     * @param $address
     * @param ExchangeItem $exchangeItem
     *
     * @return boolean
     * @throws CoinNotFoundException
     * @throws \Exception
     */
    public function downloadDepositsByAddress($address, ExchangeItem $exchangeItem, $debug=false)
    {
        $this->debug = $debug;
        $coin = CoinFactory::create($exchangeItem->symbol);

        if (!$coin) {
            throw new CoinNotFoundException(__('Invalid coin.'));
        }

        if (!$coin->isValid($address->address)) {
            return false;
        }

        if (!$coin->isOurs($address->address)) {
            return false;
        }

        (new CoinAddress())->downloadBlockChainDeposits($address->address_id, $this->debug, $coin); //set second param to true to turn on debugging

        return true;
    }

    public function updateBlockchainConfirmations($coinSymbol, $transaction_id = 0, $limit = 100, $debug = false)
    {
        $this->debug = $debug;
        $exchangeItem = (new ExchangeItem())->newQuery()
            ->active()
            ->where('symbol', strtoupper(trim($coinSymbol)))
            ->first();

        if (!$exchangeItem) {
            if ($this->debug) {
                echo "exchangeItem $coinSymbol is false...";
            }
            return false;
        }

        $coin = CoinFactory::create($exchangeItem->symbol);

        if (!$coin) {
            throw new CoinNotFoundException(__('Invalid coin.'));
        }

        (new CoinAddress())->updateBlockChainConfirmations($transaction_id, $limit, $this->debug, $coin);

        return true;
    }

    /**
     * @param User $user
     * @param ExchangeItem $item
     * @param $amount
     *
     * @return mixed
     */
    public function reloadFunds(User $user, ExchangeItem $item, $amount)
    {
        $time = Carbon::now()->timestamp;

        return ExchangeTransaction::create([
            'module'    => 'purchases',
            'module_id' => 0,
            'user_id'   => $user->id,
            'item_id'   => $item->item_id,
            'amount'    => $amount,
            'fee'       => 0.00000000,
            'type'      => 'deposit',
            'created'   => $time,
            'released'  => $time,
        ]);
    }


    /**
     * @param ExchangeService $exchange
     *
     * @return mixed
     */
    public function getOrderbookFromExternalExchange(ExchangeService $exchange, $limit)
    {
        return $exchange->getOrderbook($limit);
    }

    /**
     * @param array $orderBook
     * @param bool $deleteOld
     *
     * @return array|mixed
     * @throws AuthenticationException
     * @throws InvalidPairException
     * @throws NotEnoughFundsException
     */
    public function insertExternalOrderBook(array $orderBook, $deleteOld = true)
    {
        // if ($deleteOld) {
        //     ExchangeOrder::where('module_id', '=', $exchange->getExchangeApi()->id)
        //         ->where('fulfilled_amount', '=', 0)
        //         ->where('pair_id', $exchange->getExchangePairStat()->pair_id)
        //         ->delete();
        // }

        $statuses = [];

        foreach ($orderBook as $order) {
            $user = User::find($order['user_id']);

            if (!$user) {
                continue;
            }

            if ($order['amount'] <= 0) {
                continue;
            }

            $statuses[] = $this->trade($user, $order);
        }

        return $statuses;
    }

    /**
     * @param ExchangeOrder $order
     *
     * @return mixed
     */
    public function cancelOrder(ExchangeOrder $order)
    {
        if (!auth()->check() || auth()->user()->id != $order->user_id) {
            return false;
        }

        $order->completed = Carbon::now()->timestamp;

        return $order->save();
    }

    /**
     * @param string|null $symbol
     *
     * @return mixed
     */
    public function getApprovedWithdrawals($symbol = null)
    {
        $query = (new ExchangeTransaction())->newQuery()
            ->withdrawals()
            ->releasing()
            ->where('exchange_api_id', 0)
            ->where('processed', 0)
            ->oldest('approved')
            ->limit(20);

        if ($symbol) {
            $exchangeItem = (new ExchangeItem())->newQuery()
                ->where('symbol', $symbol)
                ->first();

            if ($exchangeItem) {
                $query = $query->where('item_id', $exchangeItem->item_id);
            } else {//doesn't exist
                return [];
            }
        } else {//symbol not set
            return [];
        }

        $transactions = $query->get()
            ->map(function ($item) use ($exchangeItem) {
                return [
                    'transaction_id' => $item->transaction_id,
                    'address'        => $item->remarks,
                    'token_address'  => $exchangeItem->token_address,
                    'type'  => $exchangeItem->type,
                    'amount'         => number_format(abs($item->amount + $item->fee), 8, '.', ''),
                ];
            });
        //being processed
        $query->update(['processed' => Carbon::now()->timestamp]);

        return $transactions;
    }

    /**
     * @param array $transactions
     */
    public function postWithdrawals(array $transactions)
    {
        if (count($transactions) === 0) {
            return;
        }

        foreach ($transactions as $transaction) {
            $exchangeTransaction = ExchangeTransaction::query()
                ->where('transaction_id', $transaction['transaction_id'])
                ->first();

            if ($exchangeTransaction) {
                $this->recordNotes($exchangeTransaction, json_encode($transaction['transaction_details']));

                if (is_null($transaction['transaction_details']['error']['code'])) {
                    $this->recordNotes($exchangeTransaction, 'Released');
                    $exchangeTransaction->released = now()->timestamp;
                }

                $exchangeTransaction->remarks2 = isset($transaction['transaction_details']['result']) ? $transaction['transaction_details']['result'] : '';//tx hash here
                $exchangeTransaction->save();
            }
        }
    }

    /**
     * @param ExchangeTransaction $exchangeTransaction
     */
    public function recordNotes(ExchangeTransaction $exchangeTransaction, $notes=null)
    {
        if (!$notes) {
            return;
        }
        $data = array();
        $current_logs = $exchangeTransaction->logs;
        $current_logs[] = array(
            'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
            'notes' => $notes,
            'updated_by' => 0
        );
        $data['logs'] = $current_logs;
        $exchangeTransaction->update($data);
    }
}
