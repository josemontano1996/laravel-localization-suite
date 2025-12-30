<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Contracts;

interface LocalizationDriverContract
{
    /**
     * Get the current locale for the request.
     *
     * @return string The current locale
     */
    public function getCurrentLocale(): ?string;

    /**
     * Set the current locale for the request.
     *
     * @param  string  $locale  Locale code to set (case-sensitive)
     */
    public function setCurrentLocale(string $locale): void;

    public function isSafeToMutateGlobalState(): bool;
}
