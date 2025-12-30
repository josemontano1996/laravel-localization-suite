<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Registrars\RegisterBladeDirectives;
use Josemontano1996\LaravelLocalizationSuite\Registrars\RegisterMacros;
use Josemontano1996\LaravelLocalizationSuite\Services\LocalizationService;

class LocalizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(LocalizationServiceContract::class, LocalizationService::class);
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
