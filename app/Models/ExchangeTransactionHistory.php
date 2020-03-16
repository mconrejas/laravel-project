<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeTransactionHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exchange_transactions_history';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'history_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'module',
        'module_id',
        'user_id',
        'item_id',
        'amount',
        'fee',
        'type',
        'remarks',
        'remarks2',
        'address',
        'category',
        'txid',
        'time',
        'confirmations',
        'raw_data',
        'created',
        'cancelled',
        'approved',
        'released',
        'insert_date',
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
    public function exchangeItem()
    {
        return $this->belongsTo(ExchangeItem::class, 'item_id', 'item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exchangeTransaction()
    {
        return $this->belongsTo(ExchangeTransaction::class, 'transaction_id', 'transaction_id');
    }
}
