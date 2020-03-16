<?php

namespace Buzzex\Services;

use Buzzex\Contracts\Security\JWTSSO;
use Buzzex\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;

class ZendeskService implements JWTSSO
{
    /**
     * @var Firebae\JWT\JWT;
     */
    private $jwt;

    /**
     * @var zendesk secret key
     */
    protected $key;

    /**
     * @var zendesk zendeskdomain
     */

    protected $zendeskdomain;
    /**
     * Constructor
     *
     * Concat all role of user
     *
     * @return string
     */
    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;

        $this->key  = config('zendesk.secret');

        $this->zendeskdomain = config('zendesk.domain');
    }

    /**
     * Build redirect url for zendesk when login
     *
     * @param  $return_to string
     * @return string
     */
    public function buildRedirect($return_to = "")
    {
        abort_unless(Auth::check(), 403);

        $jwt = $this->generateJwt();
        $location = $this->zendeskdomain . "/access/jwt?jwt=" . $jwt . "&return_to=" . urlencode($return_to);

        return $location;
    }
    /**
     * Generate Jwt token with required fields
     *
     * Concat all role of user
     *
     * @return string
     */
    public function generateJwt()
    {
        $now = time();
        $user = User::find(Auth::user()->id);

        $token = array(
                "jti"   => bcrypt($now . rand()),
                "iat"   => $now,
                "name"  => $user->name,
                "email" => $user->email
            );

        return JWT::encode($token, $this->key);
    }
}
