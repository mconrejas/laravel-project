<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeFulfillment extends Model
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
    protected $primaryKey = 'fulfillment_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sell_order_id',
        'buy_order_id',
        'amount',
        'fee',
        'type',
        'price',
        'created',
    ];
}
