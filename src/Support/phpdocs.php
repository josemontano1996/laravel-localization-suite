<?php

declare(strict_types=1);

namespace Illuminate\Http;

/**
 * @method array acceptedLocales()
 * @method string|null preferredLocale(array $supported = [])
 * @method string locale()
 */
class Request {}

namespace Illuminate\Routing;

use Josemontano1996\LaravelLocalizationSuite\Services\RedirectorService;

class Redirector
{
    public function localized(): RedirectorService
    {
        // Dummy return for the analyzer
        return new RedirectorService(
            $this,
            app(\Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract::class)
        );
    }
}

namespace Illuminate\Support\Facades;

/**
 * @method static string withLocale(string $locale)
 * @method static string localeRoute($name, $params = [], $absolute = true)
 */
class URL {}
