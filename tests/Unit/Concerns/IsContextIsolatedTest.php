<?php

declare(strict_types=1);
use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\Concerns\IsStatefull;

describe('IsStatefull Trait', function (): void {
    $makeTraitUser = fn (): object => new class
    {
        use IsStatefull;
    };

    test('returns false for isSafeToMutateGlobalState', function () use ($makeTraitUser): void {
        $user = $makeTraitUser();
        expect($user->isSafeToMutateGlobalState())->toBe(false);
    });

});
