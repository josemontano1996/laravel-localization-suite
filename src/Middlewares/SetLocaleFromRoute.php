<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Symfony\Component\HttpFoundation\Response;

final class SetLocaleFromRoute
{
    public function __construct(
        private readonly LocalizationServiceContract $service
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->service->getRouteKey();
        $locale = $request->route($key);
        $supported = $this->service->getSupportedLocales();

        // 1. If the URL has an UNSUPPORTED locale
        if (! empty($locale) && ! \in_array($locale, $supported)) {
            // Find the best valid language
            $fallback = $request->preferredLocale($supported) ?? $this->service->getConfigLocale();

            // CRITICAL: Set the service state FIRST
            $this->service->setCurrentLocale((string) $fallback);

            // NOW the macro will use the correct locale for the URL
            return redirect()->localized()->route(
                $request->route()->getName() ?? 'home',
                $request->route()->parameters()
            );
        }

        // 2. If the URL has NO locale (or a valid one)
        if (empty($locale)) {
            $locale = $request->preferredLocale($supported) ?? $this->service->getConfigLocale();
        }

        $this->service->setCurrentLocale((string) $locale);

        return $next($request);
    }
}
