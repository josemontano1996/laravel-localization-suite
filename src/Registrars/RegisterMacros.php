<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Registrars;

use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Josemontano1996\LaravelLocalizationSuite\Facades\Localization;
use Josemontano1996\LaravelLocalizationSuite\Services\RedirectorService;

class RegisterMacros
{
    public static function register(): void
    {
        $facade = '\\'.Localization::class;

        // 1. Redirector macro
        Redirector::macro('localized', function () use ($facade) {
            /** @var \Illuminate\Routing\Redirector $this */
            return new RedirectorService(
                $this,
                $facade::getFacadeRoot()
            );
        });

        // 2. Request macros
        Request::macro('locale', function () use ($facade) {
            return $facade::getCurrentLocale();
        });

        Request::macro('acceptedLocales', function (): array {
            return array_flip($this->getLanguages());
        });

        Request::macro('preferredLocale', function (array $supported = []): ?string {
            $accepted = $this->acceptedLocales();

            if (empty($accepted)) {
                return null;
            }

            if (empty($supported)) {
                return (string) \array_key_first($accepted);
            }

            $normalizedSupported = [];
            foreach ($supported as $original) {
                $normalizedSupported[strtolower((string) $original)] = (string) $original;
            }

            foreach ($accepted as $locale => $quality) {
                $localeLower = strtolower((string) $locale);

                // 1. Try exact match (case-insensitive)
                if (isset($normalizedSupported[$localeLower])) {
                    return $normalizedSupported[$localeLower];
                }

                // 2. Try language prefix match (e.g., 'en' from 'en-US')
                $langPrefix = explode('-', $localeLower)[0];
                if (isset($normalizedSupported[$langPrefix])) {
                    return $normalizedSupported[$langPrefix];
                }
            }

            return null;
        });

        URL::macro('withLocale', function (string $locale) use ($facade): string {
            $route = request()->route();

            if (! $route || ! $route->getName()) {
                $currentUrl = request()->fullUrl();
                $currentLocale = $facade::getCurrentLocale();

                return str_replace("/{$currentLocale}/", "/{$locale}/", $currentUrl);
            }

            return $facade::route(
                $route->getName(),
                array_merge($route->parameters(), ['locale' => $locale])
            );
        });

        URL::macro('localeRoute', function ($name, $params = [], $absolute = true) use ($facade) {
            return $facade::route($name, $params, $absolute);
        });

        // Route macro for locale-prefixed route groups
        Route::macro('localized', function (?\Closure $callback = null) use ($facade) {
            $supportedList = $facade::getSupportedLocales();

            // In RegisterMacros.php -> Route::macro
            $regex = ! empty($supportedList)
                ? '(?i)'.implode('|', array_map('preg_quote', $supportedList))
                : '[a-zA-Z-]+';

            $registrar = Route::prefix('{locale}')->where(['locale' => $regex]);

            return $callback ? $registrar->group($callback) : $registrar;
        });
    }
}
