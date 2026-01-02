<?php

declare(strict_types=1);

use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Facades\Localization;

describe('Localization Facade', function () {
    it('resolves the bound localization service from the container', function () {
        $service = app(LocalizationServiceContract::class);

        expect(Localization::getFacadeRoot())->toBe($service)
            ->and(Localization::getCurrentLocale())->toBe($service->getCurrentLocale());
    });
});
