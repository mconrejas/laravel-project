<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class RevdistRunSetting extends Model
{

    protected $primaryKey = 'setting_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'currency',
        'source',
        'settings',
        'start_time',
        'end_time'
    ];


}
