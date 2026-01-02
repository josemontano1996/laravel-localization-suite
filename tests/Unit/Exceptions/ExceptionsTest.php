<?php

declare(strict_types=1);

use Josemontano1996\LaravelLocalizationSuite\Exceptions\LocaleConfigException;
use Josemontano1996\LaravelLocalizationSuite\Exceptions\OpenSwooleDriverException;
use Josemontano1996\LaravelLocalizationSuite\Exceptions\SwooleDriverException;

describe('Exceptions', function () {
    it('LocaleConfigException::missingSupportedLocales provides an actionable message', function () {
        $exception = LocaleConfigException::missingSupportedLocales();

        expect($exception)
            ->toBeInstanceOf(LocaleConfigException::class)
            ->and($exception->getMessage())
            ->toBe("Localization Suite: The 'app.supported_locales' configuration is missing or empty. Please define at least one locale in your config file.");
    });

    it('LocaleConfigException::missingFallbackLocale provides an actionable message', function () {
        $exception = LocaleConfigException::missingFallbackLocale();

        expect($exception)
            ->toBeInstanceOf(LocaleConfigException::class)
            ->and($exception->getMessage())
            ->toBe("Localization Suite: Error while falling back to fallback locale, 'app.fallback_locale' configuration is missing or empty. Please define the app.fallback_locale or the app.locale.");
    });

    it('OpenSwooleDriverException::missingExtension explains the extension requirement', function () {
        $exception = OpenSwooleDriverException::missingExtension();

        expect($exception)
            ->toBeInstanceOf(OpenSwooleDriverException::class)
            ->and($exception->getMessage())
            ->toBe('The "openswoole" PHP extension is required to use the OpenSwooleDriver.');
    });

    it('OpenSwooleDriverException::missingCoroutineSupport explains coroutine requirement', function () {
        $exception = OpenSwooleDriverException::missingCoroutineSupport();

        expect($exception)
            ->toBeInstanceOf(OpenSwooleDriverException::class)
            ->and($exception->getMessage())
            ->toBe('OpenSwoole Coroutine support is not available or the class does not exist.');
    });

    it('SwooleDriverException::missingExtension explains the extension requirement', function () {
        $exception = SwooleDriverException::missingExtension();

        expect($exception)
            ->toBeInstanceOf(SwooleDriverException::class)
            ->and($exception->getMessage())
            ->toBe('The SwooleDriver requires the "swoole" PHP extension to be installed and enabled.');
    });
});
