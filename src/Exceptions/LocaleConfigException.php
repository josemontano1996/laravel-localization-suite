<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Exceptions;

use RuntimeException;

class LocaleConfigException extends RuntimeException
{
    /**
     * Create a new exception instance with a helpful, actionable message.
     */
    public static function missingSupportedLocales(): self
    {
        return new self(
            "Localization Suite: The 'app.supported_locales' configuration is missing or empty. Please define at least one locale in your config file."
        );
    }

    public static function missingFallbackLocale(): self
    {
        return new self(
            "Localization Suite: Error while falling back to fallback locale, 'app.fallback_locale' configuration is missing or empty. Please define the app.fallback_locale or the app.locale."
        );
    }
}
