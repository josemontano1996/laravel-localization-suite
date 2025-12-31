<?php

declare(strict_types=1);
use Josemontano1996\LaravelLocalizationSuite\Traits\MutatesGlobalState;

describe('MutatesGlobalState Trait', function () {
    $makeTraitUser = fn () => new class
    {
        use MutatesGlobalState;
    };

    test('returns true for isSafeToMutateGlobalState', function () use ($makeTraitUser) {
        $user = $makeTraitUser();
        expect($user->isSafeToMutateGlobalState())->toBe(true);
    });
});
