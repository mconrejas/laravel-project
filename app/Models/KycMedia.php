<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class KycMedia extends Model
{
    use LogsActivity;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kyc_medias';

    /**
     * @var casting
     */
    protected $casts = [
        'images' => 'array'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type',
        'first_name',
        'last_name',
        'date_of_birth',
        'street_address',
        'street_address2',
        'city',
        'state',
        'postal_code',
        'country',
        'contact_number',
        'images',
        'approved',
        'id_number',
        'id_type',
        'nationality'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Change ExchangePair event description
     *
     * @param string $eventName
     *
     * @return string
     */
    public function getDescriptionForEvent($eventName)
    {
        return __CLASS__ . " model has been {$eventName}";
    }

    /**
     * Get Name
     *
     * nationality full.
     *
     * @return string
     */
    public function getCountryNationalityAttribute()
    {
        if (!empty(trim($this->nationality))) {
            return getCountryOptions(trim($this->nationality));
        }
        return "";
    }

    /**
     * Get Name
     *
     * get kyc status.
     *
     * @return string
     */
    public function getStatus()
    {
        if ($this->approved == 1) {
            return "approved";
        } elseif ($this->approved == 2) {
            return "rejected";
        } else {
            return "pending";
        }
    }
}
