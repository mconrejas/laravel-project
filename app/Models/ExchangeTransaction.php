<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ExchangeTransaction extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'transaction_id';

    /**
     * @var casting
     */
    protected $casts = [
        'logs' => 'array'
    ];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'module',
        'module_id',
        'user_id',
        'item_id',
        'amount',
        'fee',
        'type',
        'remarks',
        'remarks2',
        'address',
        'category',
        'txid',
        'time',
        'confirmations',
        'raw_data',
        'created',
        'cancelled',
        'approved',
        'released',
        'exchange_api_id',
        'tag',
        'processed',
        'logs',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exchangeItem()
    {
        return $this->belongsTo(ExchangeItem::class, 'item_id', 'item_id');
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWithdrawals(Builder $query)
    {
        return $query->where('type', 'withdrawal-request');
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeDeposits(Builder $query)
    {
        return $query->where('type', 'like', 'deposit%');
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopePending(Builder $query)
    {
        return $query->where('cancelled', 0)
            ->where('approved', 0)
            ->where('released', 0);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeApproved(Builder $query)
    {
        return $query->where('cancelled', 0)
            ->where('approved', '!=', 0);
    }

    /**
     * Approved but not yet released
     * @param Builder $query
     * @return $this
     */
    public function scopeReleasing(Builder $query)
    {
        return $query->where('cancelled', 0)
            ->where('approved', '!=', 0)
            ->where('released', 0);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeReleased(Builder $query)
    {
        return $query->where('cancelled', 0)
            ->where('approved', '!=', 0)
            ->where('released', '!=', 0);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeCancelled(Builder $query)
    {
        return $query->where('cancelled', '!=', 0);
    }

    /**
     *
     * @return boolean
     */
    public function isPending()
    {
        return ($this->released == 0 && $this->approved == 0 && $this->cancelled == 0);
    }

    /**
     *
     * @return boolean
     */
    public function isCancelled()
    {
        return ($this->released == 0 && $this->approved == 0 && $this->cancelled > 0);
    }

    /**
     *
     * @return boolean
     */
    public function isApproved()
    {
        return ($this->released == 0 && $this->approved > 0 && $this->cancelled == 0);
    }

    /**
     *
     * @return boolean
     */
    public function isReleased()
    {
        return ($this->released > 0 && $this->cancelled == 0);
    }

    /**
     *
     * @return boolean
     */
    public function isProcessed()
    {
        return ($this->processed > 0 && $this->cancelled == 0 && $this->approved > 0 && $this->released == 0);
    }

    /**
     *
     * @return String
     */
    public function getStatus()
    {
        return getTxnStatus($this->cancelled, $this->approved, $this->released, $this->processed);
    }

    /**
     *
     * @return timestamp
     */
    public static function getMilestoneLastDate($item_id)
    {
        $current_milestone = CoinCompetitionRecord::where('item_id', $item_id)
                                                    ->orderBy('completed_at', 'DESC')
                                                    ->pluck('completed_at');
                                      
        return count($current_milestone) > 0 ? $current_milestone[0] : 0;
    }

    /**
     *
     * @return array
     */
    public static function getTransactions($item_id, $partner_only=false)
    {
        $last_date = ExchangeTransaction::getMilestoneLastDate($item_id);


        $transactions = ExchangeTransaction::selectRaw("
                        coalesce(sum(exchange_transactions.amount), 0) as amount_orig, 
                        FORMAT(sum(exchange_transactions.amount * exchange_transactions.item_btc_price), 8) as amount_btc
                    ")
                    ->join('exchange_items', 'exchange_transactions.item_id', '=', 'exchange_items.item_id')
                    ->join('users', 'users.id', '=', 'exchange_transactions.user_id')
                    ->where('exchange_items.item_id', $item_id)
                    ->where('exchange_transactions.module', 'exchange_fulfillments')
                    ->where('exchange_transactions.amount', '>', 0)
                    ->where('exchange_transactions.cancelled', '=', 0)
                    ->whereNotIn('users.email', config('account.official_emails'))
                    ->orderBy('exchange_transactions.created', 'ASC');

        // if partner only
        if ($partner_only) {
            $transactions = $transactions->where('users.settings->is_coin_partner', true);
        }

        return $transactions->first();
    }

    /**
     *
     * @return array
     */
    public static function getUsersFromTransactions($item_id, $partner_only=false)
    {
        $last_date = ExchangeTransaction::getMilestoneLastDate($item_id);


        $transactions = ExchangeTransaction::selectRaw("
                        users.*, 
                        FORMAT(sum(ABS(exchange_transactions.amount) * exchange_transactions.item_btc_price), 8) as volume
                    ")
                    ->join('exchange_items', 'exchange_items.item_id', '=', 'exchange_transactions.item_id')
                    ->join('users', 'users.id', '=', 'exchange_transactions.user_id')
                    ->where('exchange_transactions.created', '>', $last_date)
                    ->where('exchange_items.item_id', '=', $item_id)
                    ->where('exchange_transactions.module', '=', 'exchange_fulfillments')
                    ->where('exchange_transactions.cancelled', '=', 0)
                    ->whereNotIn('users.email', config('account.official_emails'));
        // if partner only
        if ($partner_only) {
            $transactions = $transactions->where('users.settings->is_coin_partner', true);
        }

        $transactions = $transactions->groupBy('exchange_transactions.user_id')
                    // ->orderBy('volume', 'desc')
                    ->orderByRaw("sum(ABS(exchange_transactions.amount) * exchange_transactions.item_btc_price) DESC")
                    ->take(10);

        return $transactions->get();
    }

     
}
