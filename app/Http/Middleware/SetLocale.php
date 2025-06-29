<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $supportedLocales = ['en', 'ar'];
        $locale = null;

        $localeHeader = $request->header('Accept-Language');

        if ($localeHeader) {
            $headerLocale = strtolower(substr($localeHeader, 0, 2));
            if (in_array($headerLocale, $supportedLocales)) {
                $locale = $headerLocale;
            }
        }

        if (!$locale) {
            $user = Auth::user();
            if ($user && in_array($user->locale, $supportedLocales)) {
                $locale = $user->locale;
            }
        }

        if (!$locale) {
            $locale = 'en';
        }

        App::setLocale($locale);
        return $next($request);
    }
}
