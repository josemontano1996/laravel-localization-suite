<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Drivers\Localization;

use Exception;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract;

class NativeDriver implements LocalizationDriverContract
{
    public function __construct()
    {
        throw new Exception('Not implemented');
    }

    public function getCurrentLocale(): string
    {
        throw new Exception('Not implemented');
    }

    /**
     * Set the current locale for the request.
     *
     * @param  string  $locale  Locale code to set (case-sensitive)
     */
    public function setCurrentLocale(string $locale): void
    {
        throw new Exception('Not implemented');
    }
}
