<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(
        protected LocalizationServiceContract $service
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Get locale from URL parameter {locale}
        $locale = $request->route($this->service->getRouteKey());

        // 2. If no locale in URL, detect via the service and request macros
        if (! $locale) {
            $supported = $this->service->getSupportedLocales();
            $locale = $request->preferredLocale($supported) ?? $this->service->getConfigLocale();
        }

        // 3. Set the locale in the service

        $this->service->setCurrentLocale((string) $locale);

        return $next($request);
    }
}
