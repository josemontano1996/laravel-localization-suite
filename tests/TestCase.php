<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Facades\Route;
use Josemontano1996\LaravelLocalizationSuite\Middlewares\SetLocaleFromRoute;
use Josemontano1996\LaravelLocalizationSuite\Middlewares\SetLocalizedHeaders;
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
        $setLocaleMiddleware = SetLocaleFromRoute::class;
        $setHeadersMiddleware = SetLocalizedHeaders::class;

        // Routes with {locale} in path - constrained to valid locales only
        Route::get('/{locale}', function () {
            return 'home';
        })->name('home')->where('locale', 'en|es|fr');

        Route::get('/{locale}/post/{id}', function () {
            return 'post';
        })->name('post.show')->where('locale', 'en|es|fr');

        Route::get('/{locale}/user/{id}', function () {
            return 'user';
        })->name('user.profile')->where('locale', 'en|es|fr');

        // Routes without {locale} - locale becomes query parameter
        Route::get('/api/status', function () {
            return 'api status';
        })->name('api.status');

        Route::get('/api/posts/{id}', function () {
            return 'api post';
        })->name('api.posts.show');
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
