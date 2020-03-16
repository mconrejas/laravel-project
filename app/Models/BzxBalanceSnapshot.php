<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class BzxBalanceSnapshot extends Model
{

    protected $primaryKey = 'snapshot_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'amount',
        'added_by',
        'time'
    ];

}
