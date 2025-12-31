<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Exceptions;

use RuntimeException;

final class OpenSwooleDriverException extends RuntimeException
{
    public static function missingExtension(): self
    {
        return new self('The "openswoole" PHP extension is required to use the OpenSwooleDriver.');
    }

    public static function missingCoroutineSupport(): self
    {
        return new self('OpenSwoole Coroutine support is not available or the class does not exist.');
    }
}
