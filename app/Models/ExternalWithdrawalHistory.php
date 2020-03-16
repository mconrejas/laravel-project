<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalWithdrawalHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'external_withdrawal_history';

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
        'external_id',
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
