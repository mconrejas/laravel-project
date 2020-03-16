<?php

namespace Buzzex\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CheckLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $url = $this->fixUrlUsingDefaultLocale($request);

        if (!$this->isValidLocale($request->locale) && !$this->isDefaultLocale($request, $request->locale)) {
            //  return redirect($url);
        }

        $locale = $this->getLocale($request);

        if (Auth::check()) {
            // @todo get User prefered locale
            $locale = Auth::user()->settings('locale', $locale);
        }

        URL::defaults(['locale' => $locale]);
        App::setLocale($locale);

        return $next($request);
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function getLocale(Request $request)
    {
        if (!$this->isValidLocale($request->locale)) {
            return $request->getDefaultLocale();
        }

        return Cache::rememberForever($request->ip() . '-locale-preferred', function () use ($request) {
            return $request->locale;
        });
    }

    /**
     * @param string $locale
     * @return bool|\Illuminate\Http\Response
     */
    protected function isValidLocale($locale)
    {
        return file_exists(resource_path("lang/{$locale}.json"));
    }

    /**
     * @param Request $request
     * @param string $locale
     * @return boolean
     */
    protected function isDefaultLocale(Request $request, $locale)
    {
        return $locale === $request->getDefaultLocale();
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function fixUrlUsingDefaultLocale(Request $request)
    {
        $pathItems = explode('/', $request->path());

        if (count($pathItems) > 1) {
            unset($pathItems[0]);
        }

        $uriWithoutLocale = implode('/', $pathItems);

        return config('app.url') . '/' . App::getLocale() . '/' . $uriWithoutLocale;
    }
}
