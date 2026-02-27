<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Josemontano1996\LaravelLocalizationSuite\Exceptions\LocaleConfigException;
use Josemontano1996\LaravelLocalizationSuite\Services\Concerns\ResolvesConfigLocale;

beforeEach(function (): void {
    // Reset config before each test
    Config::set('app.locale');
    Config::set('app.fallback_locale');
    Config::set('app.supported_locales');
});

describe('ResolvesConfigLocale Trait', function (): void {
    // Helper class to use the trait
    $makeTraitUser = fn (): object => new class
    {
        use ResolvesConfigLocale;
    };

    test('getConfigLocale returns app.locale if set', function () use ($makeTraitUser): void {
        Config::set('app.locale', 'es');
        Config::set('app.fallback_locale', 'en');
        $user = $makeTraitUser();
        expect($user->getConfigLocale())->toBe('es');
    });

    test('getConfigLocale falls back to fallback locale', function () use ($makeTraitUser): void {
        Config::set('app.locale');
        Config::set('app.fallback_locale', 'en');
        $user = $makeTraitUser();
        expect($user->getConfigLocale())->toBe('en');
    });

    test('getConfigLocale throws if neither locale nor fallback is set', function () use ($makeTraitUser): void {
        Config::set('app.locale');
        Config::set('app.fallback_locale');
        $user = $makeTraitUser();
        expect(fn (): string => $user->getConfigLocale())
            ->toThrow(LocaleConfigException::class);
    });

    test('getSupportedLocales returns supported_locales as array', function () use ($makeTraitUser): void {
        Config::set('app.supported_locales', ['en', 'es']);
        $user = $makeTraitUser();
        expect($user->getSupportedLocales())->toBe(['en', 'es']);
    });

    test('getSupportedLocales casts string to array', function () use ($makeTraitUser): void {
        Config::set('app.supported_locales', 'en');
        $user = $makeTraitUser();
        expect($user->getSupportedLocales())->toBe(['en']);
    });

    test('getSupportedLocales falls back to config and fallback locale', function () use ($makeTraitUser): void {
        Config::set('app.supported_locales');
        Config::set('app.locale', 'es');
        Config::set('app.fallback_locale', 'en');
        $user = $makeTraitUser();
        expect($user->getSupportedLocales())->toBe(['es', 'en']);
    });

    test('getSupportedLocales throws if no locales found', function () use ($makeTraitUser): void {
        Config::set('app.supported_locales');
        Config::set('app.locale');
        Config::set('app.fallback_locale');
        $user = $makeTraitUser();
        expect(fn (): array => $user->getSupportedLocales())
            ->toThrow(LocaleConfigException::class);
    });
});
