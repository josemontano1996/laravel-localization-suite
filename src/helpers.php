<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;

if (! function_exists('localization')) {
    /**
     * Get the localization service instance.
     *
     * Provides convenient access to the context-safe localization service
     * for getting/setting locale and generating localized URLs.
     *
     * @return LocalizationServiceContract The localization service instance
     *
     * @example
     * localization()->getCurrentLocale(); // 'en'
     * localization()->setCurrentLocale('es');
     * localization()->route('profile.show', ['id' => 1]); // '/es/profile/1'
     */
    function localization(): LocalizationServiceContract
    {
        return App::make(LocalizationServiceContract::class);
    }
}

if (! function_exists('tchoice')) {
    /**
     * Context-aware pluralization helper.
     *
     * Mirrors trans_choice signature but defaults $locale to the current context locale
     * when not provided.
     *
     * @param  array<string, mixed>  $replace
     */
    function tchoice(string $key, int|float $number, array $replace = [], ?string $locale = null): string
    {
        $loc = $locale ?? localization()->getCurrentLocale();

        return trans_choice($key, $number, $replace, $loc);
    }
}
