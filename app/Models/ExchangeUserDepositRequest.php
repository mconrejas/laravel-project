<?php

namespace Buzzex\Models;

use Buzzex\Models\ExchangeApi;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\User;
use Illuminate\Database\Eloquent\Model;

class ExchangeUserDepositRequest extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exchange_user_deposit_requests';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'request_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'exchange_api_id',
        'item_id',
        'amount',
        'created',
        'canceled',
        'expired',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(ExchangeItem::class, 'item_id', 'item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function api()
    {
        return $this->belongsTo(ExchangeApi::class, 'exchange_api_id', 'id');
    }
}
