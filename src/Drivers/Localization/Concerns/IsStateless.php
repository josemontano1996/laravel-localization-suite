<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\Concerns;

trait IsStateless
{
    public function isSafeToMutateGlobalState(): bool
    {
        return true;
    }
}
