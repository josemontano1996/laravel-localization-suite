<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Registrars;

use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Services\RedirectorService;

class RegisterMacros
{
    public static function register(): void
    {
        $contract = LocalizationServiceContract::class;

        // 1. Redirector macro
        Redirector::macro('localized', function () use ($contract): RedirectorService {
            /** @var \Illuminate\Routing\Redirector $this */
            return new RedirectorService(
                $this,
                app($contract) // Resolves the scoped instance for the current request
            );
        });

        // 2. Request macros
        Request::macro('locale', fn() => app($contract)->getCurrentLocale());

        Request::macro('acceptedLocales', fn(): array => array_flip($this->getLanguages()));

        Request::macro('preferredLocale', function (array $supported = []): ?string {
            $accepted = $this->acceptedLocales();
            if (empty($accepted)) {
                return null;
            }

            if ($supported === []) {
                return (string) \array_key_first($accepted);
            }

            $normalizedSupported = [];
            foreach ($supported as $original) {
                $normalizedSupported[strtolower((string) $original)] = (string) $original;
            }

            foreach ($accepted as $locale => $quality) {
                $localeLower = strtolower((string) $locale);
                if (isset($normalizedSupported[$localeLower])) {
                    return $normalizedSupported[$localeLower];
                }
                $langPrefix = explode('-', $localeLower)[0];
                if (isset($normalizedSupported[$langPrefix])) {
                    return $normalizedSupported[$langPrefix];
                }
            }

            return null;
        });

        // 3. URL macros
        URL::macro('withLocale', function (string $locale) use ($contract): string {
            $service = app($contract);
            $routeKey = $service->getRouteKey();
            $route = request()->route();

            if (! $route || ! $route->getName()) {
                $currentUrl = request()->fullUrl();
                $currentLocale = $service->getCurrentLocale();

                return str_replace("/{$currentLocale}/", "/{$locale}/", $currentUrl);
            }

            return $service->route(
                $route->getName(),
                array_merge($route->parameters(), [$routeKey => $locale])
            );
        });

        URL::macro('localeRoute', fn(\BackedEnum|string $name, $params = [], bool $absolute = true) => app($contract)->route($name, $params, $absolute));

        // 4. Route macro for locale-prefixed groups
        Route::macro('localized', function (?\Closure $callback = null) use ($contract) {
            $service = app($contract);
            $routeKey = $service->getRouteKey(); // Dynamically get the key (e.g., 'lang')
            $supportedList = $service->getSupportedLocales();

            $regex = empty($supportedList)
                ? '[a-zA-Z-]+'
                : '(?i)'.implode('|', array_map(preg_quote(...), $supportedList));

            // Use the dynamic key for the prefix and the where constraint
            $registrar = Route::prefix('{'.$routeKey.'}')->where([$routeKey => $regex]);

            return $callback instanceof \Closure ? $registrar->group($callback) : $registrar;
        });
    }
}
