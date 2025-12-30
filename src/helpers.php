<?php

declare(strict_types=1);

use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Facades\Localization;

if (! function_exists('localization')) {
    /**
     * Get the localization service instance.
     */
    function localization(): LocalizationServiceContract
    {
        return app(LocalizationServiceContract::class);
    }
}

if (! function_exists('t')) {
    /**
     * Shortcut for context-aware translation.
     */
    function t(string $key, array $replace = [], ?string $locale = null): string
    {
        return Localization::t($key, $replace, $locale);
    }
}

if (! function_exists('tchoice')) {
    /**
     * Shortcut for context-aware pluralization.
     */
    function tchoice(string $key, int|float $number, array $replace = [], ?string $locale = null): string
    {
        return Localization::tchoice($key, $number, $replace, $locale);
    }
}

if (! function_exists('l_format_number')) {
    /**
     * Shortcut for international number formatting.
     */
    function l_format_number($value, int $style, array $options = []): string
    {
        return Localization::formatNumber($value, $style, $options);
    }
}
