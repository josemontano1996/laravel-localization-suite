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

        // 1. Locale present but UNSUPPORTED
        if (! empty($locale) && ! $this->service->isSupported((string) $locale)) {
            $fallback = $this->service->negotiateLocale($request);
            $this->service->setCurrentLocale($fallback);

            $segments = $request->segments();
            // Replace the invalid locale segment with the fallback if it's the first segment
            if (! empty($segments) && $segments[0] === $locale) {
                $segments[0] = $fallback;
            }

            return redirect()->to($this->buildUrl($request, $segments));
        }

        // 2. No locale or Valid locale
        $this->service->setCurrentLocale(
            empty($locale) ? $this->service->negotiateLocale($request) : (string) $locale
        );

        return $next($request);
    }

    /**
     * Helper to build the redirect URL
     */
    private function buildUrl(Request $request, array $segments): string
    {
        $newPath = '/'.implode('/', $segments);
        $newUrl = $request->getSchemeAndHttpHost().$newPath;

        if ($query = $request->getQueryString()) {
            $newUrl .= '?'.$query;
        }

        return $newUrl;
    }
}
