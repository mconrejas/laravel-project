<?php

namespace Buzzex\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class CheckExternalApiKey
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
     
        if ($request->get('apiKey') != env('EXTERNAL_API_KEY')) {
            throw new AuthenticationException(__('Invalid external api key.'));
        }

        return $response;
    }
}
