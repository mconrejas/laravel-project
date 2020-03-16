<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class UserSignin extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_signin';

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'ip',
        'device',
        'location'
    ];


    /**
     * Get the owner of this model
     *
     * @var array
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    /**
     * Get the signin device json to array
     *
     * @param  string  $value
     * @return array
     */
    public function getDeviceAttribute($value)
    {
        return json_decode($value,true);
    }

    /**
     * Get the signin location json to array
     *
     * @param  string  $value
     * @return array
     */
    public function getLocationAttribute($value)
    {
        return json_decode($value,true);
    }

    /**
     * Get scope 
     *
     * @param \Illuminate\Database\Eloquent\Builder
     * @param referral string
     */
    public static function scopelast30Days(Builder $query)
    {
        return $query->where('created_at','>=', date("Y-m-d H:i:s",strtotime('-30 days')))->orderBy('id','desc')->get();
    }

}
