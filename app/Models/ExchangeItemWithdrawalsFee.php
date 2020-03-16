<?php

namespace Buzzex\Models;

use Buzzex\Models\ExchangeApi;
use Buzzex\Models\ExchangeItem;
use Illuminate\Database\Eloquent\Model;

class ExchangeItemWithdrawalsFee extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exchange_items_api_withdrawal_fees';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'fee_id';

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
        'exchange_api_id',
        'item_id',
        'fee',
        'minimum_amount',
        'raw_data'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function item()
    {
        return $this->hasOne(ExchangeItem::class, 'item_id', 'item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function api()
    {
        return $this->hasOne(ExchangeApi::class, 'exchange_api_id', 'id');
    }
}
