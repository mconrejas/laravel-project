<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeTransactionDeletedRow extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exchange_transactions_deleted_rows';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'transaction_id';

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
        'deleted_on',
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
}
