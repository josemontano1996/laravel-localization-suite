<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Contracts;

use BackedEnum;
use Josemontano1996\LaravelLocalizationSuite\Exceptions\LocaleConfigException;

interface LocalizationServiceContract
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
     * Get the application's config locale, falling back to the default fallback locale if not set.
     *
     * @return string The resolved locale string.
     *
     * @throws LocaleConfigException If the locale is not provided and fallback locale is not set in config.
     */
    public function getConfigLocale(): string;

    /**
     * Get the supported locales from configuration, or fallback to defaults.
     *
     * @return array<string> List of supported locale strings.
     *
     * @throws LocaleConfigException If no supported locales can be determined.
     */
    public function getSupportedLocales(): array;
}
