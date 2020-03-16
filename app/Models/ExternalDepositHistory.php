<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalDepositHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'external_deposit_history';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'txid',
        'timestamp',
        'status',
        'address',
        'raw_data',
        'amount',
        'source',
        'fee',
        'asset',
        'has_match'
    ];
}
