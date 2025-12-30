<?php

namespace Josemontano1996\LaravelLocalizationSuite\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string getCurrentLocale()
 * @method static void setCurrentLocale(string $locale)
 * @method static string route(mixed $name, mixed $parameters = [], bool $absolute = true)
 */
class Localization extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        // This must match the string key or the Interface name
        // you used in your ServiceProvider's bind/scoped method.
        return \Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract::class;
    }
}
