<?php

namespace Josemontano1996\LaravelLocalizationSuite\Contracts;

use BackedEnum;

interface LocalizationService
{
    /**
     * Get the current locale for the request.
     *
     * @return string The current locale
     */
    public function getCurrentLocale(): string;

    /**
     * Set the current locale for the request.
     *
     * @param  string  $locale  Locale code to set (case-sensitive)
     */
    public function setCurrentLocale(string $locale): void;

    /**
     * Generate a localized route URL.
     *
     * Creates a URL for the given route name, automatically injecting the current
     * locale into the route parameters. Supports BackedEnum route names.
     *
     * @param  BackedEnum|string  $name  Route name or BackedEnum case
     * @param  mixed  $parameters  Route parameters (array or single value)
     * @param  bool  $absolute  Whether to generate absolute URL (default: true)
     * @return string The generated URL with locale injected
     */
    public function route(BackedEnum|string $name, mixed $parameters = [], bool $absolute = true): string;

    /**
     * Get the list of supported locales from configuration.
     *
     * Retrieves the supported locales from app.supported_locales configuration.
     * Returns an empty array if not configured.
     *
     * @return array<int, string> Array of supported locale codes
     */
    public function getSupportedLocales(): array;
}
