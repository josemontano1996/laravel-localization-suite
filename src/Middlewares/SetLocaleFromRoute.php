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
        $key = $this->service->getRouteKey(); // usually 'locale'
        $locale = $request->route($key);
        $supported = $this->service->getSupportedLocales();

        // 1. If a locale is present in the route but is UNSUPPORTED
        if (! empty($locale) && ! \in_array($locale, $supported)) {
            $preferred = $request->preferredLocale($supported);
            $fallback = $preferred !== null ? $preferred : $this->service->getConfigLocale();

            $this->service->setCurrentLocale((string) $fallback);

            $path = $request->path();
            $segments = explode('/', trim($path, '/'));

            // Replace the invalid locale segment with the fallback
            if (! empty($segments) && $segments[0] === $locale) {
                $segments[0] = $fallback;
            }

            return redirect()->to($this->buildUrl($request, $segments));
        }

        // 2. If NO locale is present in the route parameters
        if (empty($locale)) {
            // Determine what the locale SHOULD be based on headers or config
            $determinedLocale = $request->preferredLocale($supported) ?? $this->service->getConfigLocale();

            // Set it globally so the app knows the language
            $this->service->setCurrentLocale((string) $determinedLocale);

            // IMPORTANT: We do NOT redirect here because the route might
            // purposely not have a {locale} segment (like /nolocale1).
            return $next($request);
        }

        // 3. Valid locale is present
        $this->service->setCurrentLocale((string) $locale);

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
