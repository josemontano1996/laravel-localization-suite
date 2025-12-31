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

    test('trait collision results in a fatal exit code', function () {
        // We create a temporary script that tries to use both traits
        $script = <<<PHP
                    <?php
                    require 'vendor/autoload.php';
                    class Tester {
                        use \Josemontano1996\LaravelLocalizationSuite\Traits\IsContextIsolated;
                        use \Josemontano1996\LaravelLocalizationSuite\Traits\MutatesGlobalState;
                    }
                    new Tester();
                    PHP;

        file_put_contents('collision_test.php', $script);

        // Run it as a separate shell command
        $result = shell_exec('php collision_test.php 2>&1');

        expect($result)->toContain('Fatal error');

        unlink('collision_test.php'); // Cleanup
    });
});
