<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Josemontano1996\LaravelLocalizationSuite\Facades\Localization;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if the route has a {locale} parameter (from your Route::localized macro)
        $locale = $request->route('locale');

        // 2. If no locale in URL, use your smart 'preferredLocale' macro 
        // to check browser headers (Accept-Language)
        if (! $locale) {
            $supported = Localization::getSupportedLocales();
            $locale = $request->preferredLocale($supported) ?? Localization::getConfigLocale();
        }

        // 3. Set the locale in the service (and the driver: session, cookie, etc.)
        Localization::setCurrentLocale($locale);

        return $next($request);
    }
}