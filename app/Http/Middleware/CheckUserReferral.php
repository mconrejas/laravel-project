<?php 
namespace Buzzex\Http\Middleware;

use Closure;
use Buzzex\Models\User;

class CheckUserReferral
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if ($request->hasCookie('referral')) {
            return $next($request);
        }
        
        if (($ref = $request->code) && app(config('referral.user_model', User::class))->referralExists($ref)) {
            return redirect($request->fullUrl())->withCookie(cookie()->forever('referral', $ref));
        }

        return $next($request);
    }
}
