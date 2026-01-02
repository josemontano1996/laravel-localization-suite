<?php

declare(strict_types=1);

use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\NativeDriver;

describe('NativeDriver', function () {
    beforeEach(function () {
        app()->forgetScopedInstances();
        app()->forgetInstance(LocalizationDriverContract::class);
        config(['localization.driver' => 'native']);
        app()->setLocale('en');
    });

    it('mutates global app locale when setting current locale', function () {
        $service = app(LocalizationServiceContract::class);
        $driver = app(LocalizationDriverContract::class);

        expect($driver)->toBeInstanceOf(NativeDriver::class)
            ->and($driver->isSafeToMutateGlobalState())->toBeTrue()
            ->and(app()->getLocale())->toBe('en');

        $service->setCurrentLocale('fr');

        expect($service->getCurrentLocale())->toBe('fr')
            ->and($driver->getCurrentLocale())->toBe('fr')
            ->and(app()->getLocale())->toBe('fr');
    });
});
