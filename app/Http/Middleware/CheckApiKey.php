<?php

namespace Buzzex\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     * @throws AuthenticationException
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (!isAuthorizedWithdrawalProcessor($request->get('apiKey'))) {
            throw new AuthenticationException(__('Invalid api withdrawal key.'));
        }

        return $response;
    }
}
