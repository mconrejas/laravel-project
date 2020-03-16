<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class RevdistScore extends Model
{

    protected $primaryKey = 'score_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'score',
        'type',
        'type_id',
        'type_owner_id',
        'is_qualified',
        'end_time',
        'start_time',
        'is_rerun'
    ];

}
