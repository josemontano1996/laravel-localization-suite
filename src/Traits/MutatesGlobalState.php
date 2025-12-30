<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Traits;

trait MutatesGlobalState
{
    public function isSafeToMutateGlobalState(): bool
    {
        return true;
    }
}
