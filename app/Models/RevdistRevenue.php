<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class RevdistRevenue extends Model
{

    protected $primaryKey = 'revenue_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'amount',
        'currency',
        'source',
        'description',
        'added_by',
        'distributed'
    ];

}
