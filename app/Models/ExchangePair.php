<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ExchangePair extends Model
{
    use LogsActivity;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'pair_id';

    /**
     * @var casting
     */
    protected $casts = [
        'filters' => 'array'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item1',
        'item2',
        'fee_percentage',
        'dynamic_pricing',
        'minimum_trade_total',
        'filters',
        'tolerance_level',
        'created',
        'deleted',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exchangeItemOne()
    {
        return $this->belongsTo(ExchangeItem::class, 'item1', 'item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exchangeItemTwo()
    {
        return $this->belongsTo(ExchangeItem::class, 'item2', 'item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function exchangePairStat()
    {
        return $this->hasOne(ExchangePairStat::class, 'pair_id', 'pair_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function exchangeOrders()
    {
        return $this->hasMany(ExchangeOrder::class, 'pair_id', 'pair_id');
    }

    /**
     * @return string
     */
    public function getNameAttribute($noslash = false)
    {
        return $noslash ? $this->exchangeItemOne->symbol . $this->exchangeItemTwo->symbol : $this->exchangeItemOne->symbol . '/' . $this->exchangeItemTwo->symbol;
    }

    /**
     * Change ExchangePair event description
     *
     * @param string $eventName
     *
     * @return string
     */
    public function getDescriptionForEvent($eventName)
    {
        return __CLASS__ . " model has been {$eventName}";
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('deleted', 0);
    }

    /**
     * Select pair on current market base
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeActiveBase(Builder $query)
    {
        $markets = ExchangeMarket::pluck('item_id')->toArray();

        return $query->whereIn('exchange_pairs.item2', $markets);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->deleted === 0;
    }

    /**
     * @return bool
     */
    public function hasActTokenItem()
    {
        return ($this->exchangeItemTwo->type == 4 || $this->exchangeItemOne->type == 4);
    }

    /**
     * @return bool
     */
    public function hasInactiveItem()
    {
        return ($this->exchangeItemTwo->deleted > 0 || $this->exchangeItemOne->deleted > 0);
    }

    /**
     * @return bool
     */
    public function isBaseActive()
    {
        $base = getBases();
        return in_array($this->exchangeItemTwo->symbol, $base);
    }
    /**
     * @param bool $include_act
     *
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getPairs($include_act = false)
    {
        if ($include_act) {
            return static::newQuery()->get();
        }

        return (new static())->newQuery()
            ->where('deleted', 0)
            ->with(['exchangeItemOne', 'exchangeItemTwo', 'exchangePairStat'])
            ->get()
            ->reject(function ($exchangePair) {
                return $exchangePair->exchangeItemOne->type === 4
                    || $exchangePair->exchangeItemTwo->type === 4;
            });
    }
    /**
     *
     * @return array|boolean
     */
    public function getFilters($source = 'binance')
    {
        $limits = $this->filters;

        if (!(is_null($limits) || $limits == 'null') && array_key_exists(strtolower($source), $limits)) {
            return $limits[strtolower($source)];
        } else {
            $default = [
                "local" => [
                    "LOT_SIZE" => [
                        "maxQty" => currency(parameter('default.max_amount', 999999999)),
                        "minQty" => currency(parameter('default.min_amount', 0.0001)),
                        "stepSize" => parameter('default.step_size', 0),
                        "filterType" => "LOT_SIZE"
                    ],

                    "MIN_NOTIONAL" => [
                        "filterType" => "MIN_NOTIONAL",
                        "minNotional" => currency(parameter('default.min_cost', 0.001)),
                        "avgPriceMins" => 5,
                        "applyToMarket" => true
                    ],

                    "PRICE_FILTER" => [
                        "maxPrice" => currency(parameter('default.max_price', 999999999)),
                        "minPrice" => currency(parameter('default.min_price', 0.00000010)),
                        "tickSize" => parameter('default.tick_size', 0),
                        "filterType" => "PRICE_FILTER"
                    ],

                    "PERCENT_PRICE" => [
                        "filterType" => "PERCENT_PRICE",
                        "multiplierUp" => "10",
                        "multiplierDown" => "0.1",
                        "avgPriceMins" => 5
                    ],

                    "ICEBERG_PARTS" => [
                        "filterType" => "ICEBERG_PARTS",
                        "limit" => 10
                    ],

                    "MAX_NUM_ALGO_ORDERS" => [
                        "filterType" => "MAX_NUM_ALGO_ORDERS",
                        "maxNumAlgoOrders" => 5
                    ]
                ]
            ];
            $existing_filters = (is_null($limits) || $limits == 'null') ? [] : $limits;
            //add the source default
            $this->filters  = array_merge($existing_filters, $default);
            $this->save();
            return $default['local'];
        }
    }

    /**
     * Get the minimum cost per trade
     * @return array|boolean
     */
    public function getMinCost($source = 'binance')
    {
        $filter = $this->getFilters($source);

        if (is_array($filter) && array_key_exists('MIN_NOTIONAL', $filter) && array_key_exists('minNotional', $filter['MIN_NOTIONAL'])) {
            return $filter['MIN_NOTIONAL']['minNotional'] < 0 ? 0 : $filter['MIN_NOTIONAL']['minNotional'];
        }
        return  parameter('default.min_cost', 0.0001);
    }
    /**
     * Get the minimum amount per trade
     * @return array|boolean
     */
    public function getMinAmount($source = 'binance')
    {
        $filter = $this->getFilters($source);
        
        if (is_array($filter) && array_key_exists('LOT_SIZE', $filter) && array_key_exists('minQty', $filter['LOT_SIZE'])) {
            return $filter['LOT_SIZE']['minQty'] <= 0 ? 0 : $filter['LOT_SIZE']['minQty'];
        }
        
        return  parameter('default.min_amount', 0.00000001);
    }
    /**
     * Get the minimum price per trade
     * @return array|boolean
     */
    public function getMinPrice($source = 'binance')
    {
        $filter = $this->getFilters($source);
        
        if (is_array($filter) && array_key_exists('PRICE_FILTER', $filter) && array_key_exists('minPrice', $filter['PRICE_FILTER'])) {
            return $filter['PRICE_FILTER']['minPrice'] <= 0 ? 0 : $filter['PRICE_FILTER']['minPrice'];
        }

        return parameter('default.min_price', 0.00000001);
    }

    /**
     * Get the maximum amount per trade
     * @return array|boolean
     */
    public function getMaxAmount($source = 'binance')
    {
        $filter = $this->getFilters($source);

        if (is_array($filter) && array_key_exists('LOT_SIZE', $filter) && array_key_exists('maxQty', $filter['LOT_SIZE'])) {
            return $filter['LOT_SIZE']['maxQty'] <= 0 ? parameter('default.max_amount', 999999999) : $filter['LOT_SIZE']['maxQty'];
        }

        return parameter('default.max_amount', 999999999);
    }
    /**
     * Get the maximum price per trade
     * @return array|boolean
     */
    public function getMaxPrice($source = 'binance')
    {
        $filter = $this->getFilters($source);

        if (is_array($filter) && array_key_exists('PRICE_FILTER', $filter) && array_key_exists('maxPrice', $filter['PRICE_FILTER'])) {
            return $filter['PRICE_FILTER']['maxPrice'] <= 0 ? parameter('default.max_price', 999999999) : $filter['PRICE_FILTER']['maxPrice'];
        }

        return  parameter('default.max_price', 999999999);
    }
    /**
     * Get the price tick size
     * @return array|boolean
     */
    public function getTickSize($source = 'binance')
    {
        $filter = $this->getFilters($source);

        if (is_array($filter) && array_key_exists('PRICE_FILTER', $filter) && array_key_exists('tickSize', $filter['PRICE_FILTER'])) {
            return $filter['PRICE_FILTER']['tickSize'];
        }

        return  parameter('default.tick_size', 0);
    }

    /**
     * Get the price tick size
     * @return array|boolean
     */
    public function getStepSize($source = 'binance')
    {
        $filter = $this->getFilters($source);

        if (is_array($filter) && array_key_exists('LOT_SIZE', $filter) && array_key_exists('stepSize', $filter['LOT_SIZE'])) {
            return $filter['LOT_SIZE']['stepSize'];
        }

        return parameter('default.step_size', 0);
    }
}
