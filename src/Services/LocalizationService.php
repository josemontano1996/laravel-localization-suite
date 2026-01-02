<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Services;

use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Routing\UrlGenerator;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Services\Concerns\ResolvesConfigLocale;

final class LocalizationService implements LocalizationServiceContract
{
    use ResolvesConfigLocale;

    public function __construct(
        private LocalizationDriverContract $localizationDriver,
        private UrlGenerator $url,
        private string $routeKey,
    ) {}

    public function getRouteKey(): string
    {
        return $this->routeKey;
    }

    public function getCurrentLocale(): string
    {
        return $this->localizationDriver->getCurrentLocale() ?? $this->getConfigLocale();
    }

    public function setCurrentLocale(string $locale): void
    {
        if (! \in_array($locale, $this->getSupportedLocales())) {
            $locale = $this->getConfigLocale();
        }
        $this->localizationDriver->setCurrentLocale($locale);

        if ($this->localizationDriver->isSafeToMutateGlobalState()) {
            $this->syncGlobalState($locale);
        }
    }

    public function route(BackedEnum|string $name, mixed $parameters = [], bool $absolute = true): string
    {
        $parameters = \is_array($parameters) ? $parameters : [$parameters];
        $parameters = [$this->getRouteKey() => $this->getCurrentLocale(), ...$parameters];

        return $this->url->route($name, $parameters, $absolute);
    }

    public function t(string $key, array $replace = [], ?string $locale = null): string
    {
        return __($key, $replace, $locale ?? $this->getCurrentLocale());
    }

    public function tchoice(string $key, int|float $number, array $replace = [], ?string $locale = null): string
    {
        return trans_choice($key, $number, $replace, $locale ?? $this->getCurrentLocale());
    }

    public function formatNumber($value, int $style, array $options = []): string
    {
        $locale = $this->getCurrentLocale();
        $fmt = new \NumberFormatter($locale, $style);

        if (isset($options['decimals']) && $options['decimals'] !== null) {
            $fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, (int) $options['decimals']);
            $fmt->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, (int) $options['decimals']);
        }

        if ($style === \NumberFormatter::CURRENCY) {
            return $fmt->formatCurrency((float) $value, $options['currency'] ?? 'USD');
        }

        return $fmt->format((float) $value) ?: (string) $value;
    }

    private function syncGlobalState(string $locale): void
    {
        $this->url->defaults([$this->getRouteKey() => $locale]);

        // Global Carbon State
        if (class_exists(Carbon::class)) {
            Carbon::setLocale($locale);
            CarbonImmutable::setLocale($locale);
        }
    }
}
