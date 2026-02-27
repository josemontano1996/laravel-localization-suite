<?php

declare(strict_types=1);
use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\Concerns\IsContextIsolated;

describe('IsContextIsolated Trait', function (): void {
    $makeTraitUser = fn (): object => new class
    {
        use IsContextIsolated;
    };

    test('returns false for isSafeToMutateGlobalState', function () use ($makeTraitUser): void {
        $user = $makeTraitUser();
        expect($user->isSafeToMutateGlobalState())->toBe(false);
    });

});
