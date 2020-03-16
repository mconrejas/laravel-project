<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordSecurity extends Model
{
	/**
     * The attributes that are guarded.
     *
     * @var array
     */
    protected $guarded = [];
 	
 	/**
     * Get the owner of this model
     *
     * @var array
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
