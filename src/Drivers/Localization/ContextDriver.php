<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Drivers\Localization;

use Illuminate\Support\Facades\Context;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract;
use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\Concerns\IsContextIsolated;

class ContextDriver implements LocalizationDriverContract
{
    use IsContextIsolated;

    private const string CONTEXT_KEY = 'localization_locale';

    public function getCurrentLocale(): string
    {
        return Context::get(self::CONTEXT_KEY);
    }

    public function setCurrentLocale(string $locale): void
    {
        Context::add(self::CONTEXT_KEY, $locale);
    }
}
