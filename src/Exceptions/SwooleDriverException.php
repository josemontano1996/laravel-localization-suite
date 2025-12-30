<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Exceptions;

use RuntimeException;

class SwooleDriverException extends RuntimeException
{
    /**
     * Create a new exception instance with a helpful, actionable message.
     */
    public static function missingExtension(): self
    {
        return new self(
            'The SwooleDriver requires the "swoole" PHP extension to be installed and enabled.'
        );
    }


}
