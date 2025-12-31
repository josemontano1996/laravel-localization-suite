<?php

declare(strict_types=1);

namespace Tests;

use Josemontano1996\LaravelLocalizationSuite\Providers\LocalizationServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * @property \Illuminate\Routing\Redirector $redirector
 * @property \Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract $service
 * @property \Josemontano1996\LaravelLocalizationSuite\Services\RedirectorService $redirectorService
 */
abstract class TestCase extends BaseTestCase
{
    protected const string DEFAULT_LOCALE = 'en';

    protected const string FALLBACK_LOCALE = 'es';

    protected const array SUPPORTED_LOCALES = ['en', 'es', 'fr'];

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function defineEnvironment($app): void
    {
        // Centralize your default config here
        $app['config']->set('app.locale', $this::DEFAULT_LOCALE);
        $app['config']->set('app.fallback_locale', $this::FALLBACK_LOCALE);
        $app['config']->set('app.supported_locales', $this::SUPPORTED_LOCALES);
    }

    /**
     * Load your package Service Provider.
     */
    protected function getPackageProviders($app): array
    {
        return [
            LocalizationServiceProvider::class,
        ];
    }
}
