<?php

namespace Buzzex\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ExchangeOrder extends Model
{
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
    protected $primaryKey = 'order_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'module',
        'module_id',
        'user_id',
        'amount',
        'fee',
        'type',
        'price',
        'stop_price',
        'limit_price',
        'stop_limit_execution_time',
        'pair_id',
        'form_type',
        'target_amount',
        'fulfilled_amount',
        'fulfilled_target_amount',
        'fulfilled_amount_reg',
        'fulfilled_target_amount_reg',
        'ip_address',
        'margin',
        'completed',
        'created',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pair()
    {
        return $this->belongsTo(ExchangePair::class, 'pair_id', 'pair_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pairStat()
    {
        return $this->hasOne(ExchangePairStat::class, 'pair_id', 'pair_id');
    }

    /**
     * @return bool
     */
    public function isStopLimit()
    {
        return $this->stop_price !== null && $this->limit_price !== null;
    }

    /**
     * @return bool
     */
    public function isFulfilled()
    {
        return ($this->completed > 0 && $this->fulfilled_amount == $this->amount);
    }

    /**
     * @return bool
     */
    public function isPartiallyFulfilled()
    {
        return ($this->completed == 0 && $this->fulfilled_amount > 0 && $this->fulfilled_amount < $this->amount);
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return ($this->completed > 0 && $this->fulfilled_amount < $this->amount);
    }

    /**
     * @return integer
     */
    public function getFulfilledPercentage()
    {
        return (($this->fulfilled_amount / $this->amount) * 100);
    }

    /**
     * @return bool
     */
    public function cancelOrder()
    {
        if ($this->fulfilled_amount == 0) {
            $this->completed = Carbon::now()->timestamp;
            return $this->save();
        }

        return false;
    }
}
