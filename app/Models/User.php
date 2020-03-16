<?php

namespace Buzzex\Models;

use Buzzex\Models\Traits\RevisionableTrait;
use Buzzex\Models\Traits\HasCoinAddress;
use Buzzex\Models\Traits\HasFunds;
use Buzzex\Models\Traits\HasTradeFeeDiscounts;
use Buzzex\Models\Traits\UserReferral;
use Buzzex\Models\Traits\UserVotes;
use Cklmercer\ModelSettings\HasSettings;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

// use Venturecraft\Revisionable\RevisionableTrait;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, UserReferral, RevisionableTrait, HasRoles, HasSettings, UserVotes, HasCoinAddress, HasTradeFeeDiscounts, HasFunds;

    /**
     * The attributes that check if model has revision enable.
     *
     * @var array
     */
    protected $revisionEnabled = true;

    /**
     * Track creation of model
     *
     * @var array
     */

    protected $revisionCreationsEnabled = false;
    /**
     * Dont track changes on this fields.
     *
     * @var array
     */
    protected $dontKeepRevisionOf = [
        'updated_at',
        'remember_token',
        'settings',
    ];
    /**
     * In case for unknow tracking or null string
     *
     * @var strings
     */
    protected $revisionNullString = 'nothing';
    protected $revisionUnknownString = 'unknown';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'affiliate_id',
        'referred_by',
        'profile_picture',
        'email_verified_at',
        'blocked',
        'username'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The model's default settings.
     *
     * @var array
     */
    protected $defaultSettings = [
        'announcement_enable' => 1,
        'locale'              => 'en',
        'theme'               => 'light',
        'admin_theme'         => 'bg1',
    ];

    /**
     * @var casting
     */
    protected $casts = [
        'blocked' => 'array'
    ];

    /**
     * boot
     *
     */
    public static function boot()
    {
        parent::boot();
    }

    /**
     * Get Name
     *
     * First name and last name combined.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return ucwords($this->first_name . ' ' . $this->last_name);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function passwordSecurity()
    {
        return $this->hasOne(PasswordSecurity::class);
    }

    /**
     * @return bool
     */
    public function is2FAEnable()
    {
        $passwordSecurity = $this->passwordSecurity;

        return $passwordSecurity ? $passwordSecurity->google2fa_enable : false;
    }

    /**
     * @return bool
     */
    public function hasBindMobile()
    {
        return !empty($this->mobile_number);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userRequest()
    {
        return $this->hasMany(UserRequests::class, 'user_id', 'id');
    }

    /**
     * Get user request by code
     *
     * @return Collection
     */
    public function getRequestByCode($code, $type = 'password_update')
    {
        return $this->userRequest->where('code', $code)->sortByDesc('id')->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userSignin()
    {
        return $this->hasMany(UserSignin::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function votes()
    {
        return $this->hasMany(UserCoinVote::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clients()
    {
        return $this->hasMany(OauthClient::class, 'user_id', 'id');
    }

    /**
     * Get roles for user
     *
     * Concat all role of user
     *
     * @return string
     */
    public function allRoles()
    {
        return implode(", ", $this->roles->reverse()->pluck('name')->toArray());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getPersonalVerification()
    {
        return $this->hasOne(KycMedia::class, 'user_id', 'id')
            ->where('type', '=', 'personal')->latest();
    }

    /**
     * Get settings
     *
     * get settings fave coin.
     *
     * @return string
     */
    public function getFavePairsAttribute()
    {
        return array_key_exists(
            'fave_pairs',
            $this->settings
        ) && isset($this->settings['fave_pairs']) ? $this->settings['fave_pairs'] : [];
    }

    /**
     * Get deposit transactions in exchange_transactions
     * type -- 'deposit-request' or 'deposit'
     *
     * @return int
     */
    public function countDepositTransactions($text)
    {
        return $this->hasMany(ExchangeTransaction::class, 'user_id', 'id')
            ->join('exchange_items', 'exchange_transactions.item_id', 'exchange_items.item_id')
            ->where('exchange_items.symbol', 'like', '%' . $text . '%')
            ->where('exchange_transactions.type', 'like', '%deposit%')
            ->where('exchange_transactions.amount', '>', 0)
            ->count();
    }

    /**
     * Get withdrawal transactions in exchange_transactions
     *
     * @return int
     */
    public function countWithdrawalTransactions($text)
    {
        return $this->hasMany(ExchangeTransaction::class, 'user_id', 'id')
            ->join('exchange_items', 'exchange_transactions.item_id', 'exchange_items.item_id')
            ->where('exchange_items.symbol', 'like', '%' . $text . '%')
            ->where('exchange_transactions.type', '=', 'withdrawal-request')
            ->where('exchange_transactions.cancelled', '=', 0)
            ->count();
    }

    /**
     * Get transaction history
     *
     * @return int
     */
    public function countTransactionHistory($text = "", $type = "")
    {
        return $this->hasMany(ExchangeTransactionHistory::class, 'user_id', 'id')
            ->join(
                'exchange_transactions',
                'exchange_transactions_history.transaction_id',
                'exchange_transactions.transaction_id'
            )
            ->join('exchange_items', 'exchange_transactions.item_id', 'exchange_items.item_id')
            ->where('exchange_items.symbol', 'like', '%' . $text . '%')
            ->where('exchange_transactions.type', 'like', '%' . $type . '%')
            ->count();
    }

    /**
     * Get deposit transactions in exchange_transactions
     * type -- 'deposit-request' or 'deposit'
     *
     * @return int
     */
    public function countPendingDepositTransactions($text)
    {
        return $this->hasMany(ExchangeTransaction::class, 'user_id', 'id')
            ->join('exchange_items', 'exchange_transactions.item_id', 'exchange_items.item_id')
            ->where('exchange_items.symbol', 'like', '%' . $text . '%')
            ->where('exchange_transactions.type', 'like', '%deposit%')
            ->where('exchange_transactions.cancelled', '=', 0)
            ->where('exchange_transactions.amount', '>', 0)
            ->where(function ($query) {
                $query->where('exchange_transactions.released', '>', time())
                    ->orWhere('exchange_transactions.released', '=', 0);
            })
            ->count();
    }

    /**
     * Get user profile picture
     * @return string
     */
    public function getProfilePicture()
    {
        return $this->profile_picture
            ? asset("storage/profiles/{$this->profile_picture}")
            : asset('img/user.jpg');
    }

    /**
     * Get user kyc media
     * @return \Buzzex\Models\KycMedia
     */
    public function kycMedia()
    {
        return $this->hasOne(KycMedia::class, 'user_id', 'id');
    }

    /**
     * Check if user is KYC verified
     * @return bool
     */
    public function isKycSubmitted()
    {
        $verification = $this->getPersonalVerification();
        if ($verification->count() > 0 && $verification->first()->approved !=  2) {
            return true;
        }
        return false;
    }

    /**
     * Check if user is KYC verified
     * @return bool
     */
    public function isKycVerified()
    {
        $kycMedia = (new KycMedia())->newQuery()
            ->where('user_id', $this->id)
            ->where('approved', 1)
            ->first();

        return $kycMedia ? true : false;
    }

    /**
     * @return Parameter|string
     */
    public function dailyWithdrawLimit()
    {
        return $this->isKycVerified()
            ? parameter('kyc_verified_daily_withdrawal_limit', 100000)
            : parameter('non_kyc_verified_daily_withdrawal_limit', 10000);
    }
}
