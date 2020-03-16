<?php

namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class CoinCompetition extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'volume',
        'prize',
        'status',
        'finished'
    ];

    /**
     * get finished competition
     *
     * @param $finished_competition array
     * @return double
     */
    public static function getFinishedCompetition($item_id)
    {
        return CoinCompetitionRecord::where('coin_competition_records.item_id',$item_id)                    
                    ->orderBy('completed_at',"DESC")
                    ->pluck('competition_id')
                    ->toArray();
    }

    /**
     * get current competition
     *
     * @param $finished_competition array
     * @return array
     */
    public static function getCurrentCompetition($item_id)
    {
        $finished = parent::getFinishedCompetition($item_id);
        return parent::whereNotIn('id', $finished)->get()[0];
    }

    /**
     * get current competition
     *
     * @param $finished_competition array
     * @return array
     */
    public static function getPreviousCompetition($item_id)
    {
        $previous = parent::getFinishedCompetition($item_id)[0];
        return parent::findOrFail($previous);
    }

}
