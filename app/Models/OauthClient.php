<?php


namespace Buzzex\Models;

use Illuminate\Database\Eloquent\Model;

class OauthClient extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_clients';

    protected $primaryKey = 'client_id';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'client_secret',
        'redirect_uri',
        'grant_types',
        'scope',
        'user_id',
        'created_by',
        'updated_by'
    ];

    /**
     * Get explode scope
     *
     * @return array
     */
    public function getScopesAttribute()
    {
        return explode(',', $this->scope);
    }

    /**
     * Check if has given scope
     *
     * @return array
     */
    public function hasScope($scope)
    {
        return in_array($scope, $this->scopes);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'created_by');
    }
}
