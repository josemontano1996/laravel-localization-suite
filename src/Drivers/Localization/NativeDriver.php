<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Drivers\Localization;

use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract;
use Josemontano1996\LaravelLocalizationSuite\Traits\MutatesGlobalState;

class NativeDriver implements LocalizationDriverContract
{

    use MutatesGlobalState;
    public function getCurrentLocale(): string
    {
        return (string) app()->getLocale();
    }

    public function setCurrentLocale(string $locale): void
    {
        app()->setLocale($locale);
    }

}
