<?php

declare(strict_types=1);

use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Facades\Localization;

describe('Localization Facade', function (): void {
    it('resolves the bound localization service from the container', function (): void {
        $service = app(LocalizationServiceContract::class);

        expect(Localization::getFacadeRoot())->toBe($service)
            ->and(Localization::getCurrentLocale())->toBe($service->getCurrentLocale());
    });
});
