<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Facades\Route;
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
     * Define routes for testing.
     */
    protected function defineRoutes($router): void
    {
        Route::get('/', function () {
            return 'home';
        })->name('home');

        Route::get('/post/{id}', function () {
            return 'post';
        })->name('post.show');

        Route::get('/user/{id}', function () {
            return 'user';
        })->name('user.profile');
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
