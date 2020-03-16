<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangePairStat extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exchange_pairs_stats';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'stat_id';

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
        'pair_id',
        'pair_text',
        'last',
        'lowest_ask',
        'highest_bid',
        'price_24h',
        'base_volume',
        'quote_volume',
        'is_frozen',
        'high_24hr',
        'low_24hr',
        'updated',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exchangePair()
    {
        return $this->belongsTo(ExchangePair::class, 'pair_id', 'pair_id');
    }

    /**
     * @return float|int
     */
    public function getPercentChangeAttribute()
    {
        if ($this->price_24h > 0) {
            return (($this->last - $this->price_24h) / $this->price_24h) * 100;
        }

        return 0;
    }
}
