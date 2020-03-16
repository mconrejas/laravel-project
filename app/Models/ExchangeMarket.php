<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeMarket extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_id',
        'order'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function exchangeItem()
    {
        return $this->hasOne(ExchangeItem::class, 'item_id', 'item_id');
    }
}
