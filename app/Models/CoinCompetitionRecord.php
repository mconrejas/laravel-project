<?php

namespace Buzzex\Models;

use Buzzex\Models\CoinCompetition;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CoinCompetitionRecord extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'competition_id',
        'item_id',
        'completed_at',
        'winners'
    ];

    /**
     * @var casting
     */
    protected $casts = [
        'winners' => 'array'
    ];

    /**
     * Get competition details
     */
    public function competition()
    {
        return $this->belongsTo(CoinCompetition::class, 'competition_id', 'id');
    }

    /**
     * Get competition details
     */
    public function exchange_item()
    {
        return $this->belongsTo(ExchangeItem::class, 'item_id', 'item_id');
    }
 
    /**
     * Get competition partner winner
     * @return \Buzzex\Models\User | null
     */
    public function getCoinPartnerWinner()
    {
        $partner_winner = array_key_exists('partner_winner', $this->winners) ? $this->winners['partner_winner'] : [];
        if (!empty($partner_winner)) {
            $user = User::find($partner_winner['id']);
            if ($user) {
                $user->rewards = $partner_winner['reward'];
                $user->claimed_at = $partner_winner['claimed_at'];
                return $user;
            }
        }
        return null;
    }

    /**
     * check if given user id is a partner winner
     * @return boolean
     */
    public function isPartnerWinner($user_id = 0)
    {
        $partner_winner = $this->getCoinPartnerWinner();

        if (is_null($partner_winner)) {
            return false;
        }
        
        return ($partner_winner->id == $user_id);
    }

    /**
     * check if given user id is in general winners
     * @return boolean
     */
    public function isGeneralWinner($user_id = 0)
    {
        $general_winners = $this->getGeneralWinners();
        if (empty($general_winners)) {
            return false;
        }
        foreach ($general_winners as $key => $winner) {
            if ((int)$user_id == (int) $winner->id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get competition general winners
     * @return array of \Buzzex\Models\User
     */
    public function getGeneralWinners()
    {
        $winners = array();
        $general_winners = array_key_exists('general_winners', $this->winners) ? $this->winners['general_winners'] : [];
        if (!empty($general_winners)) {
            $ids = array_column($general_winners, 'id');

            foreach ($ids as $key => $id) {
                $user = User::find($id);
                if ($user) {
                    $user->rewards = $general_winners[$key]['reward'];
                    $user->claimed_at = $general_winners[$key]['claimed_at'];
                    $user->total_volume = $general_winners[$key]['total_volume'];
                    $winners[] = $user;
                }
            }
        }
        return $winners;
    }

    /**
    * Get competition general winner by id
    * @return \Buzzex\Models\User
    */
    public function getGeneralWinnerByUserId($user_id = 0)
    {
        $winners = $this->getGeneralWinners();
        if (!empty($winners)) {
            foreach ($winners as $key => $winner) {
                if ($winner->id == $user_id) {
                    return $winner;
                }
            }
        }
        return null;
    }

    /**
    * Claim general winner by id
    * @return \Buzzex\Models\User
    */
    public function setClaimGeneralWinner($user_id = 0)
    {
        $winners = $this->winners;
        if (!empty($winners)) {
            $general_winners = $winners['general_winners'];
            if (!empty($general_winners)) {
                foreach ($general_winners as $key => $general_winner) {
                    if ((int) $general_winner['id'] == $user_id) {
                        $general_winners[$key]['claimed_at'] = Carbon::now()->timestamp;
                    }
                }
                $winners['general_winners'] = $general_winners;
                $this->winners = $winners;
                $this->save();
            }
        }
    }

    /**
    * Claim partner winner
    * @return \Buzzex\Models\User
    */
    public function setClaimPartnerWinner()
    {
        $winners = $this->winners;
        if (!empty($winners)) {
            $winners['partner_winner']['claimed_at'] = Carbon::now()->timestamp;
            $this->winners = $winners;
            $this->save();
        }
    }
}
