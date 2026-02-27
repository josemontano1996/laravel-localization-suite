<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use NumberFormatter;

describe('LocalizationService', function () {

    it('gets route key from configuration', function () {
        $service = app(LocalizationServiceContract::class);

        expect($service->getRouteKey())->toBe('locale');
    });

    it('returns current locale from driver', function () {
        $service = app(LocalizationServiceContract::class);

        $currentLocale = $service->getCurrentLocale();

        expect($currentLocale)->toBeIn(['en', 'es', 'fr']);
    });

    it('sets current locale when locale is supported', function () {
        $service = app(LocalizationServiceContract::class);

        $service->setCurrentLocale('es');

        expect($service->getCurrentLocale())->toBe('es');
    });

    it('falls back to default locale when setting unsupported locale', function () {
        $service = app(LocalizationServiceContract::class);
        $defaultLocale = config('app.locale');

        $service->setCurrentLocale('unsupported-locale');

        expect($service->getCurrentLocale())->toBe($defaultLocale);
    });

    it('generates route with current locale parameter', function () {
        $service = app(LocalizationServiceContract::class);
        $service->setCurrentLocale('en');

        $route = $service->route('home');

        expect($route)->toContain('/en');
    });

    it('generates route with additional parameters', function () {
        $service = app(LocalizationServiceContract::class);
        $service->setCurrentLocale('es');

        $route = $service->route('post.show', ['id' => 123]);

        expect($route)->toContain('/es/post/123');
    });

    it('generates route without locale segment using query parameter', function () {
        $service = app(LocalizationServiceContract::class);
        $service->setCurrentLocale('en');

        $route = $service->route('api.status');

        expect($route)->toContain('/api/status');
        expect($route)->toContain('locale=en');
    });

    it('generates route without locale segment with additional params', function () {
        $service = app(LocalizationServiceContract::class);
        $service->setCurrentLocale('fr');

        $route = $service->route('api.posts.show', ['id' => 42]);

        expect($route)->toContain('/api/posts/42');
        expect($route)->toContain('locale=fr');
    });

    it('generates absolute route by default', function () {
        $service = app(LocalizationServiceContract::class);
        $service->setCurrentLocale('en');

        $route = $service->route('home');

        expect($route)->toMatch('/^https?:\/\//');
    });

    it('generates relative route when absolute is false', function () {
        $service = app(LocalizationServiceContract::class);
        $service->setCurrentLocale('en');

        $route = $service->route('home', [], false);

        expect($route)->not()->toMatch('/^https?:\/\//');
    });

    it('translates keys using current locale', function () {
        $service = app(LocalizationServiceContract::class);
        app('translator')->addLines([
            'messages.greeting' => 'Hello World',
        ], 'en');

        $service->setCurrentLocale('en');
        $result = $service->t('messages.greeting');

        expect($result)->toBe('Hello World');
    });

    it('translates keys with replacements', function () {
        $service = app(LocalizationServiceContract::class);
        app('translator')->addLines([
            'messages.welcome' => 'Welcome, :name!',
        ], 'en');

        $service->setCurrentLocale('en');
        $result = $service->t('messages.welcome', ['name' => 'John']);

        expect($result)->toBe('Welcome, John!');
    });

    it('translates with specified locale regardless of current locale', function () {
        $service = app(LocalizationServiceContract::class);
        app('translator')->addLines([
            'messages.hello' => 'Hello',
        ], 'en');
        app('translator')->addLines([
            'messages.hello' => 'Hola',
        ], 'es');

        $service->setCurrentLocale('en');

        expect($service->t('messages.hello', [], 'es'))->toBe('Hola');
        expect($service->t('messages.hello', [], 'en'))->toBe('Hello');
    });

    it('handles choice translations correctly', function () {
        $service = app(LocalizationServiceContract::class);
        app('translator')->addLines([
            'messages.items' => '{0} No items|{1} One item|[2,*] :count items',
        ], 'en');

        $service->setCurrentLocale('en');

        expect($service->tchoice('messages.items', 0))->toBe('No items');
        expect($service->tchoice('messages.items', 1))->toBe('One item');
        expect($service->tchoice('messages.items', 5))->toBe('5 items');
    });

    it('handles choice translations with replacements', function () {
        $service = app(LocalizationServiceContract::class);
        app('translator')->addLines([
            'messages.apples' => '{0} No apples|{1} One :fruit|[2,*] :count :fruit',
        ], 'en');

        $service->setCurrentLocale('en');

        $result = $service->tchoice('messages.apples', 5, ['fruit' => 'apples']);

        expect($result)->toContain('5');
        expect($result)->toContain('apples');
    });

    it('uses specified locale for choice translations', function () {
        $service = app(LocalizationServiceContract::class);
        app('translator')->addLines([
            'messages.items' => '{0} No items|{1} One item|[2,*] :count items',
        ], 'en');
        app('translator')->addLines([
            'messages.items' => '{0} Sin items|{1} Un item|[2,*] :count items',
        ], 'es');

        $service->setCurrentLocale('en');

        expect($service->tchoice('messages.items', 1, [], 'es'))->toBe('Un item');
    });

    it('formats numbers as decimal by default', function () {
        $service = app(LocalizationServiceContract::class);
        $service->setCurrentLocale('en');

        $result = $service->formatNumber(1234.56, 1);

        // NumberFormatter in en locale formats as "1,234.56"
        expect($result)->toMatch('/1,?234\.56/');
    });

    it('formats numbers with specific decimal places', function () {
        $service = app(LocalizationServiceContract::class);
        $service->setCurrentLocale('en');

        $result = $service->formatNumber(1234.56789, 1, ['decimals' => 2]);

        expect($result)->toMatch('/1,?234\.5[0-9]/');
    });

    it('formats currency numbers', function () {
        $service = app(LocalizationServiceContract::class);
        $service->setCurrentLocale('en');

        $result = $service->formatNumber(1234.56, NumberFormatter::CURRENCY, ['currency' => 'USD']);

        // USD currency format: $1,234.56
        expect($result)->toContain('$');
        expect($result)->toMatch('/1,?234\.56/');
    });

    it('formats currency with different currency code', function () {
        $service = app(LocalizationServiceContract::class);
        $service->setCurrentLocale('en');

        $result = $service->formatNumber(1234.56, NumberFormatter::CURRENCY, ['currency' => 'EUR']);

        // EUR currency format: €1,234.56
        expect($result)->toContain('€');
        expect($result)->toMatch('/1,?234\.56/');
    });

    it('returns current locale when translating without locale parameter', function () {
        $service = app(LocalizationServiceContract::class);
        app('translator')->addLines([
            'messages.test' => 'Test EN',
        ], 'en');
        app('translator')->addLines([
            'messages.test' => 'Test ES',
        ], 'es');

        $service->setCurrentLocale('en');
        expect($service->t('messages.test'))->toBe('Test EN');

        $service->setCurrentLocale('es');
        expect($service->t('messages.test'))->toBe('Test ES');
    });

});
