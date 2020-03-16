<?php

namespace Buzzex\Services;

use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Models\ExchangeFulfillment;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\ExchangePair;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketService extends BaseService implements Marketable
{
    /**
     * MarketService constructor.
     *
     * @param ExchangePair $model
     */
    public function __construct(ExchangePair $model)
    {
        parent::__construct($model);
    }

    /**
     * @param string $coinSymbol
     * @param int $limit
     * @param int $offset
     *
     * @return array|bool
     */
    public function getMarketFor($coinSymbol, $limit = 100, $offset = 0)
    {
        if (!$this->isValidCoin($coinSymbol)) {
            return false;
        }

        $market = $this->model->newQuery()
            ->join('exchange_pairs_stats', 'exchange_pairs.pair_id', '=', 'exchange_pairs_stats.pair_id')
            ->join('exchange_items', 'exchange_pairs.item2', '=', 'exchange_items.item_id')
            ->with(['exchangeItemOne', 'exchangeItemTwo', 'exchangePairStat'])
            ->where('exchange_pairs.deleted', '=', 0)
            ->where(function ($where) use ($coinSymbol) {
                if ($coinSymbol !== 'selected') {
                    $where->where('exchange_items.symbol', strtoupper(trim($coinSymbol)));
                }
            })
            ->orderBy('exchange_pairs_stats.base_volume', 'desc');

        if ($coinSymbol !== 'selected' && $limit !== null) {
            $market = $market->offset($offset)
                ->limit($limit);
        }

        $market = $market->get();
        $data = [];

        foreach ($market as $entry) {
            if ($entry->hasActTokenItem() || $entry->hasInactiveItem()) {
                continue;
            }
            if ($entry->exchangePairStat->pair_text != ($entry->exchangeItemTwo->symbol.'_'.$entry->exchangeItemOne->symbol)) {
                continue;
            }

            $data[] = [
                'base' => $entry->exchangeItemTwo->symbol,
                'pair_id' => $entry->pair_id,
                'coin' => $entry->exchangeItemOne->symbol,
                'price' => currency($entry->exchangePairStat->last),
                'price_usd' => currency($entry->exchangePairStat->last * $entry->exchangeItemTwo->index_price_usd, 4),
                'price_in_usd' => currency(($entry->index_price_usd * $entry->exchangePairStat->last), 2),
                'h24_value' => currency($entry->exchangePairStat->base_volume),
                'h24_high' => currency($entry->exchangePairStat->high_24hr),
                'h24_low' => currency($entry->exchangePairStat->low_24hr),
                'h24_volume' => currency($entry->exchangePairStat->quote_volume),
                'h24_change' => number_format($entry->exchangePairStat->percent_change, 2, '.', '.') . '%',
                'pair_text' => $entry->exchangePairStat->pair_text,
                'active' => (request()->has('target') && request()->target == $entry->exchangeItemOne->symbol ? 1 : 0),
            ];
        }

        return $data;
    }

    /**
     * @param $coinSymbol
     *
     * @return bool
     */
    public function isValidCoin($coinSymbol)
    {
        if ($coinSymbol == 'selected') {
            return true;
        }

        return (new ExchangeItem())->newQuery()
            ->where('symbol', strtoupper(trim($coinSymbol)))
            ->where('deleted', 0)
            ->exists();
    }

    /**
     * @param $pairText
     *
     * @return array|bool
     */
    public function getPairInfoByPairText($pairText)
    {
        $pair = $this->model->newQuery()
            ->join('exchange_pairs_stats', 'exchange_pairs.pair_id', '=', 'exchange_pairs_stats.pair_id')
            ->with(['exchangeItemOne', 'exchangeItemTwo', 'exchangePairStat'])
            ->where('exchange_pairs_stats.pair_text', strtoupper(trim($pairText)))
            ->first();

        if (!$pair) {
            return false;
        }

        return [
            'base' => $pair->exchangeItemTwo->symbol,
            'coin' => $pair->exchangeItemOne->symbol,
            'pair_id' => $pair->pair_id,
            'price' => currency($pair->exchangePairStat->last),
            'price_usd' => currency($pair->exchangePairStat->last * $pair->exchangeItemTwo->index_price_usd, 4),
            'h24_value' => currency($pair->exchangePairStat->base_volume),
            'h24_high' => currency($pair->exchangePairStat->high_24hr),
            'h24_low' => currency($pair->exchangePairStat->low_24hr),
            'h24_volume' => currency($pair->exchangePairStat->quote_volume),
            'h24_change' => number_format($pair->exchangePairStat->percent_change, 2, '.', '.') . '%',
            'pair_text' => $pair->exchangePairStat->pair_text,
            'fee_percentage' => $pair->fee_percentage >= 0 ? $pair->fee_percentage : parameter('exchange.trade_fee', 0),
            'minimum_trade' => $pair->minimum_trade_total,
            'ask_price' => currency($pair->exchangePairStat->lowest_ask),
            'bid_price' => currency($pair->exchangePairStat->highest_bid),
            'pairObject' => $pair
        ];
    }

    /**
     * @param $pairId
     *
     * @return array|bool
     */
    public function getPairInfoByPairId($pairId)
    {
        $pair = $this->model->newQuery()
            ->join('exchange_pairs_stats', 'exchange_pairs.pair_id', '=', 'exchange_pairs_stats.pair_id')
            ->with(['exchangeItemOne', 'exchangeItemTwo', 'exchangePairStat'])
            ->where('exchange_pairs_stats.pair_id', $pairId)
            ->first();

        if (!$pair) {
            return false;
        }

        $order = (new ExchangeOrder())->newQuery()
                    ->select('type')
                    ->where('pair_id', $pair->pair_id)
                    ->orderBy('order_id', 'desc')
                    ->first();

        return [
            'base' => $pair->exchangeItemTwo->symbol,
            'coin' => $pair->exchangeItemOne->symbol,
            'pair_id' => $pair->pair_id,
            'price' => currency($pair->exchangePairStat->last),
            'price_usd' => currency($pair->exchangePairStat->last * $pair->exchangeItemTwo->index_price_usd, 4),
            'h24_value' => currency($pair->exchangePairStat->base_volume),
            'h24_high' => currency($pair->exchangePairStat->high_24hr),
            'h24_low' => currency($pair->exchangePairStat->low_24hr),
            'h24_volume' => currency($pair->exchangePairStat->quote_volume),
            'h24_change' => number_format($pair->exchangePairStat->percent_change, 2, '.', '.') . '%',
            'pair_text' => $pair->exchangePairStat->pair_text,
            'ask_price' => currency($pair->exchangePairStat->lowest_ask),
            'bid_price' => currency($pair->exchangePairStat->highest_bid),
            'fee_percentage' => $pair->fee_percentage >= 0 ? $pair->fee_percentage : parameter('exchange.trade_fee', 0),
            'pairObject' => $pair,
            'type' => $order ? $order->type : 'BUY',
        ];
    }

    /**
     * @param $type
     *
     * @return bool
     */
    protected function isValidOrderType($type)
    {
        return in_array(strtolower($type), ['all', 'ask', 'bid', 'sell', 'buy']);
    }

    /**
     * @param $pairText
     * @param int $limit
     * @param string $type Values can be sell|ask or bid|buy. Default is 'all'
     * @param string $orderDirection
     *
     * @return mixed
     */
    public function getDepthByPairText($pairText, $limit = 1, $type = 'all', $orderDirection = 'desc')
    {
        if (!$this->isValidOrderType($type)) {
            return false;
        }

        $query = (new ExchangeOrder())->newQuery()
            ->selectRaw('exchange_orders.*, sum(amount) as total_amount, exchange_pairs_stats.lowest_ask, exchange_pairs_stats.highest_bid')
            ->join('exchange_pairs_stats', 'exchange_orders.pair_id', '=', 'exchange_pairs_stats.pair_id')
            ->with(['pair', 'pair.exchangeItemOne', 'pair.exchangeItemTwo', 'pair.exchangePairStat'])
            ->where('exchange_pairs_stats.pair_text', strtoupper(trim($pairText)))
            ->groupBy('exchange_orders.price')
            ->orderBy('exchange_orders.price', $orderDirection);

        $order = null;

        if ($type !== 'all') {
            if ($type === 'ask') {
                $type = 'sell';
            }

            if ($type === 'bid') {
                $type = 'buy';
            }

            $query = $query->where('exchange_orders.type', strtoupper($type));
        }

        if ($limit === 1) {
            $order = $query->first();
        }

        if ($limit > 1) {
            $order = $query->limit($limit)
                ->get();
        }

        if (!$order) {
            return false;
        }
        $global_fee = parameter('exchange.trade_fee', 0);
        $filters = $order->pair->filters;
        $filters = array_shift($filters);

        if ($limit > 1) {
            return $order->mapWithKeys(function ($order, $key) use ($global_fee) {
                return [
                    $key => [
                        'base' => $order->pair->exchangeItemTwo->symbol,
                        'coin' => $order->pair->exchangeItemOne->symbol,
                        'pair_id' => $order->pair->pair_id,
                        'amount' => currency($order->amount),
                        'total_amount' => currency($order->total_amount),
                        'price' => currency($order->price),
                        'ask_price' => currency($order->lowest_ask),
                        'bid_price' => currency($order->highest_bid),
                        'price_usd' => currency($order->price * $order->pair->exchangeItemTwo->index_price_usd, 4),
                        'h24_value' => currency($order->pair->exchangePairStat->base_volume),
                        'h24_high' => currency($order->pair->exchangePairStat->high_24hr),
                        'h24_low' => currency($order->pair->exchangePairStat->low_24hr),
                        'h24_volume' => currency($order->pair->exchangePairStat->quote_volume),
                        'h24_change' => number_format($order->pair->exchangePairStat->percent_change, 2, '.', '.') . '%',
                        'pair_text' => $order->pair->exchangePairStat->pair_text,
                        'depth_type' => $order->type,
                        'fee_percentage' => $order->pair->fee_percentage >= 0 ? $order->pair->fee_percentage : $global_fee
                    ],
                ];
            })
                ->toArray();
        }

        return [
            'base' => $order->pair->exchangeItemTwo->symbol,
            'coin' => $order->pair->exchangeItemOne->symbol,
            'pair_id' => $order->pair->pair_id,
            'amount' => currency($order->total_amount),
            'total_amount' => currency($order->total_amount),
            'price' => currency($order->price),
            'ask_price' => currency($order->lowest_ask),
            'bid_price' => currency($order->highest_bid),
            'price_usd' => currency($order->price * $order->pair->exchangeItemTwo->index_price_usd, 4),
            'h24_value' => currency($order->pair->exchangePairStat->base_volume),
            'h24_high' => currency($order->pair->exchangePairStat->high_24hr),
            'h24_low' => currency($order->pair->exchangePairStat->low_24hr),
            'h24_volume' => currency($order->pair->exchangePairStat->quote_volume),
            'h24_change' => number_format($order->pair->exchangePairStat->percent_change, 2, '.', '.') . '%',
            'pair_text' => $order->pair->exchangePairStat->pair_text,
            'depth_type' => $order->type,
            'fee_percentage' => $order->pair->fee_percentage >= 0 ? $order->pair->fee_percentage : $global_fee
        ];
    }

    /**
     * @param $pairId
     * @param int $limit
     * @param string $type Values can be sell|ask or bid|buy. Default is 'all'
     * @param string $orderDirection
     *
     * @return array|bool
     */
    public function getDepthByPairId($pairId, $decimal = 8, $limit = 1, $type = 'all', $orderDirection = 'desc')
    {
        if (!$this->isValidOrderType($type)) {
            return false;
        }


        $query = (new ExchangeOrder())->newQuery()
            ->selectRaw('exchange_orders.*, sum(amount) as total_amount, price')
            ->with(['pair', 'pair.exchangeItemOne', 'pair.exchangeItemTwo', 'pair.exchangePairStat'])
            ->where('exchange_orders.pair_id', $pairId)
            ->groupBy('exchange_orders.price')
            ->orderBy('exchange_orders.price', $orderDirection);

        $order = null;

        if ($type !== 'all') {
            if ($type === 'ask') {
                $type = 'sell';
            }

            if ($type === 'bid') {
                $type = 'buy';
            }

            $query = $query->where('exchange_orders.type', strtoupper($type));
        }

        if ($limit === 1) {
            $order = $query->first();
        }

        if ($limit > 1) {
            $order = $query->limit($limit)
                ->get();
        }

        if (!$order) {
            return false;
        }

        if ($limit > 1) {
            return $order->mapWithKeys(function ($order, $key) use ($decimal) {
                return [
                    $key => [
                        'base' => $order->pair->exchangeItemTwo->symbol,
                        'coin' => $order->pair->exchangeItemOne->symbol,
                        'pair_id' => $order->pair->pair_id,
                        'amount' => currency($order->amount, $decimal),
                        'total_amount' => currency($order->total_amount, $decimal),
                        'price' => currency($order->price),
                        'price_usd' => currency($order->price * $order->pair->exchangeItemTwo->index_price_usd, 4),
                        'h24_value' => currency($order->pair->exchangePairStat->base_volume),
                        'h24_high' => currency($order->pair->exchangePairStat->high_24hr),
                        'h24_low' => currency($order->pair->exchangePairStat->low_24hr),
                        'h24_volume' => currency($order->pair->exchangePairStat->quote_volume),
                        'h24_change' => number_format($order->pair->exchangePairStat->percent_change, 2, '.', '.') . '%',
                        'pair_text' => $order->pair->exchangePairStat->pair_text,
                        'depth_type' => $order->type,
                        'ask_price' => currency($order->pair->exchangePairStat->lowest_ask),
                        'bid_price' => currency($order->pair->exchangePairStat->highest_bid),
                    ],
                ];
            })
            ->toArray();
        }

        return [
            'base' => $order->pair->exchangeItemTwo->symbol,
            'coin' => $order->pair->exchangeItemOne->symbol,
            'pair_id' => $order->pair->pair_id,
            'amount' => currency($order->amount, $decimal),
            'total_amount' => currency($order->total_amount, $decimal),
            'price' => currency($order->price),
            'price_usd' => currency($order->price * $order->pair->exchangeItemTwo->index_price_usd, 4),
            'h24_value' => currency($order->pair->exchangePairStat->base_volume),
            'h24_high' => currency($order->pair->exchangePairStat->high_24hr),
            'h24_low' => currency($order->pair->exchangePairStat->low_24hr),
            'h24_volume' => currency($order->pair->exchangePairStat->quote_volume),
            'h24_change' => number_format($order->pair->exchangePairStat->percent_change, 2, '.', '.') . '%',
            'pair_text' => $order->pair->exchangePairStat->pair_text,
            'depth_type' => $order->type,
            'ask_price' => currency($order->pair->exchangePairStat->lowest_ask),
            'bid_price' => currency($order->pair->exchangePairStat->highest_bid),
        ];
    }

    /**
     * @param $pairId
     * @param int $limit
     * @param string $type Values can be sell|ask or bid|buy. Default is 'all'
     * @param string $orderDirection
     * @param string $module
     *
     * @return array|bool
     */
    public function getOrderBook($pairId, $decimal = 8, $limit = 1, $type = 'all', $orderDirection = 'desc', $module = '')
    {
        if (!$this->isValidOrderType($type)) {
            return false;
        }

        $module = trim($module);

        $query = (new ExchangeOrder())->newQuery()
            ->selectRaw('exchange_orders.*, sum(amount - fulfilled_amount) as total_amount')
            ->with(['pair', 'pair.exchangeItemOne', 'pair.exchangeItemTwo', 'pair.exchangePairStat'])
            ->where('exchange_orders.pair_id', '=', $pairId)
            ->where('exchange_orders.completed', '=', 0)
            ->groupBy('exchange_orders.price')
            ->orderBy('exchange_orders.price', $orderDirection);

        $order = null;

        if ($type !== 'all') {
            if ($type === 'ask') {
                $type = 'sell';
            }

            if ($type === 'bid') {
                $type = 'buy';
            }

            $query = $query->where('exchange_orders.type', strtoupper($type));
        }

        if (strlen($module) > 0) {
            $query = $query->where('module', $module);
        }

        if ($limit === 1) {
            $order = $query->first();
        }

        if ($limit > 1) {
            $order = $query->limit($limit)
                ->get();
        }



        if (!$order) {
            return false;
        }

        if ($limit > 1) {
            return $order->mapWithKeys(function ($order, $key) use ($decimal) {
                return [
                    $key => [
                        'order_id' => $order->order_id,
                        'base' => $order->pair->exchangeItemTwo->symbol,
                        'coin' => $order->pair->exchangeItemOne->symbol,
                        'pair_id' => $order->pair->pair_id,
                        'amount' => currency($order->amount, $decimal),
                        'total_amount' => currency($order->total_amount, $decimal),
                        'price' => currency($order->price, $decimal),
                        'full_total_amount' => $order->total_amount,
                        'full_price' => $order->price,
                        'orig_price' => $order->price,
                        'price_usd' => currency($order->price * $order->pair->exchangeItemTwo->index_price_usd, 4),
                        'h24_value' => currency($order->pair->exchangePairStat->base_volume),
                        'h24_high' => currency($order->pair->exchangePairStat->high_24hr),
                        'h24_low' => currency($order->pair->exchangePairStat->low_24hr),
                        'h24_volume' => currency($order->pair->exchangePairStat->quote_volume),
                        'h24_change' => number_format($order->pair->exchangePairStat->percent_change, 2, '.', '.').'%',
                        'pair_text' => $order->pair->exchangePairStat->pair_text,
                        'depth_type' => $order->type,
                        'module' => "",
                    ],
                ];
            })
            ->toArray();
        }

        return [
            'order_id' => $order->order_id,
            'base' => $order->pair->exchangeItemTwo->symbol,
            'coin' => $order->pair->exchangeItemOne->symbol,
            'pair_id' => $order->pair->pair_id,
            'amount' => currency($order->amount, $decimal),
            'total_amount' => currency($order->total_amount, $decimal),
            'price' => currency($order->price, $decimal),
            'full_total_amount' => $order->total_amount,
            'full_price' => $order->price,
            'orig_price' => $order->price,
            'price_usd' => currency($order->price * $order->pair->exchangeItemTwo->index_price_usd, 4),
            'h24_value' => currency($order->pair->exchangePairStat->base_volume),
            'h24_high' => currency($order->pair->exchangePairStat->high_24hr),
            'h24_low' => currency($order->pair->exchangePairStat->low_24hr),
            'h24_volume' => currency($order->pair->exchangePairStat->quote_volume),
            'h24_change' => number_format($order->pair->exchangePairStat->percent_change, 2, '.', '.') . '%',
            'pair_text' => $order->pair->exchangePairStat->pair_text,
            'depth_type' => $order->type,
            'module' => "",
        ];
    }

    /**
     * @param $pairId
     * @param $user
     * @param int $limit
     * @param int $offset
     * @param array $filters
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getLatestExecution($pairId = 0, $user, $limit = 100, $offset = 0, $filters = [])
    {
        if ($user instanceof User) {
            return $this->getLatestExecutionBy($pairId, $user, $limit, $offset, $filters);
        }

        $query = (new ExchangeFulfillment())->newQuery()
            ->select(DB::raw('exchange_fulfillments.*, exchange_orders.user_id, exchange_orders.pair_id, exchange_orders.type'))
            ->leftJoin('exchange_orders', 'exchange_orders.order_id', '=', 'exchange_fulfillments.sell_order_id')
            ->where('exchange_orders.pair_id', $pairId);

        return $query->orderBy('exchange_fulfillments.fulfillment_id', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->mapWithKeys(function ($order, $key) {
                return [
                    $key => [
                        'id' => $order->fulfillment_id,
                        'time' => Carbon::createFromTimestamp($order->created)->format('H:i:s'),
                        'price' => currency($order->price),
                        'amount' => currency($order->amount),
                        'type' => $order->sell_order_id > $order->buy_order_id ? "sell" : 'buy'
                    ],
                ];
            });
    }

    /**
     * @param $pairId
     * @param User $user
     * @param int $limit
     * @param int $offset
     * @param array $filters
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    protected function getLatestExecutionBy($pairId = 0, User $user, $limit = 100, $offset = 0, $filters = [])
    {
        $response = [];

        $query = (new ExchangeFulfillment())
            ->selectRaw('exchange_fulfillments.*,e1.user_id as e1_user_id, e1.type as e1_type, e2.user_id as e2_user_id, e2.type as e2_type , exchange_pairs_stats.pair_text')
            ->leftJoin(DB::raw('exchange_orders as e1'), DB::raw('e1.order_id'), '=', DB::raw('exchange_fulfillments.sell_order_id'))
            ->leftJoin(DB::raw('exchange_orders as e2'), DB::raw('e2.order_id'), '=', DB::raw('exchange_fulfillments.buy_order_id'))
            ->leftJoin('exchange_pairs_stats', 'exchange_pairs_stats.pair_id', '=', DB::raw('e1.pair_id'))
            ->where(function ($query) use ($user) {
                $query->where(DB::raw('e1.user_id'), '=', $user->id)
                ->orWhere(DB::raw('e2.user_id'), '=', $user->id);
            });

        if ($pairId > 0) {
            $query = $query->where('e1.pair_id', '=', $pairId);
        }

        if (array_key_exists('side', $filters)) {
            //this means you're in /exchange/orders/latest-execution

            if ($filters['side'] != 'all') {
                $comparator = ($filters['side'] == 'buy' ? '<' : '>');
                $query = $query->whereRaw("exchange_fulfillments.sell_order_id $comparator exchange_fulfillments.buy_order_id");
            }

            if (isset($filters['from']) && $filters['from'] != "") {
                $query = $query->where('exchange_fulfillments.created', '>', strtotime($filters['from'] . " 00:00:00"));
            }

            if (isset($filters['to']) && $filters['to'] != "") {
                $query = $query->where('exchange_fulfillments.created', '<', strtotime($filters['to'] . " 24:59:59"));
            }
            $response['last_page'] = ceil($query->count() / $filters['size']);

            $query = $query->orderBy('exchange_fulfillments.fulfillment_id', $filters['order'] ?? 'desc')
                  ->skip($filters['size'] * ($filters['page'] - 1))
                  ->take($filters['size']);
        } else {
            $query = $query->orderBy('exchange_fulfillments.fulfillment_id', 'desc')
                ->limit($limit)
                ->offset($offset);
        }

        $response['data'] = $query->get()
            ->mapWithKeys(function ($order, $key) {
                return [
                    $key => [
                        'id' => $order->fulfillment_id,
                        'date' => Carbon::createFromTimestamp($order->created)->format('Y-m-d H:i:s'),
                        'time' => Carbon::createFromTimestamp($order->created)->format('H:i:s'),
                        'price' => currency($order->price),
                        'pair_name' => str_replace('_', '/', $order->pair_text),
                        'side' => $order->sell_order_id > $order->buy_order_id ? "sell" : 'buy',
                        'type' => $order->sell_order_id > $order->buy_order_id ? "sell" : 'buy',
                        'amount' => currency($order->amount),
                        'fees' => currency($order->fee)
                    ],
                ];
            });

        if (isset($filters['side'])) {
            //this means you're in /exchange/orders/latest-execution
            return $response;
        } else {
            return $response['data'];
        }
    }

    /**
     * @param string $term
     *
     * @return array
     */
    public function searchPair($term)
    {
        $term = strtoupper(trim(str_replace("/", "_", $term)));
        $data = array();

        if (strlen($term) === 0) {
            return [];
        }

        $items = (new ExchangePair())->newQuery()
                ->join('exchange_pairs_stats', function ($join) use ($term) {
                    $join->on('exchange_pairs.pair_id', '=', 'exchange_pairs_stats.pair_id')
                        ->where('exchange_pairs_stats.pair_text', 'LIKE', "%$term%");
                })
                ->join('exchange_items as i1', function ($join) {
                    $join->on(DB::raw('i1.item_id'), '=', 'exchange_pairs.item1')
                        ->where(DB::raw('i1.type'), '<>', 4)
                        ->where(DB::raw('i1.deleted'), '=', 0);
                })
                ->join('exchange_items as i2', function ($join) {
                    $join->on(DB::raw('i2.item_id'), '=', 'exchange_pairs.item2')
                        ->where(DB::raw('i2.type'), '<>', 4)
                        ->where(DB::raw('i2.deleted'), '=', 0);
                })
                ->where('exchange_pairs.deleted', '=', 0)
                ->limit(30)
                ->get();

        if ($items) {
            $fave_pairs = Auth::check() ? Auth::user()->fave_pairs : [];
            foreach ($items as $key => $item) {
                if (!$item->isBaseActive()) {
                    continue;
                }

                $data[$key] = [
                    'label' => $item->exchangeItemOne->symbol."/".$item->exchangeItemTwo->symbol,
                    'value' => $item->pair_id,
                    'starred' => in_array($item->pair_id, $fave_pairs) ? 1 : 0,
                    'url' => route('exchange', [
                        'base' => $item->exchangeItemTwo->symbol,
                        'target' => $item->exchangeItemOne->symbol,
                    ]),
                ];
            }
        }
        return response()->json($data, 200);
    }

    /**
     * @param $term
     *
     * @return array
     */
    public function searchCoin($term)
    {
        if (strlen($term) === 0) {
            return [];
        }

        $term = ($term == 'all') ? '' : trim($term);

        // initialize
        $data[] = [
            'label' => "All", // Bitcoin
            'value' => 'all', // BTC
            'icon' => parameter('icon.default', asset('img/58x58.png')),
        ];
        $coins = ExchangeItem::where('deleted', 0)
            ->where('symbol', 'like', '%' . $term . '%')
            ->orderBy('name')
            ->get();

        if ($coins) {
            foreach ($coins as $coin) {
                $data[] = [
                    'label' => $coin->name, // Bitcoin
                    'value' => $coin->symbol, // BTC
                    'icon' => $coin->iconUrl,
                ];
            }
        }

        return $data;
    }

    /**
     * Get coin
     *
     * @param string $name
     * @return ExchangeItem
     */
    public function getCoin($name)
    {
        return ExchangeItem::where('symbol', trim(strtoupper($name)))
                ->where('deleted', 0)
                ->first();
    }

    /**
     * @param ExchangePair $pair
     *
     * @return mixed
     */
    public function updateExchangePairStats(ExchangePair $pair)
    {
        $baseVolume = 0;
        $quoteVolume = 0;
        $low24hr = 0;
        $high24hr = 0;
        $price24hr = 0;

        if (!$pair->exchangePairStat) {
            return false;
        }

        $where = function ($query) use ($pair) {
            $query->where('exchange_orders.pair_id', $pair->pair_id)
                ->whereRaw('exchange_fulfillments.created > (UNIX_TIMESTAMP()-86400) ');
        };

        $infoVolumes = (new ExchangeOrder())->newQuery()
            ->selectRaw('sum(exchange_fulfillments.amount) as quote_volume, sum(exchange_fulfillments.amount * exchange_fulfillments.price) as base_volume')
            ->leftJoin('exchange_fulfillments', 'exchange_fulfillments.sell_order_id', '=', 'exchange_orders.order_id')
            ->where($where)
            ->first();

        if ($infoVolumes) {
            $baseVolume = $infoVolumes->base_volume;
            $quoteVolume = $infoVolumes->quote_volume;
        }

        $infoHigh = (new ExchangeOrder())->newQuery()
            ->selectRaw('exchange_fulfillments.price')
            ->leftJoin('exchange_fulfillments', 'exchange_fulfillments.sell_order_id', '=', 'exchange_orders.order_id')
            ->where($where)
            ->orderBy('exchange_fulfillments.price', 'desc')
            ->first();

        if ($infoHigh) {
            $high24hr = $infoHigh->price;
        }

        $infoLow = (new ExchangeOrder())->newQuery()
            ->selectRaw('exchange_fulfillments.price')
            ->leftJoin('exchange_fulfillments', 'exchange_fulfillments.sell_order_id', '=', 'exchange_orders.order_id')
            ->where($where)
            ->orderBy('exchange_fulfillments.price', 'asc')
            ->first();

        if ($infoLow) {
            $low24hr = $infoLow->price;
        }

        $infoPrice = (new ExchangeOrder())->newQuery()
            ->selectRaw('exchange_fulfillments.price')
            ->leftJoin('exchange_fulfillments', 'exchange_fulfillments.sell_order_id', '=', 'exchange_orders.order_id')
            ->where($where)
            ->orderBy('exchange_fulfillments.created', 'asc')
            ->first();

        if ($infoPrice) {
            $price24hr = $infoPrice->price;
        }

        return $pair->exchangePairStat->update([
            'quote_volume' => $quoteVolume,
            'base_volume' => $baseVolume,
            'high_24hr' => $high24hr,
            'low_24hr' => $low24hr,
            'price_24h' => $price24hr,
            'updated' => Carbon::now()->timestamp,
        ]);
    }

    /**
     * Get highest and lowest pair price
     * @param Int $pair
     * @return Array
     */
    public function getHighestLowestPrice($pair_id)
    {
        if (!$pair_id) {
            return ['highest_bid' => 0, 'lowest_ask' => 0];
        }

        $pairStat = ExchangePairStat::where('pair_id', $pair_id)->first();

        return (object) ['highest_bid' => $pairStat->highest_bid, 'lowest_ask' => $pairStat->lowest_ask];
    }
}
