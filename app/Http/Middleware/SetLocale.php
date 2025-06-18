<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $localeHeader = $request->header('Accept-Language', 'en');
        $supportedLocales = ['en', 'ar'];
        $locale = strtolower(substr($localeHeader, 0, 2));
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'en';
        }
        App::setLocale($locale);
        return $next($request);
    }
}
