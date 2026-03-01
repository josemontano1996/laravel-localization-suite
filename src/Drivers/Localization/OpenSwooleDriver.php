<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Drivers\Localization;

use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract;
use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\Concerns\IsStatefull;
use Josemontano1996\LaravelLocalizationSuite\Exceptions\OpenSwooleDriverException;
use OpenSwoole\Coroutine;

final class OpenSwooleDriver implements LocalizationDriverContract
{
    use IsStatefull;

    private const string CONTEXT_KEY = 'localization_locale';

    /**
     * Create a new OpenSwooleDriver instance.
     *
     * @throws OpenSwooleDriverException
     */
    public function __construct()
    {
        if (! extension_loaded('openswoole')) {
            throw OpenSwooleDriverException::missingExtension();
        }

        if (! class_exists(Coroutine::class)) {
            throw OpenSwooleDriverException::missingCoroutineSupport();
        }
    }

    /**
     * Get the current locale from the OpenSwoole Coroutine Context.
     */
    public function getCurrentLocale(): ?string
    {
        // OpenSwoole uses getCid() to identify the current coroutine
        $cid = Coroutine::getCid();

        // CID is -1 if we are not inside a coroutine (e.g., CLI/Task worker)
        if ($cid <= 0) {
            return null;
        }

        $context = Coroutine::getContext($cid);

        return $context[self::CONTEXT_KEY] ?? null;
    }

    /**
     * Set the current locale for the specific OpenSwoole Coroutine.
     */
    public function setCurrentLocale(string $locale): void
    {
        $cid = Coroutine::getCid();

        if ($cid > 0) {
            $context = Coroutine::getContext($cid);
            $context[self::CONTEXT_KEY] = $locale;
        }
    }
}
