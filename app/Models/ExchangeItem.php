<?php

namespace Buzzex\Models;

use Buzzex\Crypto\Currency\CoinFactory;
use Buzzex\Crypto\Currency\Coins\Exceptions\CoinNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; 
use Buzzex\Models\CoinCompetitionRecord;
use Buzzex\Models\CoinCompetition;

class ExchangeItem extends Model
{
    use LogsActivity;

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
    protected $primaryKey = 'item_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'exchange_api_id',
        'name',
        'description',
        'symbol',
        'type',
        'token_address',
        'deposits_off',
        'withdrawals_off',
        'withdrawal_fee',
        'index_price_usd',
        'index_price_btc',
        'alternative_deposit',
        'alternative_withdrawal',
        'limits',
        'created',
        'deleted',
        'icon'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Get icon full Url
     * Symlink from storage
     *
     * @return string
     */
    public function getIconUrlAttribute()
    {
        if (is_null($this->icon)) {
            return parameter('icon.default', '');
        }
        return asset('storage/icons/'.$this->icon);
    }

    /**
     * Change ExchangeItem event description
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
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('deleted', 0);
    }

    protected function coinInstance(){
        $coin = CoinFactory::create($this->symbol);

        if (!$coin && !$this->getAltDepositStatus()) {
            throw new CoinNotFoundException(__('Invalid coin.'));
        }

        return $coin;
    }
    /**
     * @return string
     */
    public function getAddressesTableAttribute()
    {
        if(!$this->coinInstance() && $this->getAltDepositStatus()){
            return strtolower($this->symbol)."_addresses";
        }
        return $this->coinInstance()->getTable();
    }

    /**
     * @return string
     */
    public function getAddressesAssignedTableAttribute()
    {
        return $this->getAddressesTableAttribute() ."_assigned";
    }

    /**
     * @return string
     */
    public function getAddressesAssignedDeletedRowsTableAttribute()
    {
        return $this->getAddressesTableAttribute() ."_assigned_deleted_rows";
    }

    /**
     * @return string
     */
    public function getAddressesAssignedHistoryTableAttribute()
    {
        return $this->getAddressesTableAttribute() ."_assigned_history";
    }

    /**
     * @return string
     */
    public function getAddressesDeletedRowsTableAttribute()
    {
        return $this->getAddressesTableAttribute() ."_deleted_rows";
    }

    /**
     * @return string
     */
    public function getAddressesHistoryTableAttribute()
    {
        return $this->getAddressesTableAttribute() ."_history";
    }


    /**
     * Get number of confirmations required for a deposit to be released
     * @return mixed
     * @throws CoinNotFoundException
     */
    public function getNumberOfConfirmationsAttribute(){
        if(!$this->coinInstance() && $this->getAltDepositStatus()){
            return 6;
        }
        return $this->coinInstance()->numberOfConfirmationsRequired();
    }

    public function getMinimumDepositAmount(){
        if(in_array($this->symbol,array("ETH","ETC"))) return $this->getWithdrawalFee() * 10;
        elseif($this->coinInstance()->getIsErc20()) return $this->getWithdrawalFee() * 10;
        else return $this->getWithdrawalFee();
    }

    /**
     * @return collection
     */
    public function getExchangeApi()
    {
        return $this->hasOne('\Buzzex\Models\ExchangeApi', 'id', 'exchange_api_id')->first();
    }

    /**
     * @return string|boolean
     */
    public function getAltDepositStatus()
    {
        if($this->alternative_deposit < 0) {
            return parameter('exchange.alt_deposit_status',0) > 0 ? true : false;
        };

        return $this->alternative_deposit > 0 ? true : false;
    }

    /**
     * @return bool
     */
    public function getAltWithdrawalStatus()
    {
        if($this->alternative_withdrawal < 0) {
            return parameter('exchange.alt_withdrawal_status',0) > 0 ? true : false;
        };

        return $this->alternative_withdrawal > 0 ? true : false;
    }

    /**
     * @return mixed
     */
    public function getLimits(){
        return is_null($this->limits)?false:$this->limits;
    }

    /**
     * @return float
     */
    public function getWithdrawMinimum(){
        $limits = $this->getLimits();
        $default = 0.00000001; //1 satoshi
        if(!$limits) return $default;
        return (isset($limits["minWithdraw"]) && $limits["minWithdraw"] > 0)?$limits["minWithdraw"]:$default;
    }

    /**
     * @return int
     */
    public function getWithdrawMaximum(){
        $limits = $this->getLimits();
        $default = 0; //Unlimited
        if(!$limits) return $default;
        return (isset($limits["maxWithdraw"]) && $limits["maxWithdraw"] > 0)?$limits["maxWithdraw"]:$default;
    }

    /**
     * @return float
     */
    public function getDepositMinimum(){
        $limits = $this->getLimits();
        $default = 0.001;
        if(!$limits) return $default;
        return (isset($limits["minDeposit"]) && $limits["minDeposit"] > 0)?$limits["minDeposit"]:$default;
    }

    /**
     * @return float
     */
    public function getDepositMaximum(){
        $limits = $this->getLimits();
        $default = 0.001;
        if(!$limits) return $default;
        return (isset($limits["maxDeposit"]) && $limits["maxDeposit"] > 0)?$limits["maxDeposit"]:$default;
    }

    public function getWithdrawalFee()
    {
        if($this->withdrawal_fee < 0) {
            return parameter('exchange.withdrawal_fee',0);
        };

        return $this->withdrawal_fee;
    }

    /**
     * get total trade fees collected per exchange item
     * @return bool
     */
    public function getTradeFeesCollected($processing_status=-1){
        if(!$this->item_id) return false;
        $fees = ExchangeTransaction::where('item_id',$this->item_id)
            ->where('module','exchange_fulfillments')
            ->where('cancelled',0)
            ->where('fee','>',0);
        if($processing_status == 0){
            $fees = $fees->where('processed',0);
        }elseif($processing_status > 0){
            $fees = $fees->where('processed','>',0);
        }
        $fees = $fees->sum('fee');
        if(!$fees){
            return false;
        }
        return $fees;
    }


}
