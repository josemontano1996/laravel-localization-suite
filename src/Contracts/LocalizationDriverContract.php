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

    /**
     * Determine if the driver environment is safe for global state mutations.
     *
     * This flag indicates whether the driver is running in an environment where
     * it is safe to mutate global state like Laravel's app locale or Carbon.
     *
     * @return bool
     */
    public function isSafeToMutateGlobalState(): bool;
}
