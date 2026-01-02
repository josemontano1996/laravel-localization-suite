<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Services\Concerns;

use Illuminate\Support\Facades\Config;
use Josemontano1996\LaravelLocalizationSuite\Exceptions\LocaleConfigException;

/**
 * Trait ResolvesConfigLocale
 *
 * Provides methods to resolve locale configuration from Laravel's config system.
 */
trait ResolvesConfigLocale
{
    /**
     * Get the application's config locale, falling back to the default fallback locale if not set.
     *
     * @return string The resolved locale string.
     *
     * @throws LocaleConfigException If the locale is not provided and fallback locale is not set in config.
     */
    public function getConfigLocale(): string
    {
        $default = $this->getDefaultConfigLocale();

        return ! empty($default) ? $default : $this->getDefaultFallbackLocale();
    }

    /**
     * Get the application's fallback locale from configuration.
     *
     * @return string The fallback locale configured in 'app.fallback_locale'.
     *
     * @throws LocaleConfigException If the fallback locale is not set in config.
     */
    public function getFallbackLocale(): string
    {
        return $this->getDefaultFallbackLocale();
    }

    /**
     * Get the application's default locale from configuration.
     *
     * @return string|null The locale configured in 'app.locale', or null if not set.
     */
    private function getDefaultConfigLocale(): ?string
    {
        return Config::get('app.locale');
    }

    /**
     * Get the application's fallback locale from configuration.
     *
     * @return string The fallback locale configured in 'app.fallback_locale'.
     *
     * @throws LocaleConfigException If the fallback locale is not set in config.
     */
    private function getDefaultFallbackLocale(): string
    {
        $fallback = Config::get('app.fallback_locale');

        if (empty($fallback)) {
            throw LocaleConfigException::missingFallbackLocale();
        }

        return $fallback;
    }

    /**
     * Get the supported locales from configuration, or fallback to defaults.
     *
     * @return array<string> List of supported locale strings.
     *
     * @throws LocaleConfigException If no supported locales can be determined.
     */
    public function getSupportedLocales(): array
    {
        // Ensure we start with an array, even if config returns null
        $supported = Config::get('app.supported_locales') ?? [];

        // Cast single string to array if necessary
        if (\is_string($supported)) {
            $supported = [$supported];
        }

        // Return if we have a valid, non-empty array
        if (\is_array($supported) && ! empty(array_filter($supported))) {
            return array_values(array_filter($supported));
        }

        // Fallback logic for when config is missing, null, or empty
        $defaults = array_values(array_unique(array_filter([
            $this->getDefaultConfigLocale(),
            $this->getDefaultFallbackLocale(),
        ])));

        if (empty($defaults)) {
            throw LocaleConfigException::missingSupportedLocales();
        }

        return $defaults;
    }
}
