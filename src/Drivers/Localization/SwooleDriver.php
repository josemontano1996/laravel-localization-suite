<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Drivers\Localization;

use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract;
use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\Concerns\IsContextIsolated;
use Josemontano1996\LaravelLocalizationSuite\Exceptions\SwooleDriverException;
use Swoole\Coroutine;

class SwooleDriver implements LocalizationDriverContract
{
    use IsContextIsolated;

    private const string CONTEXT_KEY = 'localization_locale';

    public function __construct()
    {
        if (! extension_loaded('swoole')) {
            throw SwooleDriverException::missingExtension();
        }

        if (! class_exists(Coroutine::class)) {
            throw SwooleDriverException::missingExtension();
        }
    }

    /**
     * Get the current locale from the Swoole Coroutine Context.
     */
    public function getCurrentLocale(): ?string
    {
        // Get current Coroutine ID. Returns -1 if not in a coroutine
        $cid = Coroutine::getuid();

        if ($cid <= 0) {
            return null;
        }

        // Access the specific context for this CID
        $context = Coroutine::getContext($cid);

        return $context[self::CONTEXT_KEY] ?? null;
    }

    /**
     * Set the current locale for the specific Swoole Coroutine.
     */
    public function setCurrentLocale(string $locale): void
    {
        $cid = Coroutine::getuid();

        if ($cid > 0) {
            $context = Coroutine::getContext($cid);
            $context[self::CONTEXT_KEY] = $locale;
        }
    }
}
