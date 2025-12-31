<?php

declare(strict_types=1);
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;


describe('Helpers', function () {

    it('localization() returns the actual service from the container', function () {
        $service = localization();
        
        expect($service)->toBeInstanceOf(LocalizationServiceContract::class);
    });

    it('t() translates a key using the real service', function () {
        // Arrange: Add a temporary translation to the real Laravel translator
        app('translator')->addLines([
            'messages.hello' => 'Hello World'
        ], 'en');

        // Act: Use the helper
        $result = t('messages.hello');

        // Assert
        expect($result)->toBe('Hello World');
    });

    it('tchoice() handles pluralization correctly', function () {
        app('translator')->addLines([
            'messages.apples' => '{0} No apples|{1} One apple|[2,*] :count apples'
        ], 'en');

        expect(tchoice('messages.apples', 0))->toBe('No apples');
        expect(tchoice('messages.apples', 1))->toBe('One apple');
        expect(tchoice('messages.apples', 5))->toBe('5 apples');
    });

    it('l_format_number() formats numbers based on the real service logic', function () {
        $result = l_format_number(1234.56, 1); 
        expect($result)->toContain('1,234.56');
    });

});