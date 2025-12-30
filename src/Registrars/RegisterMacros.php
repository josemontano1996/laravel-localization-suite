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
        Redirector::macro('localized', function () {
            return new RedirectorService(
                $this,
                app(LocalizationServiceContract::class)
            );
        });

        // Request macro to get current locale
        Request::macro('locale', function () {
            return localization()->getCurrentLocale();
        });

        // Request macro to parse Accept-Language header into locale => quality pairs
        Request::macro('acceptedLocales', function (): array {
            return array_flip($this->getLanguages());
        });

        // Request macro to get the best-matching locale from Accept-Language and supported locales
        Request::macro('preferredLocale', function (array $supported = []): mixed {
            $accepted = $this->acceptedLocales();

            if (empty($accepted)) {
                return null;
            }

            if (empty($supported)) {
                return \array_key_first($accepted);
            }

            // Find the first accepted locale (by quality) that's in the supported list
            foreach ($accepted as $locale => $quality) {
                // Try exact match first
                if (\in_array($locale, $supported, true)) {
                    return $locale;
                }

                // Try language prefix match (e.g., 'en' from 'en-US')
                $langPrefix = \explode('-', $locale)[0];
                if (\in_array($langPrefix, $supported, true)) {
                    return $langPrefix;
                }
            }

            return null;
        });

        // URL macro to generate current URL with different locale
        URL::macro('withLocale', function (string $locale): string {
            // This ensures that if you change how routes are built in the future,
            // this macro won't break.
            return localization()->route(
                request()->route()->getName(),
                array_merge(request()->route()->parameters(), ['locale' => $locale])
            );
        });

        // URL macro to generate a localized route URL
        URL::macro('localeRoute', function ($name, $params = [], $absolute = true) {
            return localization()->route($name, $params, $absolute);
        });

        // Route macro for locale-prefixed route groups
        // Routes will use /{locale}/... pattern
        // Note: locale validation is handled by SetLocale or SetLocaleWithPreference middleware
        // TODO: update tests for this macro
        Route::macro('localized', function (?\Closure $callback = null) {
            $registrar = Route::prefix('{locale}');

            return $callback ? $registrar->group($callback) : $registrar;
        });
    }
}
