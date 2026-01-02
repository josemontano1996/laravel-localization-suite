<?php

declare(strict_types=1);
use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\Concerns\IsContextIsolated;

describe('IsContextIsolated Trait', function () {
    $makeTraitUser = fn () => new class
    {
        use IsContextIsolated;
    };

    test('returns false for isSafeToMutateGlobalState', function () use ($makeTraitUser) {
        $user = $makeTraitUser();
        expect($user->isSafeToMutateGlobalState())->toBe(false);
    });

});
