<?php

namespace Buzzex\Http\Middleware;

use Buzzex\Services\ZendeskService;
use Illuminate\Support\Facades\Auth;
use Closure;

class RedirectIfAuthenticated
{
    protected $service;

    public function __construct(ZendeskService $service)
    {
        $this->service = $service;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return $this->isFromZendesk($request) ?: redirect('/home');
        }

        return $next($request);
    }

    /**
     * Check if previous request has return to from zendesk
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function isFromZendesk($request)
    {
        if ($request->has("return_to") && $request->has('brand_id')) {
            
            $location = $this->service->buildRedirect($request->return_to);

            return redirect()->away($location);
        }
        
        return false;
    }
}
