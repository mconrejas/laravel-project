<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeItemPrice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exchange_items_prices';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'price_id';

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
        'item_id',
        'currency',
        'price',
        'source',
        'created',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exchangeItem()
    {
        return $this->belongsTo(ExchangeItem::class, 'item_id', 'item_id');
    }
}
