<?php

namespace Buzzex\Models;

use Buzzex\Models\User;
use Buzzex\Models\UserCoinVote;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class CoinProject extends Model
{
    use LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'coin_projects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'logo',
        'symbol',
        'name',
        'info',
        'status',
        'created_by',
        'updated_by'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function votes()
    {
        return $this->hasMany(UserCoinVote::class, 'project_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'id', 'created_by');
    }

    /**
     * Get icon full Url
     * Symlink from storage
     *
     * @return string
     */
    public function getIconUrlAttribute()
    {
        if (is_null($this->logo)) {
            return parameter('icon.default', '');
        }
        return asset('storage/icons/'.$this->logo);
    }

    /**
     * Get the info attribute.
     *
     * @return mixed
     */
    public function getInfosAttribute()
    {
        return json_decode($this->info, true);
    }

    /**
     * Get the info attribute by key.
     *
     * @param string $key
     * @return mixed
     */
    public function attribute($key)
    {
        $infos =  json_decode($this->info, true);

        return array_key_exists($key, $infos) ? $infos[$key] : null;
    }

    /**
     * Get the block explorer attribute.
     *
     * @return mixed
     */
    public function getBlockExplorerAttribute()
    {
        $info = $this->infos['blockchain_explorer'];
        
        return $info ? explode("\r\n", $info) : null;
    }

    /**
    * Get the vote counts
    *
    * @return mixed
    */
    public function votesCount()
    {
        return $this->hasOne(UserCoinVote::class, 'project_id', 'id')
            ->selectRaw('project_id, count(*) as aggregate')
            ->groupBy('project_id');
    }

    /**
    * Get the vote counts
    *
    * @return mixed
    */
    public function getVotesCountAttribute()
    {
        // if relation is not loaded already, let's do it first
        if (! array_key_exists('votesCount', $this->relations)) {
            $this->load('votesCount');
        }
 
        $related = $this->getRelation('votesCount');
 
        // then return the count directly
        return ($related) ? (int) $related->aggregate : 0;
    }

    /**
     * Change Coin Project event description
     *
     * @param string $eventName
     *
     * @return string
     */
    public function getDescriptionForEvent($eventName)
    {
        return __CLASS__ . " model has been {$eventName}";
    }
}
