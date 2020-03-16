<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeReward extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exchange_rewards';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'reward_id';
    
    /**
     * @var casting
     */
    protected $casts = [
        'raw_data' => 'array'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'amount',
        'user_id',
        'item_id',
        'raw_data'
    ];
}
