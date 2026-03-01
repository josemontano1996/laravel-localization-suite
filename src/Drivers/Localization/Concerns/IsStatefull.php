<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\Concerns;

trait IsStatefull
{
    public function isSafeToMutateGlobalState(): bool
    {
        return false;
    }
}
