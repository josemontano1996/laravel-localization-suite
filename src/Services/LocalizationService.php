<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Services;

use BackedEnum;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Traits\ResolvesConfigLocale;

final class LocalizationService implements LocalizationServiceContract
{
    use ResolvesConfigLocale;

    public function __construct(private LocalizationDriverContract $localizationDriver) {}

    public function getCurrentLocale(): string
    {
        return $this->localizationDriver->getCurrentLocale();
    }

    public function setCurrentLocale(string $locale): void
    {
        $this->localizationDriver->setCurrentLocale($locale);
    }

    public function route(BackedEnum|string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return '';
    }
}
