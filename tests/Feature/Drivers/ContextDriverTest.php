<?php

declare(strict_types=1);

use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\ContextDriver;

describe('ContextDriver', function (): void {
    beforeEach(function (): void {
        app()->forgetScopedInstances();
        app()->forgetInstance(LocalizationDriverContract::class);
        config(['localization.driver' => 'context']);
    });

    it('stores locale in Laravel Context without mutating global state', function (): void {
        $service = app(LocalizationServiceContract::class);
        $driver = app(LocalizationDriverContract::class);

        expect($driver)->toBeInstanceOf(ContextDriver::class)
            ->and($driver->isSafeToMutateGlobalState())->toBeFalse()
            ->and(app()->getLocale())->toBe('en');

        $service->setCurrentLocale('es');

        expect($service->getCurrentLocale())->toBe('es')
            ->and($driver->getCurrentLocale())->toBe('es')
            ->and(app()->getLocale())->toBe('en');
    });
});
