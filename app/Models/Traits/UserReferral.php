<?php

namespace Buzzex\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cookie;

trait UserReferral
{
    /**
     * Get user referral link
     *
     * @return sting
     */
    public function getReferralLink()
    {
        return route('referral.join', ['code'=>$this->affiliate_id]);
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @param \Illuminate\Database\Eloquent\Builder
     * @param referral string
     */
    public static function scopeReferralExists(Builder $query, $referral)
    {
        return $query->whereAffiliateId($referral)->exists();
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
            if ($referredBy = Cookie::get('referral')) {
                $model->referred_by = $referredBy;
            }
        });
    }
    
}
