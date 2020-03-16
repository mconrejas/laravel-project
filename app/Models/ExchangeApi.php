<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeApi extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exchange_apis';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'trade_url',
        'orderbook_url',
        'server_time_url',
        'test_url',
        'base_url',
        'balance_filter',
        'profit_margin'
    ];
}
