<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class RevdistPool extends Model
{

    protected $primaryKey = 'pool_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'currency',
        'source',
        'name',
        'description',
        'function',
        'requirement_ids',
        'reference_pool_ids',
        'share_percentage',
        'status_id'
    ];

}
