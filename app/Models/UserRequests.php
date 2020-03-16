<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRequests extends Model
{
	use SoftDeletes;

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [ 'deleted_at' ];

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
     * Get scope 
     *
     * @param \Illuminate\Database\Eloquent\Builder
     * @param referral string
     */
    public static function scopeCodeExists(Builder $query, $code)
    {
        return $query->where('code',$code)->exists();
    }

    /**
     * Boot model
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->code = self::generateCode();
        });
    }

 	/**
     * Generate unique request code for each user
     *
     * @return string
     */
    protected static function generateCode()
    {
        $length = config('codes.length', 8);

        do {
            $code = strtoupper(str_random($length));

        } while (static::codeExists($code));
        
        return $code;
    }

}
