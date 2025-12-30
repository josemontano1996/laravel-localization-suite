<?php

declare(strict_types=1);

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\ContextDriver;
use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\NativeDriver;
use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\OpenSwooleDriver;
use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\SwooleDriver;
use Josemontano1996\LaravelLocalizationSuite\Registrars\RegisterBladeDirectives;
use Josemontano1996\LaravelLocalizationSuite\Registrars\RegisterMacros;
use Josemontano1996\LaravelLocalizationSuite\Services\LocalizationService;

class LocalizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        $this->app->scoped(LocalizationDriverContract::class, function ($app) {
            // 1. Get the string "key" from the config (default to 'native')
            $driverKey = config('localization.driver', 'native');

            // 2. Map the string to the actual class
            $driverClass = match ($driverKey) {
                'native' => NativeDriver::class,
                'context' => ContextDriver::class,
                'swoole' => SwooleDriver::class,
                'openswoole' => OpenSwooleDriver::class,
                default => $driverKey, // Allow users to pass a custom FQCN if they want
            };

            $driver = $app->make($driverClass);

            if (! $driver instanceof LocalizationDriverContract) {
                throw new \RuntimeException('The localization driver must implement LocalizationDriverContract.');
            }

            return $driver;
        });

        $this->app->scoped(LocalizationServiceContract::class, function ($app) {
            return new LocalizationService(
                $app->make(LocalizationDriverContract::class),
                $app->make(\Illuminate\Routing\UrlGenerator::class)
            );
        });
    }

    /**
     * Bootstrap package services.
     *
     * Registers Blade directives and Redirector macros for context-aware
     * localization in views and redirects.
     */
    public function boot(): void
    {
        RegisterBladeDirectives::register();
        RegisterMacros::register();
        $this->registerValidationLocalization();

        $loader = AliasLoader::getInstance();
        $loader->alias('Localization', \Josemontano1996\LaravelLocalizationSuite\Facades\Localization::class);

        $this->publishes([
            __DIR__.'/../config/localization.php' => config_path('localization.php'),
        ], 'localization-config');
    }

    /**
     * Make validation messages respect the context locale without touching App locale.
     *
     * Uses a per-validator cloned Translator instance set to localization()->getCurrentLocale(),
     * preventing cross-request bleed in concurrent environments.
     */
    protected function registerValidationLocalization(): void
    {
        Validator::resolver(function (\Illuminate\Contracts\Translation\Translator $translator, $data, $rules, $messages, $attributes): \Illuminate\Validation\Validator {
            $locale = localization()->getCurrentLocale();

            // Create a new translator instance with the context locale
            $ctxTranslator = clone $translator;
            $ctxTranslator->setLocale($locale);

            return new \Illuminate\Validation\Validator($ctxTranslator, $data, $rules, $messages, $attributes);
        });
    }
}
