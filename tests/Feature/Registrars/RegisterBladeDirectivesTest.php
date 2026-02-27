<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Registrars\RegisterBladeDirectives;

describe('RegisterBladeDirectives', function (): void {
    beforeEach(function (): void {
        $this->service = app(LocalizationServiceContract::class);
        CarbonImmutable::setTestNow(CarbonImmutable::create(2020, 1, 1, 12, 0, 0, 'UTC'));
        RegisterBladeDirectives::register();
    });

    it('renders navigation and translation directives', function (): void {
        app('translator')->addLines([
            'messages.welcome' => 'Welcome',
            'messages.apples' => '{0} No apples|{1} One apple|[2,*] :count apples',
        ], 'en');

        expect(trim(Blade::render("@route('home', ['id' => 7], false)")))->toBe($this->service->route('home', ['id' => 7], false))
            ->and(trim(Blade::render("@t('messages.welcome')")))->toBe($this->service->t('messages.welcome'))
            ->and(trim(Blade::render("@tchoice('messages.apples', 3)")))->toBe($this->service->tchoice('messages.apples', 3));
    });

    it('exposes locale state helpers', function (): void {
        expect(trim(Blade::render('@locale')))->toBe($this->service->getCurrentLocale())
            ->and(trim(Blade::render("@localeIs('en') ok @endlocaleIs")))->toBe('ok')
            ->and(trim(Blade::render("@localeIs('fr') nope @endlocaleIs")))->toBe('');
    });

    it('iterates supported locales', function (): void {
        $output = Blade::render('@locales($loc){{ $loc }}|@endlocales');

        expect(str_replace(' ', '', trim($output)))->toBe(implode('|', $this->service->getSupportedLocales()).'|');
    });

    it('formats numbers, currency, and percent via the service', function (): void {
        $number = $this->service->formatNumber(123.45, \NumberFormatter::DECIMAL);
        $currency = $this->service->formatNumber(50, \NumberFormatter::CURRENCY, ['currency' => 'EUR', 'decimals' => 2]);
        $percent = $this->service->formatNumber(0.5, \NumberFormatter::PERCENT);

        expect(trim(Blade::render('@number(123.45)')))->toBe($number)
            ->and(trim(Blade::render("@currency(50, 'EUR', 2)")))->toBe($currency)
            ->and(trim(Blade::render('@percent(0.5)')))->toBe($percent);
    });

    it('formats dates using Carbon with current locale', function (): void {
        expect(trim(Blade::render('@date()')))->toBe('January 1, 2020')
            ->and(trim(Blade::render('@datetime()')))->toBe('January 1, 2020 12:00 PM');
    });
});
